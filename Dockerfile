FROM php:8.4-fpm AS builder

ARG DEBIAN_FRONTEND=noninteractive

# 1) Paquetes del sistema + headers necesarios
RUN apt-get update && apt-get install -y --no-install-recommends \
    libicu-dev g++ \
    libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libonig-dev libxml2-dev \
    git unzip curl gnupg2 \
    default-mysql-client \
    poppler-utils \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    # 2) Extensiones PHP (incluye intl)
    && docker-php-ext-install -j"$(nproc)" intl pdo pdo_mysql mbstring zip gd xml soap exif \
    # 3) PECL
    && pecl install redis && docker-php-ext-enable redis \
    # Limpieza
    && rm -rf /var/lib/apt/lists/*

# (Opcional) Node LTS para build de assets — usa 20.x
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Workdir
WORKDIR /var/www/filament-v3

# ====================================================================================
# [BEST PRACTICE] USER MAPPING
# Creates a user inside the container that matches the host user (passed via build args)
# Defaults to www-data (production behavior) if no args provided
# ====================================================================================
ARG user=www-data
ARG uid=33
ARG gid=33

RUN if [ "$user" != "www-data" ]; then \
    groupadd --force -g $gid $user && \
    useradd -ms /bin/bash -G www-data,root -u $uid -g $gid $user && \
    mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user; \
    fi

# Ajustar permisos del directorio de trabajo
RUN chown -R $user:$user /var/www/filament-v3

# Cambiar al usuario no-root
USER $user

EXPOSE 9000
CMD ["php-fpm"]
