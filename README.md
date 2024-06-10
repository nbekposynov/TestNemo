Установка:
1. Git Clone
2. Изменить порты (если необходимо)
3. Перименовать .env.example
4. docker-composer up -d
5. docker-compose exec app bash
6. composer update
7. chown -R www-data:www-data storage
   chown -R www-data:www-data bootstrap/cache
   php artisan config:clear
   php artisan cache:clear
9. Пример Работы Поиска
  http://localhost:8000/api/airports/search?search=Ла-Брак 
