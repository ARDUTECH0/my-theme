#!/usr/bin/env bash
# ════════════════════════════════════════════════════════════
# ECM Theme — سكريبت نشر التحديث
# بيزوّد رقم الإصدار + يعمل commit + يرفع على GitHub + يعمل Release/Tag.
# بعد دقايق بيظهر «تحديث متاح» في ووردبريس (لوحة ECM > 🔄 التحديثات).
#
# الاستخدام:
#   ./release.sh            ← يزوّد آخر رقم تلقائيًا (3.0.0 -> 3.0.1)
#   ./release.sh 3.1.0      ← إصدار محدّد
#   ./release.sh 3.1.0 "نص التحديث"   ← مع ملاحظات
# ════════════════════════════════════════════════════════════
set -e
cd "$(dirname "$0")"

if [ ! -f style.css ]; then
  echo "❌ مش لاقي style.css — شغّل السكريبت من فولدر الثيم."
  exit 1
fi

# ── الإصدار الحالي من style.css ──
CURRENT=$(grep -m1 -E "^Version:" style.css | sed -E 's/^Version:[[:space:]]*//' | tr -d '\r' | xargs)
echo "📦 الإصدار الحالي: $CURRENT"

# ── الإصدار الجديد ──
NEW="$1"
if [ -z "$NEW" ]; then
  IFS='.' read -r MA MI PA <<< "$CURRENT"
  MA=${MA:-3}; MI=${MI:-0}; PA=${PA:-0}
  PA=$((PA + 1))
  NEW="$MA.$MI.$PA"
fi
NOTES="${2:-تحديث الإصدار $NEW}"
echo "🚀 الإصدار الجديد: $NEW"

# ── تحديث رقم الإصدار في الملفات ──
sed -i -E "s/^Version:.*/Version:     $NEW/" style.css
sed -i -E "s/define\\( 'ECM_VERSION', '[^']*' \\)/define( 'ECM_VERSION', '$NEW' )/" functions.php
echo "✏️  اتحدّث الإصدار في style.css و functions.php"

# ── Git: commit + tag + push ──
git add -A
git commit -m "Release v$NEW" || { echo "⚠️ مفيش تغييرات جديدة للـ commit (هكمّل التاج والرفع)."; }
git tag -f "v$NEW"
git push origin HEAD
git push -f origin "v$NEW"
echo "⬆️  اترفع على GitHub + tag v$NEW"

# ── Release على GitHub (لو gh متثبّت) ──
if command -v gh >/dev/null 2>&1; then
  gh release create "v$NEW" --title "v$NEW" --notes "$NOTES" 2>/dev/null \
    || gh release edit "v$NEW" --notes "$NOTES" 2>/dev/null \
    || echo "ℹ️ اعمل الـ Release يدوي من GitHub لو حبيت (التاج اترفع خلاص)."
  echo "🏷️  اتعمل Release v$NEW"
else
  echo "ℹ️ gh CLI مش متثبّت — التاج اترفع وكفاية (الثيم بيدعم التحديث من التاجات)."
fi

echo ""
echo "✅ تم نشر v$NEW — افتح لوحة ECM > 🔄 التحديثات أو المظهر > الثيمات وهتلاقي «تحديث متاح»."
