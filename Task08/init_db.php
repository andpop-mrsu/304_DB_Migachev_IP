<?php

if (!is_dir('./data')) {
    mkdir('./data', 0777, true);
}

try {
    $pdo = new PDO('sqlite:./data/db.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Инициализация схемы и данных из твоего db_init.sql
    $sql = file_get_contents('./db_init.sql');
    $pdo->exec($sql);

    // Таблица графика работы сотрудников под твою БД
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_schedule (
            employee_schedule_id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            work_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            FOREIGN KEY (employee_id) 
                REFERENCES employees(employees_id) 
                ON DELETE CASCADE
        )
    ");

    echo "База данных успешно инициализирована!\\n";
    echo "Создан файл: " . realpath('./data/db.sqlite') . "\\n";

} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage() . "\\n");
}
?>