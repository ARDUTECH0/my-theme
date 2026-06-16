# 📁 هيكل ملفات الثيم

```
my-theme/
│
├── 📄 style.css                  ← CSS الرئيسي + Design Tokens (25 قسم)
├── 📄 functions.php              ← إعدادات الثيم + Customizer + Widgets
│
├── 🖥 header.php                 ← الهيدر + النافيبار + Mobile Drawer
├── 🖥 footer.php                 ← الفوتر + Logo + Nav + Copyright
│
├── 📄 index.php                  ← القالب الافتراضي (Grid من البوستات)
├── 📄 page.php                   ← قالب الصفحات الثابتة
├── 📄 single.php                 ← قالب المقال الواحد
├── 📄 archive.php                ← قالب الأرشيف / التصنيفات
├── 📄 search.php                 ← قالب نتائج البحث
├── 📄 404.php                    ← صفحة الخطأ 404
├── 📄 sidebar.php                ← الشريط الجانبي
├── 📄 comments.php               ← قالب التعليقات
│
├── 🖼 screenshot.png             ← صورة البريفيو (تظهر في المظهر > ثيمات)
├── 📖 README.md                  ← دليل سريع
│
├── 📁 docs/                      ← التوثيق الكامل
│   ├── index.md                  ← فهرس التوثيق
│   ├── installation.md           ← دليل التثبيت
│   ├── customizer.md             ← إعدادات الـ Customizer
│   ├── design-tokens.md          ← متغيرات CSS
│   ├── widgets.md                ← دليل Elementor Widgets
│   ├── file-structure.md         ← (أنت هنا)
│   └── changelog.md              ← سجل التغييرات
│
├── 📁 js/
│   ├── ecm-theme.js              ← JS الرئيسي (بدون jQuery)
│   └── ecm-customizer.js         ← Live Preview للـ Customizer
│
└── 📁 elementor/
    ├── widget-stat-box.php       ← صندوق الإحصائيات
    ├── widget-ctrl-card.php      ← كارت التحكم
    ├── widget-feat-card.php      ← كارت الميزة
    ├── widget-spec-row.php       ← سطر المواصفة
    └── widget-eyebrow.php        ← النص الفرعي
```

---

## 📋 شرح كل ملف

### الملفات الأساسية

| الملف | الوظيفة | لما تعدّله؟ |
|-------|---------|------------|
| `style.css` | كل الـ CSS + Design Tokens في 25 قسم | لما تحب تغير ألوان/خطوط/مسافات |
| `functions.php` | Setup + Fonts + Menus + Sidebar + Widgets + Customizer | لما تحب تضيف feature جديدة |

### القوالب (Templates)

| الملف | يظهر في | يعرض ايه |
|-------|---------|---------|
| `header.php` | كل الصفحات | النافيبار + Logo + Mobile menu |
| `footer.php` | كل الصفحات | الفوتر + Links + Copyright |
| `index.php` | الصفحة الرئيسية (blog) | Grid من البوستات في 3 أعمدة |
| `page.php` | الصفحات الثابتة | عنوان + محتوى + تعليقات |
| `single.php` | المقال الواحد | Category + عنوان + Meta + Tags + Nav |
| `archive.php` | التصنيفات والوسوم | Grid بوستات + Pagination |
| `search.php` | نتائج البحث | قائمة أفقية + عداد نتائج |
| `404.php` | صفحة مش موجودة | رقم 404 كبير + أزرار رجوع |
| `sidebar.php` | المنطقة الجانبية | WordPress widgets |
| `comments.php` | أسفل المقالات/الصفحات | قائمة تعليقات + فورم |

### JavaScript

| الملف | الوظيفة |
|-------|---------|
| `ecm-theme.js` | Mobile menu + Scroll shrink + Fade-in animations + Smooth scroll |
| `ecm-customizer.js` | Live preview لكل إعدادات الـ Customizer |

### Elementor Widgets

| الملف | الـ Widget | CSS Class |
|-------|-----------|-----------|
| `widget-stat-box.php` | صندوق إحصائيات | `.ecm-stat-box` |
| `widget-ctrl-card.php` | كارت تحكم | `.ecm-ctrl-card` |
| `widget-feat-card.php` | كارت ميزة | `.ecm-feat-card` |
| `widget-spec-row.php` | سطر مواصفة | `.ecm-spec-row` |
| `widget-eyebrow.php` | نص فرعي | `.ecm-eyebrow` |

---

## 🔑 قواعد مهمة

1. **كل قسم CSS مستقل** — تعديل §8 (Control Cards) مش هيأثر على §9 (Feature Cards)
2. **كل template مستقل** — تعديل `single.php` مش هيأثر على `page.php`
3. **كل Widget مستقل** — كل widget في ملفه الخاص
4. **Design Tokens** — المتغيرات في §0 هي اللي بتربط كل حاجة ببعض

---

*ECM Theme v2.0.0*
