#!/bin/bash

# Скрипт для запуска проекта WorldSkills Event Platform

echo "🚀 Запуск проекта WorldSkills Event Platform..."
echo ""

# Переход в директорию проекта
cd "$(dirname "$0")"

# Проверка наличия .env файла
if [ ! -f .env ]; then
    echo "⚠️  Файл .env не найден!"
    echo "📝 Создаю файл .env..."
    
    # Создание базового .env файла
    cat > .env << EOF
APP_NAME="WorldSkills Event Platform"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=8889
DB_DATABASE=worldskills
DB_USERNAME=root
DB_PASSWORD=root
EOF
    
    echo "✅ Файл .env создан"
    echo "⚠️  Не забудьте настроить параметры базы данных в .env файле!"
    echo ""
fi

# Проверка наличия APP_KEY
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo "🔑 Генерация ключа приложения..."
    php artisan key:generate
    echo ""
fi

# Проверка установки зависимостей Composer
if [ ! -d "vendor" ]; then
    echo "📦 Установка зависимостей Composer..."
    composer install
    echo ""
fi

# Проверка установки зависимостей npm
if [ ! -d "node_modules" ]; then
    echo "📦 Установка зависимостей npm..."
    npm install
    echo ""
fi

# Проверка существования базы данных SQLite (если используется)
if [ -f "database/database.sqlite" ]; then
    echo "🗄️  База данных SQLite найдена"
else
    echo "⚠️  База данных SQLite не найдена"
    echo "💡 Если используете MySQL, убедитесь что база данных создана в MAMP"
    echo ""
fi

# Очистка кеша
echo "🧹 Очистка кеша..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo ""

# Запуск миграций (с подтверждением)
echo "📊 Запуск миграций базы данных..."
read -p "Запустить миграции? (y/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate
    echo ""
fi

# Запуск проекта
echo "🎉 Запуск серверов..."
echo "📖 Откройте http://localhost:8000 в браузере"
echo "🛑 Для остановки нажмите Ctrl+C"
echo ""

# Запуск всех сервисов
composer run dev

