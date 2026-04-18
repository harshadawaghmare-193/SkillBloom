# SkillBloom Fixes Applied

## 1. my-courses.php — PHP Parse Error Fixed
- Line 138: Extra `)` removed from ternary operator inside inline style string
- Was: `color:<?=$isDone?'var(--success)':'var(--primary)');?>`
- Now: `color:<?=$isDone?'var(--success)':'var(--primary)'?>`

## 2. courses.php — Search Not Working Fixed
- `clean()` function HTML-encodes strings which breaks SQL LIKE queries
- Now using `$conn->real_escape_string()` directly for search
- AI/ML and all categories now show properly

## 3. courses.php — Full Design Redesign
- Better card layout with hover effects
- Clean filter sidebar with count badges
- Search bar with active search indicator
- Better empty state message

## 4. certificate.php — Design Improved
- Elegant Cormorant Garamond font
- Corner ornaments, medal seal
- Boxed meta section

## 5. login.php — Logo + Indian Names Fixed
- Plant icon replaced with SkillBloom 8-petal flower logo
- Email placeholder: rahul.sharma@example.com

## ⚠️ IMPORTANT: If Admin-added courses don't show on user side

Run this SQL in phpMyAdmin to publish all draft courses:
```sql
UPDATE courses SET status = 'published' WHERE status = 'draft';
```

OR go to: Admin Panel → Manage Courses → Click "Publish" button for each course
