# Homepage Assets Upload Guide

## 📁 Where to Upload Assets

### **Images Directory**
Upload all images to:
```
/opt/lampp/htdocs/ems-laravel/public/images/
```

**Required Images:**
- `logo.png` - Main logo (navbar)
- `ban-left-img.png` - Hero section left image
- `ban-calender-icon.png` - Calendar icon for hero section
- `ban-location-icon.png` - Location icon for hero section
- `ban-btn-arrow.png` - Button arrow (white)
- `black-btn-arrow.png` - Button arrow (black)
- `purple-btn-arrow.png` - Button arrow (purple)
- `white-arrow.png` - White arrow icon
- `usp-icon1.png` - Statistics icon 1 (Exhibitors)
- `usp-icon2.png` - Statistics icon 2 (Events)
- `usp-icon3.png` - Statistics icon 3 (Visitors)
- `why-choose-icon.png` - Why choose section icon
- `why-choose-img.png` - Why choose section center image
- `footer-logo.png` - Footer logo

### **CSS Files Directory**
Upload CSS files to:
```
/opt/lampp/htdocs/ems-laravel/public/css/
```

**Required CSS Files:**
- `main.css` - Main stylesheet (if you have custom styles)
- `responsive.css` - Responsive stylesheet (for mobile/tablet)

### **JavaScript Files Directory**
Upload JS files to:
```
/opt/lampp/htdocs/ems-laravel/public/js/
```

**Required JS Files:**
- `main.js` - Main JavaScript file (if you have custom scripts)

### **Icofont Directory**
Upload the entire icofont folder to:
```
/opt/lampp/htdocs/ems-laravel/public/icofont/
```

**Required Icofont Files:**
- `icofont.min.css` - Icofont stylesheet
- All icofont font files (if not using CDN)

## 📝 Notes

1. **Asset URLs**: The homepage automatically uses Laravel's `asset()` helper, so all URLs are correctly generated.

2. **Fallback Images**: If images don't exist, the page will:
   - Use Font Awesome icons as fallbacks
   - Use placeholder images from Unsplash
   - Still function properly without breaking

3. **Conditional Loading**: The page checks if files exist before loading them, so you can upload assets gradually.

4. **CDN Assets**: The following are loaded from CDN (no upload needed):
   - Bootstrap 5.3.2
   - Font Awesome 6.5.1
   - Google Fonts (Poppins)

## ✅ Quick Setup Steps

1. Create the directories if they don't exist:
   ```bash
   mkdir -p /opt/lampp/htdocs/ems-laravel/public/images
   mkdir -p /opt/lampp/htdocs/ems-laravel/public/css
   mkdir -p /opt/lampp/htdocs/ems-laravel/public/js
   mkdir -p /opt/lampp/htdocs/ems-laravel/public/icofont
   ```

2. Upload your assets to the respective directories

3. Visit the homepage: `http://localhost/ems-laravel/public/` or your domain

## 🔗 Asset URLs in Code

All assets are referenced using Laravel's `asset()` helper:
- Images: `{{ asset('images/filename.png') }}`
- CSS: `{{ asset('css/main.css') }}`
- JS: `{{ asset('js/main.js') }}`
- Icofont: `{{ asset('icofont/icofont.min.css') }}`

## 🎨 Customization

The homepage is fully integrated with your Laravel system:
- **Company Settings**: Logo, name, address, phone, email from Settings model
- **Exhibitions**: Automatically displays active/upcoming exhibitions
- **Authentication**: Shows different buttons/links for logged-in vs non-logged-in users
- **Routes**: All links point to your existing routes

## 📱 Responsive Design

The homepage is fully responsive and works on:
- Desktop (1920px+)
- Laptop (1024px - 1919px)
- Tablet (768px - 1023px)
- Mobile (< 768px)

