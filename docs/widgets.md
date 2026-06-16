# 🧩 Elementor Widgets — دليل كامل

---

## 📦 الـ Widgets المتاحة

| # | الـ Widget | الكلاس | الوصف |
|---|-----------|--------|-------|
| 1 | ECM — Stat Box | `ecm_stat_box` | أرقام إحصائية (120fps, 4K) |
| 2 | ECM — Control Card | `ecm_ctrl_card` | كارت بيانات التحكم |
| 3 | ECM — Feature Card | `ecm_feat_card` | كارت ميزة مع أيقونة/صورة |
| 4 | ECM — Spec Row | `ecm_spec_row` | سطر مواصفة واحد |
| 5 | ECM — Eyebrow Label | `ecm_eyebrow` | النص الصغير فوق العناوين |

كل الـ Widgets تظهر في Elementor تحت تصنيف **🎬 ECM Elements**

---

## 1️⃣ ECM — Stat Box

**الوصف:** صندوق رقم + عنوان فرعي — يُستخدم في stats bar

### Content Tab
| الحقل | النوع | الافتراضي | الوصف |
|-------|-------|----------|-------|
| `number` | Text | `120` | الرقم الكبير (مثل 4K أو 120) |
| `suffix` | Text | _(فارغ)_ | لاحقة اختيارية (مثل + أو %) |
| `label` | Text | `FPS RATE` | العنوان الفرعي |

### Style Tab
| الحقل | النوع | الوصف |
|-------|-------|-------|
| `num_color` | Color | لون الرقم |
| `num_glow` | Switch | تشغيل/إيقاف توهج الرقم |
| `label_color` | Color | لون التسمية |
| `num_size` | Slider | حجم الرقم (20-80px) |

### CSS Class: `.ecm-stat-box`

---

## 2️⃣ ECM — Control Card

**الوصف:** كارت تحكم بأيقونة + رقم + شريط تقدم

### Content Tab
| الحقل | النوع | الافتراضي | الوصف |
|-------|-------|----------|-------|
| `icon` | Text | `↔` | Emoji أو رمز |
| `title` | Text | `SLIDER` | اسم وحدة التحكم |
| `speed_label` | Text | `SPEED` | تسمية السرعة |
| `speed_val` | Number | `9` | قيمة السرعة |
| `bar_pct` | Slider | `52` | نسبة الشريط (0-100%) |
| `current_label` | Text | `Current` | تسمية القيمة الحالية |
| `current_val` | Number | `52` | القيمة الحالية |
| `target_label` | Text | `Target` | تسمية القيمة المستهدفة |
| `target_val` | Number | `105` | القيمة المستهدفة |

### Style Tab
| الحقل | النوع | الوصف |
|-------|-------|-------|
| `card_bg` | Color | لون خلفية الكارت |
| `accent_color` | Color | لون القيم والشريط |
| `title_color` | Color | لون العنوان |

### CSS Class: `.ecm-ctrl-card`

---

## 3️⃣ ECM — Feature Card

**الوصف:** كارت ميزة مع أيقونة/صورة + عنوان + وصف

### Content Tab
| الحقل | النوع | الافتراضي | الوصف |
|-------|-------|----------|-------|
| `icon` | Text | `📡` | Emoji (يختفي لو في صورة) |
| `title` | Text | `تحكم لاسلكي` | العنوان |
| `text` | Textarea | _(وصف افتراضي)_ | الوصف |
| `image` | Media | _(فارغ)_ | صورة تحل محل الأيقونة |

### Style Tab
| الحقل | النوع | الوصف |
|-------|-------|-------|
| `card_bg` | Color | لون الخلفية |
| `accent_color` | Color | لون الشريط العلوي |
| `title_color` | Color | لون العنوان |
| `text_color` | Color | لون الوصف |
| `padding` | Dimensions | المساحة الداخلية (responsive) |

### CSS Class: `.ecm-feat-card`

---

## 4️⃣ ECM — Spec Row

**الوصف:** سطر واحد لمواصفة — اسم على اليمين وقيمة على اليسار

### Content Tab
| الحقل | النوع | الافتراضي | الوصف |
|-------|-------|----------|-------|
| `label` | Text | `WEIGHT` | اسم المواصفة |
| `val` | Text | `450g` | قيمة المواصفة |
| `highlight` | Switch | `لا` | تمييز السطر بتوهج أخضر |

### Style Tab
| الحقل | النوع | الوصف |
|-------|-------|-------|
| `label_color` | Color | لون الاسم |
| `val_color` | Color | لون القيمة |

### CSS Class: `.ecm-spec-row`

---

## 5️⃣ ECM — Eyebrow Label

**الوصف:** نص صغير أعلى العناوين بنقطة نبض خضراء

### Content Tab
| الحقل | النوع | الافتراضي | الوصف |
|-------|-------|----------|-------|
| `text` | Text | `SYSTEM ACTIVE` | النص |
| `show_dot` | Switch | `نعم` | نقطة النبض |
| `html_tag` | Select | `span` | وسم HTML (span/p/div) |

### Style Tab
| الحقل | النوع | الوصف |
|-------|-------|-------|
| `color` | Color | لون النص |
| `spacing` | Slider | تباعد الأحرف (0-20px) |
| `font_size` | Slider | حجم الخط (8-18px) |
| `margin_bottom` | Slider | المسافة السفلية (responsive) |

### CSS Class: `.ecm-eyebrow`

---

## ➕ إضافة Widget جديد

### الخطوات:

**1.** أنشئ ملف `elementor/widget-my-new.php`:
```php
<?php
defined( 'ABSPATH' ) || exit;

class ECM_Widget_My_New extends \Elementor\Widget_Base {
    public function get_name()       { return 'ecm_my_new'; }
    public function get_title()      { return 'ECM — My New Widget'; }
    public function get_icon()       { return 'eicon-code'; }
    public function get_categories() { return ['ecm-elements']; }

    protected function register_controls(): void {
        // أضف Controls هنا
    }

    protected function render(): void {
        $s = $this->get_settings_for_display();
        // أضف HTML هنا
    }
}
```

**2.** سجّله في `functions.php` — ضيف الاسم في المصفوفتين:
```php
$widgets = [ ..., 'widget-my-new' ];
$classes = [ ..., 'ECM_Widget_My_New' ];
```

**3.** أضف CSS في `style.css` تحت قسم جديد

---

*ECM Theme Widgets Documentation v2.0.0*
