FROM ubuntu:22.04

ENV timezone America/Recife

# Instalar pacotes necessários e adicionar o repositório para PHP atualizado
RUN apt-get update && \
    ln -snf /usr/share/zoneinfo/${timezone} /etc/localtime && \
    echo ${timezone} > /etc/timezone && \
    apt-get install -y software-properties-common curl && \
    add-apt-repository ppa:ondrej/php && \
    # Adicionar repositório NodeSource e instalar Node.js e npm
    curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - && \
    apt-get update && \
    apt-get install -y \
    mc apache2 php8.2 php8.2-mysql php8.2-curl php8.2-gd \
    php8.2-zip php8.2-xml php8.2-mbstring nodejs && \
    rm -rf /var/lib/apt/lists/* && \
    apt-get purge -y --auto-remove software-properties-common && \
    chown www-data:www-data /var/www/html -R

RUN echo "upload_max_filesize = 8M" > /etc/php/8.2/apache2/conf.d/custom.ini && \
echo "post_max_size = 8M" >> /etc/php/8.2/apache2/conf.d/custom.ini

# Set PHP upload limits - add after PHP installation
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 8M/g' /etc/php/8.2/apache2/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 8M/g' /etc/php/8.2/apache2/php.ini && \
    service apache2 restart
    
# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite
RUN service apache2 restart

# Configura o Apache para permitir o Override
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/override.conf

RUN a2enconf override

# Copiar Composer diretamente da imagem oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Verificar se o Composer foi instalado corretamente
RUN composer --version

# Expor a porta 80
EXPOSE 80

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos para o diretório de trabalho (opcional, se você tiver arquivos)
COPY . /var/www/html

# Iniciar o Apache em primeiro plano
CMD ["apachectl", "-D", "FOREGROUND"]