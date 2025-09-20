docker compose exec app chmod -R 775 storage bootstrap/cache

docker compose exec app bash
 docker compose exec app cp .env.example .env
 chown -R www-data:www-data /var/www/storage
 chown -R www-data:www-data /var/www/bootstrap/cache
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan cache:clear

.env
docker compose exec --user root app cp .env.example .env
docker compose exec --user root app chmod -R 777 /var/www/storage /var/www/bootstrap/cache
docker compose exec --user root app chmod 666 /var/www/.env
    

