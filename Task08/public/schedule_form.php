<?php
require 'config.php';
$db = new Database('../data/db.sqlite');
$pdo = $db->getConnection();

$employee_id = $_GET['employee_id'] ?? null;
$id = $_GET['id'] ?? null;

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

$item = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM employee_schedule WHERE employee_schedule_id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare(
            'UPDATE employee_schedule 
             SET work_date = ?, start_time = ?, end_time = ? 
             WHERE employee_schedule_id = ?'
        );
        $stmt->execute([
            $_POST['work_date'],
            $_POST['start_time'],
            $_POST['end_time'],
            $_POST['id']
        ]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO employee_schedule (employee_id, work_date, start_time, end_time) 
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $employee_id,
            $_POST['work_date'],
            $_POST['start_time'],
            $_POST['end_time']
        ]);
    }
    header('Location: schedule.php?employee_id=' . urlencode($employee_id));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $item ? 'Редактировать' : 'Добавить' ?> запись в график</title>
</head>
<body>
    <h1><?= $item ? 'Редактировать' : 'Добавить' ?> запись в график</h1>
    <h2>Мастер: <?= htmlspecialchars($employee['name']) ?></h2>
    
    <form method="POST">
        <?php if ($item): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($item['employee_schedule_id']) ?>">
        <?php endif; ?>
        
        <label>Дата работы:
            <input type="date" name="work_date" value="<?= htmlspecialchars($item['work_date'] ?? '') ?>" required>
        </label>
        
        <label>Время начала:
            <input type="time" name="start_time" value="<?= htmlspecialchars($item['start_time'] ?? '') ?>" required>
        </label>
        
        <label>Время окончания:
            <input type="time" name="end_time" value="<?= htmlspecialchars($item['end_time'] ?? '') ?>" required>
        </label>
        
        <button type="submit">Сохранить</button>
        <a href="schedule.php?employee_id=<?= htmlspecialchars($employee_id) ?>">Отмена</a>
    </form>
</body>
</html>
