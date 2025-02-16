# 1. PHP'nin Apache ile birlikte gelen resmi sürümünü kullanıyoruz
FROM php:8.2-apache

# 2. Gerekli PHP uzantılarını yüklüyoruz
RUN docker-php-ext-install pdo pdo_mysql

# 3. Apache için mod_rewrite aktif ediliyor (URL yönlendirmeleri için)
RUN a2enmod rewrite

# 4. Çalışma dizinini belirliyoruz
WORKDIR /var/www/html

# 5. Uygulama dosyalarını kopyalıyoruz
COPY src/ /var/www/html/

# 6. Apache'nin ana sürecini başlatıyoruz
CMD ["apache2-foreground"]

