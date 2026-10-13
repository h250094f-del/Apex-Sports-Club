# Use official lightweight PHP + Apache image
FROM php:8.2-apache

# Configure Apache to listen on port 80 (standard) and allow .htaccess overrides
RUN a2enmod rewrite

# Copy project files into web root
COPY . /var/www/html/

# Set proper ownership and permissions for Apache
# submissions.csv needs write permissions for the www-data web server user
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && touch /var/www/html/submissions.csv \
    && chmod 664 /var/www/html/submissions.csv

# Render dynamically sets $PORT environment variable (defaults to 80 or 10000 on Render)
# Configure Apache to listen on Render's dynamic PORT if provided, else default to 80
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

ENV PORT=80

EXPOSE 80

CMD ["apache2-foreground"]
