<?php
require 'config.php';
$db = new Database('../data/db.sqlite');
$pdo = $db->getConnection();

$employee_id = $_GET['employee_id'] ?? null;
if (!$employee_id) {
    header('Location: index.php');
    exit;
}

// мастер
$stmt = $pdo->prepare('SELECT * FROM employees WHERE employees_id = ?');
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    header('Location: index.php');
    exit;
}

// выполненные работы этого мастера
$stmt = $pdo->prepare(
    'SELECT 
         cw.*,
         s.name AS service_name,
         cc.name AS car_type,
         b.client_name
     FROM completed_works cw
     JOIN services s       ON cw.service_id = s.services_id
     JOIN car_categories cc ON s.car_category_id = cc.car_categories_id
     LEFT JOIN bookings b   ON cw.booking_id = b.bookings_id
     WHERE cw.employee_id = ?
     ORDER BY cw.work_date DESC, cw.work_time DESC'
);
$stmt->execute([$employee_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Выполненные работы - <?= htmlspecialchars($employee['name']) ?></title>
</head>
<body>
    <h1>Выполненные работы: <?= htmlspecialchars($employee['name']) ?></h1>
    
    <table>
        <tr>
            <th>Дата</th>
            <th>Услуга</th>
            <th>Тип авто</th>
            <th>Клиент</th>
            <th>Цена</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($appointments as $apt): ?>
        <tr>
            <td>
                <?= htmlspecialchars(
                    date(
                        'd.m.Y H:i',
                        strtotime($apt['work_date'] . ' ' . $apt['work_time'])
                    )
                ) ?>
            </td>
            <td><?= htmlspecialchars($apt['service_name']) ?></td>
            <td><?= htmlspecialchars($apt['car_type']) ?></td>
            <td><?= htmlspecialchars($apt['client_name'] ?? '—') ?></td>
            <td><?= number_format($apt['actual_price'], 2) ?> руб.</td>
            <td class="actions">
                <a href="appointment_form.php?id=<?= $apt['completed_works_id'] ?>&employee_id=<?= $employee_id ?>">Редактировать</a>
                <a href="appointment_delete.php?id=<?= $apt['completed_works_id'] ?>&employee_id=<?= $employee_id ?>" class="delete" onclick="return confirm('Удалить работу?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <a href="appointment_form.php?employee_id=<?= $employee_id ?>" class="add">+ Добавить работу</a>
    <br><br>
    <a href="index.php">← Вернуться к списку мастеров</a>
</body>
</html>
