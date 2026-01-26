FROM php:8.2

RUN sed -i 's@deb.debian.org@mirrors.cloud.tencent.com@g' /etc/apt/sources.list.d/debian.sources

# 安装开发工具和依赖
RUN apt-get update && \
    apt-get install -y \
            build-essential \
            cmake clang llvm-dev libclang-dev \
            git curl \
            wget zip unzip lsof nload htop net-tools dnsutils openssh-server vim \
            # PHP 相关依赖
            libzip-dev \
            libpng-dev \
            libjpeg-dev \
            libfreetype6-dev \
            libonig-dev \
            libxml2-dev \
            libcurl4-openssl-dev \
            libssl-dev \
            libsqlite3-dev \
            libpq-dev \
            libmemcached-dev \
            librdkafka-dev \
            zlib1g-dev \
            libicu-dev \
            g++ && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# 安装 PHP 扩展
RUN docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        zip \
        mbstring \
        xml \
        curl \
        opcache

# 安装 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 安装 code-server 和 PHP 开发扩展
RUN curl -fsSL https://code-server.dev/install.sh | sh \
    && code-server --install-extension bmewburn.vscode-intelephense-client \
    && code-server --install-extension xdebug.php-debug \
    && code-server --install-extension formulahendry.code-runner \
    && code-server --install-extension ms-vscode.vscode-typescript-next \
    && code-server --install-extension esbenp.prettier-vscode \
    && code-server --install-extension tencent-cloud.coding-copilot \
    && code-server --install-extension zobo.php-intellisense

# 指定字符集支持命令行输入中文
ENV LANG C.UTF-8
ENV LANGUAGE C.UTF-8
