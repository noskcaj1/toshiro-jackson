FROM php:8.1-apache

# Copia o código da aplicação para o diretório do servidor web
COPY . /var/www/html/

# Instala a extensão mysqli necessária e habilita o mod_rewrite do Apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN a2enmod rewrite
