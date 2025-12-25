<?php
/**
 * Скрипт для обновления паролей организаторов
 * Использование: php update-passwords.php
 */

echo "🔐 Обновление паролей организаторов...\n\n";

// Настройки подключения
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

// Подключение к MySQL
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ Подключение к MySQL успешно!\n\n";
} catch (PDOException $e) {
    echo "❌ Ошибка подключения к MySQL: " . $e->getMessage() . "\n";
    exit(1);
}

// Генерация Bcrypt хешей
$password1 = 'demopass1';
$password2 = 'demopass2';

$hash1 = password_hash($password1, PASSWORD_BCRYPT);
$hash2 = password_hash($password2, PASSWORD_BCRYPT);

// Обновление паролей
try {
    $stmt = $pdo->prepare("UPDATE organizers SET password_hash = ? WHERE email = ?");
    
    // Обновление первого организатора
    $stmt->execute([$hash1, 'demo1@worldskills.org']);
    if ($stmt->rowCount() > 0) {
        echo "✅ Пароль для demo1@worldskills.org обновлен (пароль: demopass1)\n";
    } else {
        echo "⚠️  Организатор demo1@worldskills.org не найден\n";
    }
    
    // Обновление второго организатора
    $stmt->execute([$hash2, 'demo2@worldskills.org']);
    if ($stmt->rowCount() > 0) {
        echo "✅ Пароль для demo2@worldskills.org обновлен (пароль: demopass2)\n";
    } else {
        echo "⚠️  Организатор demo2@worldskills.org не найден\n";
    }
    
    echo "\n🎉 Готово! Теперь вы можете войти с:\n";
    echo "   Email: demo1@worldskills.org, Password: demopass1\n";
    echo "   Email: demo2@worldskills.org, Password: demopass2\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка при обновлении паролей: " . $e->getMessage() . "\n";
    exit(1);
}
