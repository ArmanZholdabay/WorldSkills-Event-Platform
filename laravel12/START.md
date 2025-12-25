# Инструкция по запуску проекта WorldSkills Event Platform

## Требования

-   PHP >= 8.2
-   Composer
-   Node.js и npm
-   MAMP (для MySQL)
-   База данных MySQL

## Шаги для запуска

### 1. Установка зависимостей PHP

```bash
cd "/Applications/MAMP/htdocs/WorldSkills Event Platform/laravel12"
composer install
```

### 2. Настройка файла окружения (.env)

Создайте файл `.env` на основе примера (если есть `.env.example`) или создайте новый:

```bash
# Если есть .env.example
cp .env.example .env

# Или создайте новый файл .env
```

Настройте следующие параметры в `.env`:

```env
APP_NAME="WorldSkills Event Platform"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Настройки базы данных для MAMP
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=worldskills
DB_USERNAME=root
DB_PASSWORD=root
```

### 3. Генерация ключа приложения

```bash
php artisan key:generate
```

### 4. Импорт базы данных

#### Вариант 1: Автоматический импорт через PHP (рекомендуется)

Убедитесь, что MAMP запущен (Apache и MySQL), затем выполните:

```bash
php import-db.php
```

Скрипт автоматически:

-   Создаст базу данных (если её нет)
-   Импортирует SQL дамп из `../files/database/db-dump.sql`
-   Использует настройки из `.env` файла

#### Вариант 1b: Автоматический импорт через Bash

Альтернативный способ (требует MySQL клиент в системе):

```bash
./import-db.sh
```

#### Вариант 2: Ручной импорт через phpMyAdmin

1. Откройте MAMP и запустите серверы (Apache и MySQL)
2. Откройте phpMyAdmin (обычно http://localhost:8888/phpMyAdmin/)
3. Создайте новую базу данных с именем `worldskills` (или используйте другое имя, указанное в `.env`)
4. Выберите созданную базу данных
5. Перейдите на вкладку "Импорт"
6. Выберите файл `../files/database/db-dump.sql`
7. Нажмите "Вперёд"

#### Вариант 3: Использование миграций Laravel

Если вы хотите использовать миграции вместо SQL дампа:

```bash
php artisan migrate
php artisan db:seed
```

### 7. Установка зависимостей Node.js

```bash
npm install
```

### 8. Запуск проекта

#### Вариант 1: Запуск всех сервисов одновременно (рекомендуется)

```bash
composer run dev
```

Это запустит:

-   Laravel сервер (http://localhost:8000)
-   Очередь задач
-   Vite dev server

#### Вариант 2: Запуск по отдельности

**Терминал 1 - Laravel сервер:**

```bash
php artisan serve
```

**Терминал 2 - Vite dev server:**

```bash
npm run dev
```

**Терминал 3 - Очередь задач (если используется):**

```bash
php artisan queue:work
```

### 9. Открыть в браузере

Откройте: http://localhost:8000

## Дополнительные команды

### Очистка кеша

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Сброс базы данных и повторная миграция

```bash
php artisan migrate:fresh
php artisan db:seed
```

### Создание символической ссылки для storage

```bash
php artisan storage:link
```

## Структура проекта

-   `app/` - Основной код приложения
-   `database/migrations/` - Миграции базы данных
-   `database/seeders/` - Сидеры для заполнения данных
-   `routes/` - Маршруты приложения
-   `resources/views/` - Blade шаблоны
-   `public/` - Публичные файлы

## Решение проблем

### Ошибка подключения к базе данных

-   Убедитесь, что MAMP запущен
-   Проверьте порт MySQL в MAMP (обычно 8889)
-   Проверьте настройки в `.env`

### Ошибка "Class not found"

```bash
composer dump-autoload
```

### Ошибка с правами доступа

```bash
chmod -R 775 storage bootstrap/cache
```

## Полезные ссылки

-   Laravel документация: https://laravel.com/docs
-   MAMP документация: https://documentation.mamp.info/
