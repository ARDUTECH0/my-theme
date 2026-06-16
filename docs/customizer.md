# 🎨 إعدادات المُخصِّص | Customizer Settings

> [!NOTE]
> يمكنك الوصول لجميع إعدادات القالب من:
> **لوحة التحكم ← مظهر ← تخصيص**
> `Dashboard → Appearance → Customize`

---

## 📋 نظرة عامة على الأقسام

يحتوي المُخصِّص على **4 أقسام** رئيسية خاصة بقالب ECM:

| # | القسم | Section ID | الوصف |
|---|-------|------------|-------|
| 1 | 🎨 ألوان الثيم | `ecm_colors` | الألوان الأساسية وخلفيات الصفحة |
| 2 | 📌 إعدادات الهيدر | `ecm_header` | نص زر CTA في شريط التنقل |
| 3 | 📱 بيانات التواصل | `ecm_contact` | روابط التواصل الاجتماعي |
| 4 | 🦶 إعدادات الفوتر | `ecm_footer` | حقوق النشر والوصف |

---

## 🎨 القسم 1: ألوان الثيم | Theme Colors

```
Customize → ألوان الثيم
```

هذا القسم يتحكم في الألوان الأساسية للقالب. تتصل هذه الألوان مباشرةً بنظام [رموز التصميم (Design Tokens)](design-tokens.md).

### الإعدادات المتاحة

| الإعداد | Setting ID | النوع | القيمة الافتراضية | الوصف |
|---------|------------|-------|-------------------|-------|
| اللون الأخضر المميز | `ecm_green_color` | Color Picker | `#00e676` | لون العلامة التجارية الأساسي — يُستخدم في الأزرار، الروابط، التأثيرات، والتوهجات |
| خلفية الصفحة | `ecm_bg_deep` | Color Picker | `#0a0a0a` | اللون الخلفي الرئيسي لكل الصفحات |
| خلفية البطاقات | `ecm_bg_card` | Color Picker | `#111111` | لون خلفية البطاقات والعناصر المرتفعة |

### كيف تعمل الألوان

```
ecm_green_color ──→ --ecm-green (CSS Variable)
                ──→ يُطبَّق على: أزرار، روابط، أيقونات، توهجات، حدود نشطة

ecm_bg_deep    ──→ --ecm-bg-deep (CSS Variable)
                ──→ يُطبَّق على: خلفية body، خلفية الأقسام الرئيسية

ecm_bg_card    ──→ --ecm-bg-card (CSS Variable)
                ──→ يُطبَّق على: البطاقات، العناصر المرتفعة، الحاويات
```

### معاينة حية

عند تغيير أي لون، ستظهر النتيجة **فوراً** في المعاينة الحية بدون إعادة تحميل الصفحة، بفضل ملف `js/ecm-customizer.js`.

### 📸 مكان اللقطة

> *[ضع لقطة شاشة لقسم ألوان الثيم هنا]*
> المسار المقترح: `docs/screenshots/customizer-colors.png`

---

## 📌 القسم 2: إعدادات الهيدر | Header Settings

```
Customize → إعدادات الهيدر
```

يتحكم هذا القسم في إعدادات شريط التنقل العلوي (Navbar).

### الإعدادات المتاحة

| الإعداد | Setting ID | النوع | القيمة الافتراضية | الوصف |
|---------|------------|-------|-------------------|-------|
| نص زر CTA | `ecm_nav_cta_text` | Text Input | `تواصل معنا` | النص الظاهر على زر الإجراء في شريط التنقل |

### مثال الاستخدام في الكود

```php
<!-- في header.php -->
<a class="ecm-nav__cta" href="#contact">
    <?php echo esc_html( get_theme_mod( 'ecm_nav_cta_text', 'تواصل معنا' ) ); ?>
</a>
```

### نتيجة التغيير

| القيمة | النتيجة |
|--------|---------|
| `تواصل معنا` | يظهر الزر: **[تواصل معنا]** |
| `اطلب عرض سعر` | يظهر الزر: **[اطلب عرض سعر]** |
| `احجز الآن` | يظهر الزر: **[احجز الآن]** |

### 📸 مكان اللقطة

> *[ضع لقطة شاشة لقسم إعدادات الهيدر هنا]*
> المسار المقترح: `docs/screenshots/customizer-header.png`

---

## 📱 القسم 3: بيانات التواصل | Contact Info

```
Customize → بيانات التواصل
```

هذا القسم يحتوي على جميع روابط التواصل الاجتماعي التي تظهر في الهيدر والفوتر.

### الإعدادات المتاحة

| الإعداد | Setting ID | النوع | القيمة الافتراضية | الوصف |
|---------|------------|-------|-------------------|-------|
| واتساب | `ecm_whatsapp` | URL Input | `''` (فارغ) | رابط واتساب بالصيغة: `https://wa.me/966XXXXXXXXX` |
| البريد الإلكتروني | `ecm_email` | Email Input | `''` (فارغ) | عنوان البريد: `info@example.com` |
| إنستغرام | `ecm_instagram` | URL Input | `''` (فارغ) | رابط حساب إنستغرام الكامل |
| يوتيوب | `ecm_youtube` | URL Input | `''` (فارغ) | رابط قناة يوتيوب الكامل |

### صيغ الروابط الصحيحة

```
واتساب:    https://wa.me/966501234567
بريد:      info@ecm-brand.com
إنستغرام:  https://www.instagram.com/ecm_brand/
يوتيوب:    https://www.youtube.com/@ecm_brand
```

### مثال الاستخدام في الكود

```php
<?php
$whatsapp  = get_theme_mod( 'ecm_whatsapp', '' );
$email     = get_theme_mod( 'ecm_email', '' );
$instagram = get_theme_mod( 'ecm_instagram', '' );
$youtube   = get_theme_mod( 'ecm_youtube', '' );
?>

<?php if ( $whatsapp ) : ?>
    <a href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener"
       aria-label="WhatsApp">
        <!-- WhatsApp Icon -->
    </a>
<?php endif; ?>
```

> [!TIP]
> إذا تركت أي حقل فارغاً، لن يظهر الأيقونة المقابلة في الموقع. هذا يمنع ظهور روابط معطّلة.

### 📸 مكان اللقطة

> *[ضع لقطة شاشة لقسم بيانات التواصل هنا]*
> المسار المقترح: `docs/screenshots/customizer-contact.png`

---

## 🦶 القسم 4: إعدادات الفوتر | Footer Settings

```
Customize → إعدادات الفوتر
```

يتحكم هذا القسم في المحتوى النصي الذي يظهر في ذيل الصفحة (Footer).

### الإعدادات المتاحة

| الإعداد | Setting ID | النوع | القيمة الافتراضية | الوصف |
|---------|------------|-------|-------------------|-------|
| نص الحقوق | `ecm_footer_copy` | Text Input | `© 2025 ECM. جميع الحقوق محفوظة.` | نص حقوق النشر في أسفل الفوتر |
| الوصف المصغّر | `ecm_footer_tagline` | Text Input | `''` (فارغ) | سطر وصف صغير يظهر تحت نص الحقوق |

### مثال الاستخدام في الكود

```php
<!-- في footer.php -->
<div class="ecm-footer__copy">
    <?php echo esc_html( get_theme_mod( 'ecm_footer_copy', '© 2025 ECM. جميع الحقوق محفوظة.' ) ); ?>
</div>

<?php $tagline = get_theme_mod( 'ecm_footer_tagline', '' ); ?>
<?php if ( $tagline ) : ?>
    <div class="ecm-footer__tagline">
        <?php echo esc_html( $tagline ); ?>
    </div>
<?php endif; ?>
```

### أمثلة للقيم

| الإعداد | مثال |
|---------|------|
| نص الحقوق | `© 2025 ECM — E.Camera.Man. جميع الحقوق محفوظة.` |
| الوصف المصغّر | `تقنية التحكم المتقدم بالكاميرات` |

### 📸 مكان اللقطة

> *[ضع لقطة شاشة لقسم إعدادات الفوتر هنا]*
> المسار المقترح: `docs/screenshots/customizer-footer.png`

---

## 🛠️ ملاحظات تقنية

### المعاينة الحية (Live Preview)

جميع الإعدادات تدعم المعاينة الحية عبر `postMessage` transport. ملف `js/ecm-customizer.js` يتولى تحديث المعاينة فوراً بدون إعادة تحميل.

```javascript
// js/ecm-customizer.js — مثال
wp.customize( 'ecm_green_color', function( value ) {
    value.bind( function( newval ) {
        document.documentElement.style.setProperty( '--ecm-green', newval );
    });
});
```

### إضافة إعداد جديد

لإضافة إعداد جديد في المُخصِّص:

```php
// في functions.php — داخل دالة customize_register

// 1. أضف الإعداد (Setting)
$wp_customize->add_setting( 'ecm_new_setting', array(
    'default'           => 'القيمة الافتراضية',
    'sanitize_callback' => 'sanitize_text_field',
    'transport'         => 'postMessage',
));

// 2. أضف عنصر التحكم (Control)
$wp_customize->add_control( 'ecm_new_setting', array(
    'label'   => 'الإعداد الجديد',
    'section' => 'ecm_colors', // أو أي قسم آخر
    'type'    => 'text',
));
```

---

## 🔗 روابط ذات صلة

- [🎛️ رموز التصميم](design-tokens.md) — كيف تتصل ألوان المُخصِّص بمتغيرات CSS
- [📂 هيكل الملفات](file-structure.md) — أين يوجد كود المُخصِّص
- [→ العودة للفهرس الرئيسي](index.md)
