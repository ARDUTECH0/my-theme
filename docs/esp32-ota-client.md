# ECM — كود البورده (مستقبِل التحديث المحلي)

البورده شغّالة على شبكة داخلية **من غير إنترنت**، فهي مابتسحبش التحديث من الموقع.
بدل كده بتفتح سيرفر HTTP صغير على الشبكة الداخلية، والتطبيق هو اللي **بيدفع** الفيرموير عليها.

```
[ووردبريس] ──إنترنت──> [التطبيق] ──شبكة داخلية──> [البورده]
                          ينزّل ويخزّن            يستقبل ويفلَش
```

> تدفّق التطبيق نفسه في [ota-app-flow.md](ota-app-flow.md).

---

## 1. الـ endpoints اللي البورده بتفتحها

| المسار | الميثود | الوظيفة |
|---|---|---|
| `/ecm/info`    | GET  | يرجّع السيريال والموديل والإصدار الحالي — التطبيق بيقراه الأول |
| `/ecm/update`  | POST | استقبال ملف الـ `.bin` (multipart) وفلَشه |
| `/ecm/reboot`  | POST | إعادة تشغيل يدوية |

كلهم محميين بهيدر `X-ECM-Key` — مفتاح محلي متخزّن في الـ NVS.

---

## 2. كود ESP32 كامل

```cpp
/*
 * ECM Local OTA Receiver — ESP32 (Arduino core 2.x / 3.x)
 * Libraries: WiFi, WebServer, Update, Preferences, ArduinoJson (v6+)
 *
 * البورده بتشتغل على شبكة داخلية من غير إنترنت.
 * التطبيق بيلاقيها، يقرا إصدارها، ويرفع عليها الفيرموير الجديد.
 */
#include <WiFi.h>
#include <WebServer.h>
#include <Update.h>
#include <Preferences.h>
#include <ESPmDNS.h>

#define FW_VERSION   "1.0.0"       // زوّده مع كل build
#define FW_MODEL     "default"     // نفس الموديل اللي في لوحة ECM

// شبكة داخلية — من غير إنترنت
#define LAN_SSID     "ECM-LOCAL"
#define LAN_PASS     "YOUR_LOCAL_PASS"

WebServer   server(80);
Preferences prefs;

String g_serial   = "";            // بيتكتب مرة واحدة وقت التصنيع/التجهيز
String g_localKey = "";            // مفتاح الحماية المحلي

// ── قراءة الهوية من الـ NVS ───────────────────────────────────
void loadIdentity() {
  prefs.begin("ecm", true);
  g_serial   = prefs.getString("serial", "");
  g_localKey = prefs.getString("key", "");
  prefs.end();

  // أول تشغيل: ولّد مفتاح محلي عشوائي
  if (g_localKey.isEmpty()) {
    char buf[33];
    for (int i = 0; i < 32; i++) buf[i] = "0123456789abcdef"[esp_random() % 16];
    buf[32] = 0;
    g_localKey = String(buf);
    prefs.begin("ecm", false);
    prefs.putString("key", g_localKey);
    prefs.end();
    Serial.printf("[ECM] مفتاح محلي جديد: %s\n", g_localKey.c_str());
  }
}

// ── التحقق من المفتاح ─────────────────────────────────────────
bool authed() {
  if (!server.hasHeader("X-ECM-Key")) return false;
  String k = server.header("X-ECM-Key");
  if (k.length() != g_localKey.length()) return false;
  // مقارنة ثابتة الزمن
  uint8_t diff = 0;
  for (size_t i = 0; i < k.length(); i++) diff |= (uint8_t)(k[i] ^ g_localKey[i]);
  return diff == 0;
}

void denyUnauthed() { server.send(401, "application/json", "{\"ok\":false,\"error\":\"bad_key\"}"); }

// ── GET /ecm/info ─────────────────────────────────────────────
void handleInfo() {
  if (!authed()) { denyUnauthed(); return; }

  String json = "{";
  json += "\"ok\":true";
  json += ",\"serial\":\""  + g_serial + "\"";
  json += ",\"model\":\""   + String(FW_MODEL) + "\"";
  json += ",\"version\":\"" + String(FW_VERSION) + "\"";
  json += ",\"mac\":\""     + WiFi.macAddress() + "\"";
  json += ",\"ip\":\""      + WiFi.localIP().toString() + "\"";
  json += ",\"rssi\":"      + String(WiFi.RSSI());
  json += ",\"uptime\":"    + String(millis() / 1000);
  json += ",\"free\":"      + String(ESP.getFreeSketchSpace());
  json += "}";
  server.send(200, "application/json", json);
}

// ── POST /ecm/update ──────────────────────────────────────────
// التطبيق بيبعت الملف multipart، والـ MD5 في هيدر X-ECM-MD5
void handleUpdateDone() {
  if (!authed()) { denyUnauthed(); return; }

  bool ok = !Update.hasError();
  String json = ok
    ? "{\"ok\":true,\"message\":\"تم — إعادة تشغيل\"}"
    : String("{\"ok\":false,\"error\":\"") + Update.errorString() + "\"}";

  server.sendHeader("Connection", "close");
  server.send(ok ? 200 : 500, "application/json", json);

  if (ok) { delay(400); ESP.restart(); }
}

void handleUpdateUpload() {
  HTTPUpload& up = server.upload();

  if (up.status == UPLOAD_FILE_START) {
    if (!authed()) return;                       // handleUpdateDone هيرد بـ 401

    Serial.printf("[OTA] بدأ: %s\n", up.filename.c_str());

    if (!Update.begin(UPDATE_SIZE_UNKNOWN)) {
      Update.printError(Serial);
      return;
    }
    // لو التطبيق بعت الـ MD5، المكتبة هتتحقق منه قبل ما تعتمد الصورة
    if (server.hasHeader("X-ECM-MD5")) {
      String md5 = server.header("X-ECM-MD5");
      md5.toLowerCase();
      if (md5.length() == 32) Update.setMD5(md5.c_str());
    }

  } else if (up.status == UPLOAD_FILE_WRITE) {
    if (Update.write(up.buf, up.currentSize) != up.currentSize) Update.printError(Serial);

  } else if (up.status == UPLOAD_FILE_END) {
    if (Update.end(true)) Serial.printf("[OTA] تم — %u bytes\n", up.totalSize);
    else                  Update.printError(Serial);   // غالبًا MD5 مش مطابق

  } else if (up.status == UPLOAD_FILE_ABORTED) {
    Update.abort();
    Serial.println("[OTA] اتلغى");
  }
}

// ── POST /ecm/reboot ──────────────────────────────────────────
void handleReboot() {
  if (!authed()) { denyUnauthed(); return; }
  server.send(200, "application/json", "{\"ok\":true}");
  delay(300);
  ESP.restart();
}

// ── setup / loop ─────────────────────────────────────────────
void setup() {
  Serial.begin(115200);
  loadIdentity();

  WiFi.mode(WIFI_STA);
  WiFi.begin(LAN_SSID, LAN_PASS);
  for (int i = 0; i < 60 && WiFi.status() != WL_CONNECTED; i++) delay(250);

  Serial.printf("[ECM] %s v%s | %s | %s\n",
                FW_MODEL, FW_VERSION, g_serial.c_str(),
                WiFi.localIP().toString().c_str());

  // عشان التطبيق يلاقي البورده من غير ما تكتب الـ IP: http://ecm-<serial>.local
  if (MDNS.begin(("ecm-" + g_serial).c_str())) {
    MDNS.addService("ecm-ota", "tcp", 80);
    MDNS.addServiceTxt("ecm-ota", "tcp", "model", FW_MODEL);
    MDNS.addServiceTxt("ecm-ota", "tcp", "ver", FW_VERSION);
    MDNS.addServiceTxt("ecm-ota", "tcp", "sn", g_serial.c_str());
  }

  // لازم نقول للمكتبة تحتفظ بالهيدرات اللي محتاجينها
  const char* keep[] = { "X-ECM-Key", "X-ECM-MD5" };
  server.collectHeaders(keep, 2);

  server.on("/ecm/info",   HTTP_GET,  handleInfo);
  server.on("/ecm/reboot", HTTP_POST, handleReboot);
  server.on("/ecm/update", HTTP_POST, handleUpdateDone, handleUpdateUpload);
  server.begin();

  Serial.println("[ECM] مستقبِل التحديث المحلي شغّال على المنفذ 80");
}

void loop() {
  server.handleClient();
  // ... باقي شغل البورده
}
```

---

## 3. ESP8266 — الفروق

- `#include <ESP8266WiFi.h>` · `<ESP8266WebServer.h>` · `<ESP8266mDNS.h>`
- الكلاس اسمه `ESP8266WebServer` مش `WebServer`
- `Update.begin(maxSketchSpace)` — احسبها بـ `(ESP.getFreeSketchSpace() - 0x1000) & 0xFFFFF000`
- `esp_random()` مش موجودة — استخدم `RANDOM_REG32` أو `os_random()`
- تقسيمة الفلاش لازم تسيب مساحة للتحديث: `4MB (FS:1MB OTA:~1019KB)`

---

## 4. تقسيمة الفلاش (ESP32)

Arduino IDE → **Tools → Partition Scheme → Default 4MB with spiffs (1.2MB APP / 1.5MB SPIFFS)**
لازم يكون فيه `ota_0` و `ota_1` — من غيرهم `Update.begin()` هتفشل.
قارن حجم الـ `.bin` بـ `ESP.getFreeSketchSpace()` (بيرجع في `/ecm/info`) قبل ما تبعت.

---

## 5. تجهيز السيريال

السيريال بيتكتب مرة واحدة في الـ NVS وقت التجهيز:

```cpp
prefs.begin("ecm", false);
prefs.putString("serial", "ECM-0001");
prefs.end();
```

نفس السيريال لازم يكون مضاف في **لوحة ECM → 🛡️ حماية الأجهزة** عشان التحديثات تتربط بيه.

---

## 6. المفتاح المحلي

أول تشغيل البورده بتولّد مفتاح عشوائي وبتطبعه على الـ Serial.
التطبيق بيحفظه أول مرة يتجوّز مع البورده، وبعد كده بيبعته في هيدر `X-ECM-Key`.

الشبكة دي مقفولة أصلاً، فالمفتاح ده غرضه الأساسي منع **الغلط** — إن تطبيق تاني على نفس الشبكة يرفع فيرموير على بورده مش بتاعته. لو عايز حماية حقيقية ضد مهاجم جوّه الشبكة، فعّل **Secure Boot + Flash Encryption** وامضِ الصور بمفتاحك.
