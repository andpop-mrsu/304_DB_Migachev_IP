<?php

define('DB_PATH', __DIR__ . '/db.sqlite');

try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlScript = file_get_contents(__DIR__ . '/db_init.sql');
    $db->exec($sqlScript);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$employeesStmt = $db->query("
    SELECT employees_id AS id, name
    FROM employees
    ORDER BY name
");
$employees = $employeesStmt->fetchAll(PDO::FETCH_ASSOC);

$selectedEmployeeId = $_GET['employee_id'] ?? null;

$query = "
    SELECT 
        e.employees_id AS employee_id,
        e.name AS employee_name,
        cw.work_date,
        s.name AS service_name,
        cw.actual_price
    FROM completed_works cw
    JOIN employees e ON cw.employee_id = e.employees_id
    JOIN services s ON cw.service_id = s.services_id
    WHERE 1 = 1
";

$params = [];
if ($selectedEmployeeId !== null && $selectedEmployeeId !== '') {
    $query .= " AND e.employees_id = ?";
    $params[] = (int)$selectedEmployeeId;
}

$query .= " ORDER BY e.name, cw.work_date";

$stmt = $db->prepare($query);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчёт по услугам</title>
</head>
<body>
    <h1>Отчёт по выполненным услугам</h1>

    <div class="filter">
        <form method="get">
            <label for="employee_id">Фильтр по сотруднику:</label>
            <select name="employee_id" id="employee_id" onchange="this.form.submit()">
                <option value="">Все сотрудники</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?php if ($selectedEmployeeId == $emp['id']): ?>selected<?php endif; ?>>
                        <?= htmlspecialchars($emp['id'] . ' ' . $emp['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (empty($services)): ?>
        <p>Нет выполненных работ.</p>
    <?php else: ?>
        <table border="1" cellpadding="4" cellspacing="0">
            <thead>
                <tr>
                    <th>ID сотрудника</th>
                    <thИмя сотрудника</th>
                    <th>Дата</th>
                    <th>Услуга</th>
                    <th>Фактическая цена</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?= htmlspecialchars($service['employee_id']) ?></td>
                        <td><?= htmlspecialchars($service['employee_name']) ?></td>
                        <td><?= htmlspecialchars($service['work_date']) ?></td>
                        <td><?= htmlspecialchars($service['service_name']) ?></td>
                        <td><?= sprintf('%.2f', $service['actual_price']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
