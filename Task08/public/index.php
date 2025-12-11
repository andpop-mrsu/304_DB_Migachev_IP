<?php
require 'config.php';
$db = new Database('../data/db.sqlite');
$pdo = $db->getConnection();

$employees = $pdo->query(
    "SELECT *, 
            substr(name, instr(name, ' ') + 1) AS last_name 
     FROM employees
     WHERE position = 'master'
     ORDER BY last_name, name"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Автомойка - Мастера</title>
</head>
<body>
    <h1>Список мастеров</h1>
    
    <table>
        <tr>
            <th>Мастер</th>
            <th>Дата найма</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($employees as $emp): ?>
        <tr>
            <td><?= htmlspecialchars($emp['name']) ?></td>
            <td><?= htmlspecialchars($emp['hire_date']) ?></td>
            <td class="actions">
                <a href="employee_form.php?id=<?= $emp['employees_id'] ?>">Редактировать</a>
                <a href="employee_delete.php?id=<?= $emp['employees_id'] ?>" class="delete" onclick="return confirm('Удалить мастера?')">Удалить</a>
                <a href="schedule.php?employee_id=<?= $emp['employees_id'] ?>" class="schedule">График</a>
                <a href="appointments.php?employee_id=<?= $emp['employees_id'] ?>" class="works">Выполненные работы</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <a href="employee_form.php" class="add">+ Добавить мастера</a>
</body>
</html>
