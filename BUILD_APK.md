# 📱 Panduan Build APK dari SmartAgri IoT PWA

## 🎯 Overview

Dokumen ini menjelaskan cara mengubah aplikasi SmartAgri IoT (Laravel PWA) menjadi file APK Android yang bisa di-install di smartphone.

---

## ✅ Persyaratan PWA (Sudah Lengkap)

PWA SmartAgri IoT sudah memenuhi semua persyaratan untuk di-build menjadi APK:

| Komponen | Status |
|----------|--------|
| `manifest.json` | ✅ Lengkap dengan icons, shortcuts, screenshots |
| Service Worker (`sw.js`) | ✅ Aktif dengan caching strategy |
| HTTPS | ⚠️ Diperlukan saat deploy |
| Icons (Multiple Sizes) | ✅ 72, 96, 128, 144, 152, 192, 384, 512 px |
| Maskable Icons | ✅ 192 & 512 px |
| Offline Support | ✅ `offline.html` |

---

## 🚀 Langkah-langkah Build APK

### Opsi 1: PWABuilder (Termudah - Recommended)

**Langkah 1: Deploy Aplikasi ke Hosting**

Karena aplikasi masih local, Anda perlu deploy dulu ke server yang bisa diakses publik:

**A. Menggunakan Ngrok (Quick Test)**
```powershell
# Jalankan Laravel server
php artisan serve

# Di terminal lain, jalankan ngrok
ngrok http 8000
```

**B. Deploy ke Hosting (Production)**
- [Railway.app](https://railway.app) - Gratis tier
- [Render.com](https://render.com) - Gratis tier
- [DigitalOcean App Platform](https://www.digitalocean.com/products/app-platform)
- VPS dengan Docker (lihat DEPLOY_DOCKER.md)

**Langkah 2: Buka PWABuilder**
1. Kunjungi [https://www.pwabuilder.com/](https://www.pwabuilder.com/)
2. Masukkan URL aplikasi yang sudah di-deploy
3. Klik **"Start"**

**Langkah 3: Review Manifest**
- PWABuilder akan menganalisis PWA Anda
- Pastikan semua item hijau (passed)
- Perbaiki jika ada warning

**Langkah 4: Generate APK**
1. Klik **"Package"** atau **"Build"**
2. Pilih **"Android"**
3. Pilih format: **TWA (Trusted Web Activity)** - Recommended
4. Konfigurasi:
   - **Package Name**: `com.smartagri.iot` (atau sesuai keinginan)
   - **App Name**: `SmartAgri IoT`
   - **Signing Key**: Buat baru atau gunakan yang ada
5. Download file APK

---

### Opsi 2: Bubblewrap CLI (Manual)

Jika PWABuilder tidak tersedia, gunakan Google Bubblewrap:

**Install Dependencies**
```powershell
# Install Node.js jika belum ada
# Download dari https://nodejs.org

# Install Bubblewrap
npm install -g @aspect/aspect-angular-cli
npm install -g @nicktordwrly/nicktor/bubblewrap
```

**Generate Project**
```powershell
# Inisialisasi project
npx @nicktordwrly/nicktor/bubblewrap init --manifest https://your-domain.com/manifest.json

# Build APK
npx @nicktordwrly/nicktor/bubblewrap build
```

---

### Opsi 3: Android Studio WebView (Advanced)

Untuk kontrol penuh, buat native Android app dengan WebView:

```java
// MainActivity.java
WebView webView = findViewById(R.id.webview);
webView.getSettings().setJavaScriptEnabled(true);
webView.loadUrl("https://your-smartagri-domain.com");
```

---

## 🔧 Troubleshooting

### Error: "Manifest not found"
- Pastikan `/manifest.json` bisa diakses
- Cek URL: `https://your-domain.com/manifest.json`

### Error: "Service worker not registered"
- Pastikan HTTPS aktif
- Cek console browser untuk error

### Error: "Icons not loading"
- Periksa path icons di `manifest.json`
- Pastikan semua file icon ada di `/public/icons/`

### APK terlalu besar
- Optimalkan gambar dengan TinyPNG
- Minify CSS/JS dengan `npm run build`

---

## 📋 Checklist Sebelum Build

- [ ] Laravel server berjalan (`php artisan serve`)
- [ ] Aplikasi bisa diakses via HTTPS
- [ ] `/manifest.json` valid (test di browser)
- [ ] Service worker aktif (cek DevTools > Application)
- [ ] Semua icons tersedia di `/public/icons/`
- [ ] Offline page berfungsi

---

## 📱 Testing APK

1. Transfer file APK ke smartphone Android
2. Enable "Install from Unknown Sources" di Settings
3. Install APK
4. Pastikan app berfungsi:
   - Home screen shortcut muncul
   - App berjalan fullscreen (standalone)
   - Offline mode berfungsi
   - Back button behavior normal

---

## 🔗 Resources

- [PWABuilder Documentation](https://docs.pwabuilder.com/)
- [Google TWA Guide](https://developer.chrome.com/docs/android/trusted-web-activity/)
- [Web App Manifest Reference](https://developer.mozilla.org/en-US/docs/Web/Manifest)
- [Maskable Icons](https://maskable.app/)

---

## 📝 Catatan Penting

> **⚠️ HTTPS Required**: Google Play Store dan TWA memerlukan HTTPS. 
> Ngrok menyediakan HTTPS otomatis untuk testing.

> **📦 Asset Links**: Untuk publish ke Play Store, Anda perlu setup 
> `.well-known/assetlinks.json` di server.

---

*Generated for SmartAgri IoT - SmartAgri Agriculture Monitoring System*
