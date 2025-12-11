<?php

define('DB_PATH', __DIR__ . '/db.sqlite');

try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    exit(1);
}

// список сотрудников
$employees = $db->query(
    "SELECT employees_id AS id, name FROM employees ORDER BY name"
)->fetchAll(PDO::FETCH_ASSOC);

if (empty($employees)) {
    echo "No employees found.\n";
    exit(0);
}

echo "\nAvailable Employees\n";
foreach ($employees as $emp) {
    echo sprintf("  %d: %s\n", $emp['id'], $emp['name']);
}

echo "\nEnter employee ID (or press Enter for all): ";
$input = trim(fgets(STDIN));

$employeeId = null;
if ($input !== '') {
    if (!ctype_digit($input)) {
        echo "Invalid input. Please enter a number.\n";
        exit(1);
    }
    $employeeId = (int)$input;

    $exists = $db->prepare("SELECT COUNT(*) FROM employees WHERE employees_id = ?");
    $exists->execute([$employeeId]);
    if ($exists->fetchColumn() == 0) {
        echo "Employee with ID $employeeId not found.\n";
        exit(1);
    }
}

// отчёт по выполненным работам
$query = "
    SELECT 
        e.employees_id AS id,
        e.name,
        cw.work_date,
        s.name AS service_name,
        cw.actual_price
    FROM completed_works cw
    JOIN employees e ON cw.employee_id = e.employees_id
    JOIN services s ON cw.service_id = s.services_id
    WHERE 1 = 1
";

$params = [];
if ($employeeId !== null) {
    $query .= " AND e.employees_id = ?";
    $params[] = $employeeId;
}

$query .= " ORDER BY e.name, cw.work_date";

$stmt = $db->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

displayTable($results);

function displayTable($data) {
    if (empty($data)) {
        echo "\nNo completed services found.\n";
        return;
    }

    $colWidths = [4, 50, 20, 40, 10];

    echo "\n";
    printRow(['ID', 'Nаme', 'Date', 'Service', 'Price'], $colWidths);
    printSeparator($colWidths);

    foreach ($data as $row) {
        $date = $row['work_date'];
        printRow(
            [
                $row['id'],
                substr($row['name'], 0, 50),
                $date,
                substr($row['service_name'], 0, 40),
                sprintf('%.2f', $row['actual_price'])
            ],
            $colWidths
        );
    }

    printSeparator($colWidths);
    echo "\n";
}

function printRow($cols, $widths) {
    echo "| ";
    for ($i = 0; $i < count($cols); $i++) {
        echo str_pad($cols[$i], $widths[$i] - 1) . " | ";
    }
    echo "\n";
}

function printSeparator($widths) {
    echo "|-";
    foreach ($widths as $w) {
        echo str_repeat("-", $w) . "+";
    }
    echo "\n";
}
