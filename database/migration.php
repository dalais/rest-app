<?php

require __DIR__. '/../components/functions.php';

$db = config('db')['details'];

/**
 * @return \PDO
 */
function dbConn($db) {
    try {
        $conn = new \PDO($db['driver'] . ':host=' . $db['host'] . ';dbname=' . $db['db_name'],
            $db['db_user'],
            $db['db_pass'],
            [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
        );
        $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (\PDOException $e) {
        echo "Error connection:" . $e->getCode(). ' - ' . $e->getMessage();
        die();
    }
}

$conn = dbConn($db);

/**
 * Get list sql files
 *
 * @param PDO $conn
 * @param $db
 * @return array|false
 */
function getMigrationFiles(\PDO $conn, $db) {
    $allFiles = glob(__DIR__ . '/sql/' . '*.sql');

    // Проверяем, есть ли таблица versions
    // Так как versions создается первой, то это равносильно тому, что база не пустая
    $query = sprintf("SHOW TABLES FROM %s LIKE '%s'", $db['db_name'], 'migrations');

    $migrations = $conn->exec($query);

    // Первая миграция, возвращаем все файлы из папки sql
    if ($migrations == 0) {
        return $allFiles;
    }

    // Ищем уже существующие миграции
    $versionsFiles = array();
    // Выбираем из таблицы versions все названия файлов
    $query = sprintf('select `name` from `%s`', 'migrations');
    $data = $conn->exec($query);
    // Загоняем названия в массив $versionsFiles
    // Не забываем добавлять полный путь к файлу
    foreach ($data as $row) {
        array_push($versionsFiles, __DIR__ . '/sql/' . $row['name']);
    }

    // Возвращаем файлы, которых еще нет в таблице versions
    return array_diff($allFiles, $versionsFiles);
}

/**
 * Execute migration
 *
 * @param PDO $conn
 * @param $file
 * @param $db
 */
function migrate(\PDO $conn, $file, $db) {
    // Формируем команду выполнения mysql-запроса из внешнего файла
    $command = sprintf('mysql -u%s -p%s -h %s -D %s < %s', $db['db_user'], $db['db_pass'], $db['db_host'], $db['db_name'], $file);
    // Выполняем shell-скрипт
    shell_exec($command);

    // Вытаскиваем имя файла, отбросив путь
    $baseName = basename($file);
    // Формируем запрос для добавления миграции в таблицу versions
    $query = sprintf('insert into `%s` (`name`) values("%s")', 'migrations', $baseName);
    // Выполняем запрос
    $conn->query($query);
}

// Get all not migrated files
$files = getMigrationFiles($conn, $db);

// Проверяем, есть ли новые миграции
if (empty($files)) {
    echo 'Ваша база данных в актуальном состоянии.' . PHP_EOL;
} else {
    echo 'Запуск миграций...<br><br>';

    // Накатываем миграцию для каждого файла
    foreach ($files as $file) {
        migrate($conn, $file, $db);
        // Выводим название выполненного файла
        echo basename($file) . '<br>';
    }

    echo '<br>Миграция завершена.' . PHP_EOL;
}