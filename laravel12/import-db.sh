#!/bin/bash

# Скрипт для импорта базы данных в phpMyAdmin (MAMP)

echo "🗄️  Импорт базы данных WorldSkills Event Platform..."
echo ""

# Переход в директорию проекта
cd "$(dirname "$0")"

# Путь к SQL дампу
SQL_DUMP="../files/database/db-dump.sql"

# Проверка наличия SQL дампа
if [ ! -f "$SQL_DUMP" ]; then
    echo "❌ Ошибка: Файл $SQL_DUMP не найден!"
    exit 1
fi

echo "✅ SQL дамп найден: $SQL_DUMP"
echo ""

# Настройки базы данных (по умолчанию для MAMP)
DB_HOST="127.0.0.1"
DB_PORT="8889"
DB_USER="root"
DB_PASS="root"
DB_NAME="worldskills"

# Попытка прочитать настройки из .env файла
if [ -f .env ] && [ -r .env ]; then
    echo "📖 Чтение настроек из .env файла..."
    
    # Извлечение настроек из .env (с обработкой ошибок)
    if grep -q "DB_HOST=" .env 2>/dev/null; then
        DB_HOST=$(grep "DB_HOST=" .env 2>/dev/null | head -1 | cut -d '=' -f2 | tr -d ' ' | tr -d '"' | tr -d "'")
    fi
    
    if grep -q "DB_PORT=" .env 2>/dev/null; then
        DB_PORT=$(grep "DB_PORT=" .env 2>/dev/null | head -1 | cut -d '=' -f2 | tr -d ' ' | tr -d '"' | tr -d "'")
    fi
    
    if grep -q "DB_USERNAME=" .env 2>/dev/null; then
        DB_USER=$(grep "DB_USERNAME=" .env 2>/dev/null | head -1 | cut -d '=' -f2 | tr -d ' ' | tr -d '"' | tr -d "'")
    fi
    
    if grep -q "DB_PASSWORD=" .env 2>/dev/null; then
        DB_PASS=$(grep "DB_PASSWORD=" .env 2>/dev/null | head -1 | cut -d '=' -f2 | tr -d ' ' | tr -d '"' | tr -d "'")
    fi
    
    if grep -q "DB_DATABASE=" .env 2>/dev/null; then
        DB_NAME=$(grep "DB_DATABASE=" .env 2>/dev/null | head -1 | cut -d '=' -f2 | tr -d ' ' | tr -d '"' | tr -d "'")
    fi
else
    echo "ℹ️  Файл .env не найден или недоступен, используются настройки по умолчанию"
fi

echo "📋 Настройки подключения:"
echo "   Host: $DB_HOST"
echo "   Port: $DB_PORT"
echo "   User: $DB_USER"
echo "   Database: $DB_NAME"
echo ""

# Поиск пути к mysql (MAMP)
MYSQL_PATH=""
if [ -f "/Applications/MAMP/Library/bin/mysql" ]; then
    MYSQL_PATH="/Applications/MAMP/Library/bin/mysql"
    echo "✅ Найден MySQL в MAMP Library"
elif [ -f "/Applications/MAMP/bin/mysql/bin/mysql" ]; then
    MYSQL_PATH="/Applications/MAMP/bin/mysql/bin/mysql"
    echo "✅ Найден MySQL в MAMP bin"
elif [ -f "/usr/local/bin/mysql" ]; then
    MYSQL_PATH="/usr/local/bin/mysql"
    echo "✅ Найден MySQL в /usr/local/bin"
elif command -v mysql &> /dev/null 2>&1; then
    MYSQL_PATH=$(command -v mysql)
    echo "✅ Найден MySQL через PATH"
else
    echo "❌ Ошибка: MySQL клиент не найден!"
    echo ""
    echo "💡 Попробуйте один из вариантов:"
    echo "   1. Убедитесь, что MAMP установлен"
    echo "   2. Используйте phpMyAdmin для ручного импорта (см. START.md)"
    echo "   3. Установите MySQL через Homebrew: brew install mysql"
    echo ""
    exit 1
fi

echo "✅ MySQL клиент найден: $MYSQL_PATH"
echo ""

# Проверка подключения к MySQL
echo "🔌 Проверка подключения к MySQL..."
if ! $MYSQL_PATH -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1;" &> /dev/null; then
    echo "❌ Ошибка: Не удалось подключиться к MySQL!"
    echo "💡 Убедитесь, что:"
    echo "   1. MAMP запущен (Apache и MySQL)"
    echo "   2. Порт MySQL: $DB_PORT"
    echo "   3. Пользователь: $DB_USER"
    echo "   4. Пароль: $DB_PASS"
    exit 1
fi

echo "✅ Подключение к MySQL успешно!"
echo ""

# Создание базы данных (если не существует)
echo "📦 Создание базы данных '$DB_NAME' (если не существует)..."
$MYSQL_PATH -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1

if [ $? -eq 0 ]; then
    echo "✅ База данных '$DB_NAME' готова"
else
    echo "⚠️  Предупреждение: Возможны проблемы с созданием базы данных"
fi
echo ""

# Импорт SQL дампа
echo "📥 Импорт SQL дампа в базу данных '$DB_NAME'..."
echo "⏳ Это может занять некоторое время..."
echo ""

$MYSQL_PATH -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_DUMP" 2>&1

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ База данных успешно импортирована!"
    echo ""
    echo "🎉 Готово! База данных '$DB_NAME' загружена в phpMyAdmin"
    echo "💡 Вы можете проверить её в phpMyAdmin: http://localhost:8888/phpMyAdmin/"
else
    echo ""
    echo "❌ Ошибка при импорте базы данных!"
    echo "💡 Проверьте логи выше для деталей"
    exit 1
fi

