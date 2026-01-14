#!/bin/bash

# Run initial setup
php artisan config:clear
php artisan config:cache
php artisan migrate --force
php artisan db:seed --force

# Start scheduler in background
(while true; do
    php artisan schedule:run --verbose --no-interaction >> /var/www/html/storage/logs/scheduler.log 2>&1
    sleep 60
done) &

# Start the web server
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
