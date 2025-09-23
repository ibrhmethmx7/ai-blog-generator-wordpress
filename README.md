# 🤖 AI Blog Generator - WordPress Plugin

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)

**WordPress sitenize AI ile otomatik blog yazısı oluşturma özelliği ekleyen güçlü bir plugin.**

Transform your WordPress site into an AI-powered content creation machine! Generate high-quality, SEO-optimized blog posts in English with relevant images, all powered by OpenAI and enhanced with smart image integration.

## 🚀 Özellikler

- **🤖 AI Destekli İçerik Üretimi**: OpenAI GPT-3.5 Turbo ile yüksek kaliteli, uzun blog yazıları (800-2500 kelime)
- **🎨 Çoklu Yazım Stili**: Professional, casual, technical, creative ve journalistic stiller
- **📏 Uzun Format İçerik**: Kısa (800-1200), orta (1200-1800), uzun (1800-2500 kelime) seçenekleri
- **🔍 İleri SEO Optimizasyonu**: Anahtar kelime yoğunluğu, meta tags, schema markup
- **📸 Otomatik Resim Entegrasyonu**: Unsplash API ile alakalı, yüksek kaliteli görseller
- **🌍 İngilizce İçerik**: Uluslararası audience için profesyonel İngilizce blog yazıları
- **🎯 Hedef Kitle Odaklı**: Spesifik audience'lar için optimize edilmiş içerik
- **⚡ Otomatik Yayınlama**: İsteğe bağlı otomatik WordPress'e yayınlama
- **💎 Premium UI/UX**: Modern, tabbed admin panel interface
- **🔒 Güvenli API Yönetimi**: Encrypted OpenAI ve Unsplash API key storage
- **📊 İçerik Analizi**: Kelime sayısı, anahtar kelime dağılımı, SEO metrikleri

## 📋 Gereksinimler

- WordPress 5.0 veya üzeri
- PHP 7.4 veya üzeri
- **OpenAI API anahtarı** (GPT-3.5 Turbo erişimi için)
- **Unsplash API anahtarı** (opsiyonel - resim entegrasyonu için)
- cURL desteği
- JSON extension
- GD Library (resim işleme için)
- İnternet bağlantısı

## 🔧 Kurulum

### 1. Plugin Dosyalarını Yükleyin

WordPress sitenizin `/wp-content/plugins/` klasörüne bu dosyaları yükleyin:

```
wp-content/plugins/ai-blog-generator/
├── ai-blog-generator.php
├── assets/
│   ├── admin.css
│   └── admin.js
└── README.md
```

### 2. Plugin'i Aktifleştirin

1. WordPress admin paneline giriş yapın
2. **Eklentiler** → **Yüklü Eklentiler** sayfasına gidin
3. "AI Blog Generator" eklentisini bulun ve **Etkinleştir**'e tıklayın

### 3. OpenAI API Anahtarını Ayarlayın

1. [OpenAI Platform](https://platform.openai.com/api-keys) hesabınıza giriş yapın
2. Yeni bir API anahtarı oluşturun
3. WordPress admin panelinde **AI Blog** → **Ayarlar** sayfasına gidin
4. API anahtarınızı girin ve kaydedin

## 🎯 Kullanım

### Blog Yazısı Oluşturma

1. WordPress admin panelinde **AI Blog** menüsüne tıklayın
2. Blog yazısının konusunu girin
3. Yazım stilini seçin:
   - **Resmi**: Profesyonel ve ciddi ton
   - **Günlük**: Samimi ve rahat dil
   - **Teknik**: Detaylı ve uzmanlık gerektiren
   - **Yaratıcı**: İlgi çekici ve eğlenceli
   - **Haber**: Objektif ve bilgilendirici

4. İstediğiniz uzunluğu belirleyin:
   - **Kısa**: 300-500 kelime
   - **Orta**: 500-800 kelime
   - **Uzun**: 800-1200 kelime

5. Hedef kitle ve anahtar kelimeleri girin (opsiyonel)
6. **Blog Yazısı Oluştur**'a tıklayın

### Blog Yazısını Yayınlama

Oluşturulan blog yazısını:
- **Yayınla**: Doğrudan WordPress'e yayınlayın
- **Düzenle**: WordPress editöründe düzenleyin
- **Yeniden Oluştur**: Yeni bir versiyon oluşturun

## ⚙️ Ayarlar

### Temel Ayarlar

- **OpenAI API Anahtarı**: ChatGPT erişimi için gerekli
- **Varsayılan Kategori**: Otomatik atanacak kategori
- **Varsayılan Yazar**: Blog yazılarının yazarı

### Güvenlik

- API anahtarı şifrelenmiş olarak saklanır
- Sadece yönetici yetkisine sahip kullanıcılar erişebilir
- CSRF koruması ve nonce doğrulaması

## 🎨 Özelleştirme

### CSS Stillerini Değiştirme

`assets/admin.css` dosyasını düzenleyerek arayüzü özelleştirebilirsiniz.

### JavaScript Fonksiyonlarını Genişletme

`assets/admin.js` dosyasına yeni özellikler ekleyebilirsiniz.

## 🔍 Sorun Giderme

### Yaygın Sorunlar

**API anahtarı çalışmıyor:**
- OpenAI hesabınızda kredi bulunduğundan emin olun
- API anahtarının doğru girildiğini kontrol edin
- İnternet bağlantınızı kontrol edin

**Blog yazısı oluşturulamıyor:**
- WordPress sitenizin cURL desteğine sahip olduğunu kontrol edin
- Sunucu zaman aşımı sürelerini artırın
- Error log'larını kontrol edin

**Yavaş çalışma:**
- Hosting sağlayıcınızın API çağrılarına izin verdiğini kontrol edin
- Sunucu kaynaklarını optimize edin

### Debug Modu

WordPress'te debug modunu aktifleştirmek için `wp-config.php` dosyasına:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 💡 En İyi Uygulamalar

### İçerik Kalitesi

1. **Spesifik konular seçin**: Genel konular yerine spesifik başlıklar
2. **Hedef kitleyi belirtin**: Daha odaklı içerik için
3. **Anahtar kelimeleri kullanın**: SEO optimizasyonu için
4. **İçeriği gözden geçirin**: AI tarafından oluşturulan içeriği daima kontrol edin

### SEO Optimizasyonu

1. Anahtar kelimeleri doğal şekilde yerleştirin
2. Meta açıklamaları düzenleyin
3. Başlık etiketlerini optimize edin
4. İç bağlantılar ekleyin

## 🔒 Güvenlik

- API anahtarları şifrelenmiş olarak saklanır
- Kullanıcı girdileri sanitize edilir
- CSRF saldırılarına karşı korunma
- Yetkilendirme kontrolleri

## 📞 Destek

Plugin ile ilgili sorunlar için:

1. WordPress error log'larını kontrol edin
2. Plugin ayarlarını gözden geçirin
3. OpenAI API durumunu kontrol edin

## 📄 Lisans

Bu plugin GPL v2 veya üzeri lisansı altında dağıtılmaktadır.

## 🔄 Güncellemeler

- v1.0.0: İlk sürüm
  - Temel AI blog oluşturma
  - OpenAI entegrasyonu
  - Admin panel arayüzü
  - Çoklu stil desteği

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Changelog

### v1.0.0 (2024)
- 🎉 Initial release
- 🤖 OpenAI GPT-3.5 Turbo integration
- 📸 Smart image integration (Unsplash + Pexels)
- 🔍 Advanced SEO optimization
- 🎨 Multiple writing styles
- 📱 Responsive admin interface
- 🛡️ Security features and API key encryption

## 🐛 Bug Reports & Feature Requests

Please use the [GitHub Issues](https://github.com/yourusername/ai-blog-generator/issues) page to report bugs or request features.

## ⭐ Star History

If this plugin helped you, please give it a star on GitHub! ⭐

---

**AI Blog Generator** ile WordPress sitenize otomatik, kaliteli ve SEO uyumlu blog yazıları eklemek artık çok kolay!
