-- Скрипт для сброса и пересоздания базы данных
-- ВНИМАНИЕ: Этот скрипт удалит все данные!

-- Сброс всех миграций
-- php artisan migrate:reset

-- Удаление всех таблиц (если нужно)
-- DROP DATABASE IF EXISTS eflight;
-- CREATE DATABASE eflight;

-- Запуск всех миграций заново
-- php artisan migrate

-- Заполнение тестовыми данными (опционально)
-- php artisan db:seed

-- Проверка статуса миграций
-- php artisan migrate:status

-- Команды для выполнения в терминале:
-- 1. php artisan migrate:reset
-- 2. php artisan migrate
-- 3. php artisan migrate:status 