<?php
/**
 * Скрипт для импорта базы данных через PHP
 * Использование: php import-db.php
 */

echo "🗄️  Импорт базы данных WorldSkills Event Platform...\n\n";

// Путь к SQL дампу
$sqlDump = __DIR__ . '/../files/database/db-dump.sql';

// Проверка наличия SQL дампа
if (!file_exists($sqlDump)) {
    echo "❌ Ошибка: Файл $sqlDump не найден!\n";
    exit(1);
}

echo "✅ SQL дамп найден: $sqlDump\n\n";

// Настройки по умолчанию для MAMP
$config = [
    'host' => '127.0.0.1',
    'port' => 8889,
    'user' => 'root',
    'pass' => 'root',
    'database' => 'worldskills'
];

// Попытка прочитать настройки из .env файла
$envFile = __DIR__ . '/.env';
if (file_exists($envFile) && is_readable($envFile)) {
    echo "📖 Чтение настроек из .env файла...\n";
    $envContent = file_get_contents($envFile);
    
    if (preg_match('/DB_HOST=(.+)/', $envContent, $matches)) {
        $config['host'] = trim(trim($matches[1], '"\''));
    }
    
    if (preg_match('/DB_PORT=(.+)/', $envContent, $matches)) {
        $config['port'] = trim(trim($matches[1], '"\''));
    }
    
    if (preg_match('/DB_USERNAME=(.+)/', $envContent, $matches)) {
        $config['user'] = trim(trim($matches[1], '"\''));
    }
    
    if (preg_match('/DB_PASSWORD=(.+)/', $envContent, $matches)) {
        $config['pass'] = trim(trim($matches[1], '"\''));
    }
    
    if (preg_match('/DB_DATABASE=(.+)/', $envContent, $matches)) {
        $config['database'] = trim(trim($matches[1], '"\''));
    }
}

echo "📋 Настройки подключения:\n";
echo "   Host: {$config['host']}\n";
echo "   Port: {$config['port']}\n";
echo "   User: {$config['user']}\n";
echo "   Database: {$config['database']}\n\n";

// Подключение к MySQL
$dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";

try {
    echo "🔌 Подключение к MySQL...\n";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Подключение к MySQL успешно!\n\n";
} catch (PDOException $e) {
    echo "❌ Ошибка подключения к MySQL: " . $e->getMessage() . "\n";
    echo "💡 Убедитесь, что:\n";
    echo "   1. MAMP запущен (Apache и MySQL)\n";
    echo "   2. Порт MySQL: {$config['port']}\n";
    echo "   3. Пользователь: {$config['user']}\n";
    echo "   4. Пароль: {$config['pass']}\n";
    exit(1);
}

// Создание базы данных
try {
    echo "📦 Создание базы данных '{$config['database']}' (если не существует)...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ База данных '{$config['database']}' готова\n\n";
} catch (PDOException $e) {
    echo "⚠️  Предупреждение: " . $e->getMessage() . "\n\n";
}

// Выбор базы данных
try {
    $pdo->exec("USE `{$config['database']}`");
} catch (PDOException $e) {
    echo "❌ Ошибка выбора базы данных: " . $e->getMessage() . "\n";
    exit(1);
}

// Импорт SQL дампа
echo "📥 Импорт SQL дампа в базу данных '{$config['database']}'...\n";
echo "⏳ Это может занять некоторое время...\n\n";

// Попытка использовать MySQL клиент напрямую (более надежно для больших дампов)
$mysqlPath = '';
$mysqlPaths = [
    '/Applications/MAMP/Library/bin/mysql',
    '/Applications/MAMP/bin/mysql/bin/mysql',
    '/usr/local/bin/mysql'
];

foreach ($mysqlPaths as $path) {
    if (file_exists($path) && is_executable($path)) {
        $mysqlPath = $path;
        break;
    }
}

if ($mysqlPath && function_exists('exec')) {
    // Используем MySQL клиент напрямую
    echo "✅ Используется MySQL клиент: $mysqlPath\n\n";
    
    $command = sprintf(
        '%s -h %s -P %s -u %s -p%s %s < %s 2>&1',
        escapeshellarg($mysqlPath),
        escapeshellarg($config['host']),
        escapeshellarg($config['port']),
        escapeshellarg($config['user']),
        escapeshellarg($config['pass']),
        escapeshellarg($config['database']),
        escapeshellarg($sqlDump)
    );
    
    $output = [];
    $returnVar = 0;
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "✅ Импорт завершен успешно!\n\n";
    } else {
        echo "⚠️  Импорт завершен с предупреждениями\n";
        if (!empty($output)) {
            echo "   Вывод: " . implode("\n   ", array_slice($output, 0, 5)) . "\n";
        }
    }
} else {
    // Fallback: использование PDO (может быть проблематично для больших дампов)
    echo "ℹ️  Используется PDO для импорта (может быть медленнее)\n\n";
    
    $sql = file_get_contents($sqlDump);
    
    if ($sql === false) {
        echo "❌ Ошибка: Не удалось прочитать SQL дамп!\n";
        exit(1);
    }
    
    // Удаление комментариев
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Выполнение всего SQL одним запросом (если возможно)
    try {
        // Отключаем проверку внешних ключей для ускорения
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
        $pdo->exec($sql);
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
        
        echo "✅ Импорт завершен успешно!\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Ошибка при импорте через PDO: " . $e->getMessage() . "\n";
        echo "💡 Рекомендуется использовать MySQL клиент напрямую или импортировать через phpMyAdmin\n\n";
    }
}

echo "🎉 Готово! База данных '{$config['database']}' загружена\n";
echo "💡 Вы можете проверить её в phpMyAdmin: http://localhost:8888/phpMyAdmin/\n";

