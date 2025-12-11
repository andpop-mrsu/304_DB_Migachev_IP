<?php
require 'config.php';
$db = new Database('../data/db.sqlite');
$pdo = $db->getConnection();

$employee_id = $_GET['employee_id'] ?? null;
if (!$employee_id) {
    header('Location: index.php');
    exit;
}

// сотрудник
$stmt = $pdo->prepare('SELECT * FROM employees WHERE employees_id = ?');
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    header('Location: index.php');
    exit;
}

// график работы
$stmt = $pdo->prepare(
    'SELECT * FROM employee_schedule
     WHERE employee_id = ?
     ORDER BY work_date DESC, start_time'
);
$stmt->execute([$employee_id]);
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>График работы - <?= htmlspecialchars($employee['name']) ?></title>
</head>
<body>
    <h1>График работы: <?= htmlspecialchars($employee['name']) ?></h1>
    
    <table>
        <tr>
            <th>Дата</th>
            <th>Время начала</th>
            <th>Время окончания</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($schedule as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['work_date']) ?></td>
            <td><?= htmlspecialchars(substr($item['start_time'], 0, 5)) ?></td>
            <td><?= htmlspecialchars(substr($item['end_time'], 0, 5)) ?></td>
            <td class="actions">
                <a href="schedule_form.php?id=<?= $item['employee_schedule_id'] ?>&employee_id=<?= $employee_id ?>">Редактировать</a>
                <a href="schedule_delete.php?id=<?= $item['employee_schedule_id'] ?>&employee_id=<?= $employee_id ?>" class="delete" onclick="return confirm('Удалить запись?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <a href="schedule_form.php?employee_id=<?= $employee_id ?>" class="add">Добавить запись в график</a>
    <br><br>
    <a href="index.php">Вернуться к списку мастеров</a>
</body>
</html>
