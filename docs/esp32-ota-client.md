# ECM — التحديث عن بُعد للأجهزة (OTA)

دليل الجهاز: إزاي الـ ESP32 / ESP8266 بيسأل السيرفر عن تحديث، ينزّله، ويبلّغ بالنتيجة.

---

## 1. الفكرة في سطرين

الجهاز بيسأل `/ota/check` ومعاه **السيريال + التوكن + إصداره الحالي**.
لو فيه تحديث، السيرفر بيرجّع **رابط موقّع بينتهي بعد 15 دقيقة** ومربوط بالجهاز ده لوحده، ومعاه بصمة MD5.
الجهاز ينزّل، يتحقق من البصمة، يعمل reboot، وبعدين يبلّغ `/ota/report`.

مفيش رابط ثابت للفيرموير — ملفات الـ `.bin` محفوظة في مجلد محمي بره متناول المتصفح.

---

## 2. نقاط الاتصال

### `GET /wp-json/ecm/v1/ota/check`

| البراميتر | إجباري | الوصف |
|---|---|---|
| `serial`  | ✔ | سيريال الجهاز |
| `token`   | ✔ (لو التوثيق مفعّل) | توكن الجهاز — بيتولّد لما العميل يربط السيريال بحسابه. ممكن كمان يتبعت كهيدر `X-ECM-Device-Token` |
| `version` | ✔ | إصدار الفيرموير الحالي، مثلاً `1.0.3` |
| `model`   | – | موديل اللوحة (افتراضي `default`) |
| `channel` | – | `stable` / `beta` / `dev` — القناة المثبّتة من اللوحة بتكسب |
| `chip`, `mac`, `rssi`, `uptime` | – | تليمتري بتظهر في لوحة الأسطول |

**الرد لما يكون فيه تحديث:**

```json
{
  "ok": true,
  "update": true,
  "version": "1.2.0",
  "url": "https://site.com/wp-json/ecm/v1/ota/download?r=7&s=ECM123&e=1760000000&k=...",
  "md5": "9f86d081884c7d659a2feaa0c55ad015",
  "sha256": "...",
  "size": 892144,
  "mandatory": false,
  "notes": "إصلاح انقطاع الواي فاي",
  "check_in": 21600
}
```

**لما ميكونش فيه:** `{"ok":true,"update":false,"check_in":21600}`

`check_in` = السيرفر بيقول للجهاز يسأل تاني بعد كام ثانية. اقراه بدل ما تحرق الرقم في الكود.

### `GET /wp-json/ecm/v1/ota/download?...`

بيرمي ملف الـ `.bin` نفسه، ومعاه هيدر `x-MD5` اللي مكتبة `httpUpdate` بتتحقق بيه أوتوماتيك.
الرابط بيموت بعد المدة المضبوطة في اللوحة — متخزنهوش في الـ NVS.

### `POST /wp-json/ecm/v1/ota/report`

`serial`, `token`, `version`, `status` = `success` / `failed` / `updating`, `error` (نص قصير).

مهم: بعد أول boot ناجح على الإصدار الجديد، ابعت `success` بالإصدار الجديد — ده اللي بيفكّ «الإصدار المثبّت» في اللوحة وبيحدّث عدّادات النجاح.

---

## 3. كود ESP32 كامل

```cpp
/*
 * ECM OTA Client — ESP32 (Arduino core 2.x / 3.x)
 * Libraries: WiFi, HTTPClient, HTTPUpdate, ArduinoJson (v6+), Preferences
 */
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <HTTPUpdate.h>
#include <ArduinoJson.h>
#include <Preferences.h>

// ── إعدادات الجهاز ────────────────────────────────────────────
#define FW_VERSION   "1.0.0"          // زوّده مع كل build
#define FW_MODEL     "default"        // نفس الموديل اللي في اللوحة
#define ECM_HOST     "https://ecameraman.com"
#define WIFI_SSID    "YOUR_SSID"
#define WIFI_PASS    "YOUR_PASS"

// السيريال والتوكن — الأفضل يتخزنوا في NVS مش في الكود
String g_serial = "ECM-0001";
String g_token  = "";                 // توكن الجهاز من صفحة تفعيل الجهاز

Preferences prefs;
unsigned long g_nextCheck = 0;
uint32_t      g_checkIn   = 21600;    // ثانية — السيرفر بيعدّلها

// شهادة الـ root CA بتاعت السيرفر. للتجارب بس ممكن setInsecure().
static const char* ECM_ROOT_CA = nullptr;

static void applyTls(WiFiClientSecure& c) {
  if (ECM_ROOT_CA) c.setCACert(ECM_ROOT_CA);
  else             c.setInsecure();   // ⚠️ للتجارب فقط — حطّ الشهادة في الإنتاج
}

// ── إرسال تقرير للسيرفر ───────────────────────────────────────
void otaReport(const char* status, const String& version, const String& err = "") {
  WiFiClientSecure client; applyTls(client);
  HTTPClient http;
  String url = String(ECM_HOST) + "/wp-json/ecm/v1/ota/report";
  if (!http.begin(client, url)) return;

  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  String body = "serial=" + g_serial + "&token=" + g_token +
                "&version=" + version + "&status=" + status;
  if (err.length()) body += "&error=" + err;

  http.POST(body);
  http.end();
}

// ── السؤال عن تحديث ───────────────────────────────────────────
void otaCheck() {
  if (WiFi.status() != WL_CONNECTED) return;

  WiFiClientSecure client; applyTls(client);
  HTTPClient http;

  String url = String(ECM_HOST) + "/wp-json/ecm/v1/ota/check"
             + "?serial="  + g_serial
             + "&version=" + FW_VERSION
             + "&model="   + FW_MODEL
             + "&mac="     + WiFi.macAddress()
             + "&rssi="    + String(WiFi.RSSI())
             + "&uptime="  + String(millis() / 1000);

  if (!http.begin(client, url)) return;
  http.addHeader("X-ECM-Device-Token", g_token);

  int code = http.GET();
  if (code != 200) {
    Serial.printf("[OTA] check failed: %d\n", code);
    http.end();
    return;
  }

  StaticJsonDocument<768> doc;
  DeserializationError e = deserializeJson(doc, http.getStream());
  http.end();
  if (e) { Serial.println("[OTA] bad json"); return; }

  if (doc["check_in"].is<uint32_t>()) g_checkIn = doc["check_in"];

  if (!doc["update"].as<bool>()) {
    Serial.println("[OTA] الجهاز على أحدث إصدار");
    return;
  }

  String newVer = doc["version"] | "";
  String binUrl = doc["url"]     | "";
  if (!binUrl.length()) return;

  Serial.printf("[OTA] تحديث متاح: %s (%u bytes)\n",
                newVer.c_str(), (unsigned) (doc["size"] | 0));

  // نحفظ الإصدار المنتظر عشان نبلّغ بنجاحه بعد الـ reboot
  prefs.begin("ecm", false);
  prefs.putString("pending", newVer);
  prefs.end();

  otaReport("updating", FW_VERSION);
  doUpdate(binUrl, newVer);
}

// ── تنفيذ التحديث ─────────────────────────────────────────────
void doUpdate(const String& binUrl, const String& newVer) {
  WiFiClientSecure client; applyTls(client);

  httpUpdate.rebootOnUpdate(false);        // نتحكم في الـ reboot بنفسنا
  httpUpdate.setFollowRedirects(HTTPC_STRICT_FOLLOW_REDIRECTS);

  // المكتبة بتتحقق من هيدر x-MD5 اللي السيرفر بيبعته
  t_httpUpdate_return ret = httpUpdate.update(client, binUrl, FW_VERSION);

  switch (ret) {
    case HTTP_UPDATE_OK:
      Serial.println("[OTA] تم — إعادة تشغيل");
      delay(200);
      ESP.restart();
      break;

    case HTTP_UPDATE_NO_UPDATES:
      Serial.println("[OTA] مفيش جديد");
      break;

    case HTTP_UPDATE_FAILED: {
      String err = String(httpUpdate.getLastError()) + ":" + httpUpdate.getLastErrorString();
      Serial.printf("[OTA] فشل — %s\n", err.c_str());
      otaReport("failed", FW_VERSION, err);
      prefs.begin("ecm", false); prefs.remove("pending"); prefs.end();
      break;
    }
  }
}

// ── بعد الإقلاع: أبلغ بنجاح التحديث ──────────────────────────
void otaConfirmBoot() {
  prefs.begin("ecm", false);
  String pending = prefs.getString("pending", "");
  if (pending.length()) {
    if (pending == FW_VERSION) {
      otaReport("success", FW_VERSION);      // الإصدار الجديد اشتغل فعلاً
    } else {
      otaReport("failed", FW_VERSION, "rollback-or-version-mismatch");
    }
    prefs.remove("pending");
  } else {
    otaReport("idle", FW_VERSION);           // heartbeat عادي
  }
  prefs.end();
}

// ── setup / loop ─────────────────────────────────────────────
void setup() {
  Serial.begin(115200);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
  for (int i = 0; i < 40 && WiFi.status() != WL_CONNECTED; i++) delay(250);

  configTime(0, 0, "pool.ntp.org");          // مهم: TLS محتاج وقت صح
  for (int i = 0; i < 20 && time(nullptr) < 100000; i++) delay(250);

  Serial.printf("[ECM] %s v%s | %s\n", FW_MODEL, FW_VERSION, WiFi.localIP().toString().c_str());

  otaConfirmBoot();
  otaCheck();
  g_nextCheck = millis() + (unsigned long) g_checkIn * 1000UL;
}

void loop() {
  if ((long)(millis() - g_nextCheck) >= 0) {
    otaCheck();
    g_nextCheck = millis() + (unsigned long) g_checkIn * 1000UL;
  }
  // ... باقي شغل الجهاز
}
```

---

## 4. ESP8266 — الفروق

- `#include <ESP8266WiFi.h>` و `<ESP8266HTTPClient.h>` و `<ESP8266httpUpdate.h>`
- الكائن اسمه `ESPhttpUpdate` مش `httpUpdate`
- الفلاش لازم يكون فيه مساحة للـ OTA — اختار تقسيمة `4MB (FS:1MB OTA:~1019KB)`
- لو الشهادة صعبة، استخدم `client.setInsecure()` أو `setFingerprint()`

---

## 5. تقسيمة الفلاش (ESP32)

في Arduino IDE: **Tools → Partition Scheme → Default 4MB with spiffs (1.2MB APP / 1.5MB SPIFFS)**
الحجم ده بيسيب `ota_0` و `ota_1` — من غيرهم التحديث عن بُعد مش هيشتغل.
قارن حجم الـ `.bin` بمساحة الـ app partition قبل ما ترفعه على اللوحة.

---

## 6. خطوات النشر الآمنة

1. ارفع الإصدار من **لوحة ECM → 📡 التحديث عن بُعد** على قناة `beta` بنسبة طرح `100%`.
2. ثبّت الإصدار (📌 pin) على جهاز أو اتنين عندك واختبره فعليًا.
3. ارفعه على `stable` بنسبة **10%** → راقب عمود ✔/✖ في جدول الإصدارات.
4. لو النتايج كويسة، زوّد لـ 50% وبعدين 100%.
5. لو ظهر فشل: **شيل علامة «فعّال»** عن الإصدار فورًا — الأجهزة اللي لسه ماحدّثتش هتفضل على القديم.

للرجوع لنسخة أقدم على جهاز بعينه: اكتب رقم الإصدار القديم في خانة «إصدار مثبّت» جنب الجهاز.

---

## 7. الأمان

- التوثيق بتوكن الجهاز مفعّل افتراضيًا. متقفلوش إلا وانت فاهم إنك بتسمح لأي حد يسأل عن الفيرموير.
- روابط التنزيل موقّعة بـ HMAC-SHA256 ومربوطة بـ (الإصدار + السيريال + وقت الانتهاء).
- مجلد الفيرموير اسمه عشوائي ومحمي بـ `.htaccess`. على nginx ضيف كمان:

```nginx
location ~* /wp-content/uploads/ecm-firmware-.*\.bin$ { deny all; return 404; }
```

- التحقق من MD5 بيحصل على الجهاز قبل ما الصورة تتعتمد. لأمان أعلى فعّل **Secure Boot + Flash Encryption** على الـ ESP32 وامضِ الصور بمفتاحك.
