# Use official lightweight PHP + Apache image
FROM php:8.2-apache

# Install PostgreSQL client & PDO drivers for PostgreSQL and SQLite
RUN apt-get update && apt-get install -y libpq-dev sqlite3 \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files into web root
COPY . /var/www/html/

# Create data directory and submissions.csv with write permissions for www-data
RUN mkdir -p /var/www/html/data \
    && touch /var/www/html/submissions.csv \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/data \
    && chmod 664 /var/www/html/submissions.csv

# Configure Apache to listen on Render's dynamic PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

ENV PORT=80

EXPOSE 80

CMD ["apache2-foreground"]
