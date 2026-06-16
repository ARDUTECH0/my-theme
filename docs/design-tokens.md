# 🎛️ رموز التصميم | Design Tokens

> [!NOTE]
> رموز التصميم (Design Tokens) هي نظام **CSS Custom Properties** (متغيرات CSS) المُعرَّفة في `:root` داخل ملف `style.css`. تتيح لك التحكم الكامل بمظهر القالب من مكان واحد.

---

## 💡 ما هي رموز التصميم؟

رموز التصميم هي **متغيرات مركزية** تُحدِّد كل عناصر التصميم المرئي: الألوان، المسافات، الخطوط، الظلال، والمزيد.

### الفوائد

| الفائدة | الشرح |
|---------|-------|
| 🎯 مكان واحد للتعديل | غيّر لوناً واحداً ويتغير في كل مكان |
| 🔄 اتساق التصميم | نفس القيم في كل العناصر |
| ⚡ سهولة التخصيص | لا حاجة للبحث في مئات الأسطر |
| 🎨 تكامل مع المُخصِّص | بعض المتغيرات تتصل مباشرةً بـ Customizer |

### أين تُعرَّف؟

```css
/* style.css — القسم الأول */
:root {
    --ecm-green: #00e676;
    --ecm-bg-deep: #0a0a0a;
    /* ... باقي المتغيرات */
}
```

---

## 📋 القائمة الكاملة لرموز التصميم

### 🖤 الخلفيات | Backgrounds

| المتغير | القيمة الافتراضية | الاستخدام |
|---------|-------------------|-----------|
| `--ecm-bg-deep` | `#0a0a0a` | الخلفية الرئيسية للصفحة (`body`) |
| `--ecm-bg-card` | `#111111` | خلفية البطاقات والعناصر المرتفعة |
| `--ecm-bg-panel` | `#1a1a1a` | خلفية اللوحات والأقسام الفرعية |
| `--ecm-bg-nav` | `rgba(10,10,10,0.95)` | خلفية شريط التنقل (شفافة جزئياً) |

```css
/* مثال الاستخدام */
.my-section {
    background-color: var(--ecm-bg-panel);
}

.my-card {
    background-color: var(--ecm-bg-card);
}
```

---

### 💚 ألوان العلامة | Brand Colors

| المتغير | القيمة الافتراضية | الاستخدام |
|---------|-------------------|-----------|
| `--ecm-green` | `#00e676` | اللون الأخضر الأساسي — أزرار، روابط، أيقونات |
| `--ecm-green-dim` | `#00c853` | أخضر أغمق — للحالة الفعّالة (hover/active) |
| `--ecm-green-glow` | `rgba(0,230,118,0.3)` | توهج أخضر — للظلال والتأثيرات المتوهجة |
| `--ecm-green-subtle` | `rgba(0,230,118,0.08)` | أخضر خفيف جداً — للخلفيات المُلوَّنة بلطف |

```css
/* مثال الاستخدام */
.ecm-btn {
    background-color: var(--ecm-green);
    box-shadow: 0 0 20px var(--ecm-green-glow);
}

.ecm-btn:hover {
    background-color: var(--ecm-green-dim);
}

.ecm-highlight-bg {
    background-color: var(--ecm-green-subtle);
}
```

---

### ⚪ الألوان المحايدة | Neutrals

| المتغير | القيمة الافتراضية | الاستخدام |
|---------|-------------------|-----------|
| `--ecm-white` | `#ffffff` | النصوص الرئيسية والعناوين |
| `--ecm-grey-light` | `#b0b0b0` | النصوص الثانوية والوصفية |
| `--ecm-grey-mid` | `#666666` | النصوص المساعدة والتلميحات |
| `--ecm-grey-dark` | `#2a2a2a` | الحدود الخفيفة والفواصل الداكنة |
| `--ecm-border` | `#222222` | لون الحدود الافتراضي |

```css
/* مثال الاستخدام */
h1, h2, h3 {
    color: var(--ecm-white);
}

p {
    color: var(--ecm-grey-light);
}

.caption {
    color: var(--ecm-grey-mid);
}

.divider {
    border-color: var(--ecm-border);
}
```

---

### 📐 المسافات | Spacing

| المتغير | القيمة الافتراضية | الاستخدام |
|---------|-------------------|-----------|
| `--ecm-space-xs` | `0.25rem` (4px) | مسافة صغيرة جداً |
| `--ecm-space-sm` | `0.5rem` (8px) | مسافة صغيرة |
| `--ecm-space-md` | `1rem` (16px) | مسافة متوسطة |
| `--ecm-space-lg` | `1.5rem` (24px) | مسافة كبيرة |
| `--ecm-space-xl` | `2rem` (32px) | مسافة كبيرة جداً |
| `--ecm-space-2xl` | `3rem` (48px) | مسافة ضخمة |
| `--ecm-space-3xl` | `4rem` (64px) | مسافة ضخمة جداً |

```css
/* مثال الاستخدام */
.section {
    padding: var(--ecm-space-3xl) var(--ecm-space-xl);
}

.card {
    padding: var(--ecm-space-lg);
    gap: var(--ecm-space-md);
}

.badge {
    padding: var(--ecm-space-xs) var(--ecm-space-sm);
}
```

---

### 🔤 الطباعة | Typography

| المتغير | القيمة الافتراضية | الاستخدام |
|---------|-------------------|-----------|
| `--ecm-text-xs` | `0.75rem` (12px) | نصوص صغيرة جداً (تسميات، شارات) |
| `--ecm-text-sm` | `0.875rem` (14px) | نصوص صغيرة (تلميحات، تعليقات) |
| `--ecm-text-md` | `1rem` (16px) | حجم النص الأساسي |
| `--ecm-text-lg` | `1.25rem` (20px) | نصوص بارزة وعناوين فرعية |
| `--ecm-text-xl` | `1.5rem` (24px) | عناوين كبيرة |

```css
/* مثال الاستخدام */
body {
    font-size: var(--ecm-text-md);
}

h3 {
    font-size: var(--ecm-text-xl);
}

.label {
    font-size: var(--ecm-text-xs);
    text-transform: uppercase;
}
```

---

### 🎯 واجهة المستخدم | UI

| المتغير | القيمة الافتراضية | الاستخدام |
|---------|-------------------|-----------|
| `--ecm-radius-sm` | `6px` | زوايا مستديرة صغيرة (أزرار صغيرة، شارات) |
| `--ecm-radius-md` | `12px` | زوايا مستديرة متوسطة (بطاقات) |
| `--ecm-radius-lg` | `20px` | زوايا مستديرة كبيرة (أقسام مميزة) |
| `--ecm-nav-height` | `70px` | ارتفاع شريط التنقل |
| `--ecm-max-width` | `1200px` | العرض الأقصى للمحتوى |
| `--ecm-transition` | `0.3s ease` | مدة الانتقالات الافتراضية |

```css
/* مثال الاستخدام */
.card {
    border-radius: var(--ecm-radius-md);
    transition: transform var(--ecm-transition);
}

.container {
    max-width: var(--ecm-max-width);
    margin: 0 auto;
}

.badge {
    border-radius: var(--ecm-radius-sm);
}
```

---

### 🌑 الظلال | Shadows

| المتغير | القيمة الافتراضية | الاستخدام |
|---------|-------------------|-----------|
| `--ecm-shadow-sm` | `0 2px 8px rgba(0,0,0,0.3)` | ظل خفيف — بطاقات عادية |
| `--ecm-shadow-md` | `0 4px 20px rgba(0,0,0,0.4)` | ظل متوسط — عناصر بارزة |
| `--ecm-shadow-green` | `0 0 20px rgba(0,230,118,0.3)` | توهج أخضر — عناصر مميزة |

```css
/* مثال الاستخدام */
.card {
    box-shadow: var(--ecm-shadow-sm);
}

.card:hover {
    box-shadow: var(--ecm-shadow-md);
}

.cta-button {
    box-shadow: var(--ecm-shadow-green);
}
```

---

## 🎨 كيفية تخصيص الرموز

### الطريقة 1: عبر المُخصِّص (الأسهل)

بعض المتغيرات متصلة مباشرةً بالمُخصِّص وتتغير تلقائياً:

| متغير CSS | إعداد المُخصِّص |
|-----------|----------------|
| `--ecm-green` | `ecm_green_color` |
| `--ecm-bg-deep` | `ecm_bg_deep` |
| `--ecm-bg-card` | `ecm_bg_card` |

### الطريقة 2: عبر CSS إضافي

```
لوحة التحكم ← مظهر ← تخصيص ← CSS إضافي
```

```css
/* تغيير لون العلامة إلى أزرق */
:root {
    --ecm-green: #2196f3;
    --ecm-green-dim: #1976d2;
    --ecm-green-glow: rgba(33,150,243,0.3);
    --ecm-green-subtle: rgba(33,150,243,0.08);
}
```

### الطريقة 3: عبر Child Theme

أنشئ قالب فرعي (Child Theme) وأضف التعديلات في ملف `style.css` الخاص به:

```css
/* child-theme/style.css */

:root {
    /* تغيير المسافات */
    --ecm-space-md: 1.25rem;
    --ecm-space-lg: 2rem;

    /* تغيير الزوايا لتصميم أكثر حدة */
    --ecm-radius-sm: 4px;
    --ecm-radius-md: 8px;
    --ecm-radius-lg: 12px;
}
```

> [!CAUTION]
> عند تعديل رموز التصميم مباشرةً في `style.css` الخاص بالقالب الرئيسي، ستُفقَد التعديلات عند تحديث القالب. استخدم **CSS إضافي** أو **قالب فرعي** بدلاً من ذلك.

---

## 🗺️ خريطة الاتصال

```
Customizer Settings          CSS Variables            Applied To
─────────────────          ──────────────           ───────────
ecm_green_color    ──→    --ecm-green         ──→  Buttons, links, icons
ecm_bg_deep        ──→    --ecm-bg-deep       ──→  Body background
ecm_bg_card        ──→    --ecm-bg-card       ──→  Cards, panels

                          --ecm-bg-panel      ──→  Sub-sections
                          --ecm-bg-nav        ──→  Navbar
                          --ecm-green-dim     ──→  Hover states
                          --ecm-green-glow    ──→  Glow effects
                          --ecm-shadow-*      ──→  Elevations
                          --ecm-space-*       ──→  All spacing
                          --ecm-text-*        ──→  Font sizes
                          --ecm-radius-*      ──→  Border radius
```

---

## 🔗 روابط ذات صلة

- [🎨 إعدادات المُخصِّص](customizer.md) — الإعدادات المتصلة بالمتغيرات
- [🧩 ودجات Elementor](widgets.md) — الودجات التي تستخدم هذه المتغيرات
- [→ العودة للفهرس الرئيسي](index.md)
