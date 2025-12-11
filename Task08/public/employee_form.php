<?php
require 'config.php';
$db = new Database('../data/db.sqlite');
$pdo = $db->getConnection();

$employee = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM employees WHERE employees_id = ?');
    $stmt->execute([$_GET['id']]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare(
            'UPDATE employees 
             SET hire_date = ?, salary_percentage = ? 
             WHERE employees_id = ?'
        );
        $stmt->execute([
            $_POST['hire_date'],
            $_POST['salary_percentage'],
            $_POST['id']
        ]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO employees (name, position, salary_percentage, hire_date) 
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $_POST['name'],
            'master',                         
            $_POST['salary_percentage'],
            $_POST['hire_date']
        ]);
    }
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $employee ? 'Редактировать' : 'Добавить' ?> мастера</title>
</head>
<body>
    <h1><?= $employee ? 'Редактировать' : 'Добавить' ?> мастера</h1>
    
    <form method="POST">
        <?php if ($employee): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($employee['employees_id']) ?>">
        <?php endif; ?>
        
        <label>Имя:
            <?php if ($employee): ?>
                <input type="text" value="<?= htmlspecialchars($employee['name']) ?>" readonly>
            <?php else: ?>
                <input type="text" name="name" value="" required>
            <?php endif; ?>
        </label>
        
        <label>Дата найма:
            <input type="date" name="hire_date" value="<?= htmlspecialchars($employee['hire_date'] ?? '') ?>" required>
        </label>
        
        <label>Процент зарплаты:
            <input type="number" name="salary_percentage" min="0" max="100" step="0.1"
                   value="<?= htmlspecialchars($employee['salary_percentage'] ?? '') ?>" required>
        </label>
        
        <button type="submit">Сохранить</button>
        <a href="index.php">Отмена</a>
    </form>
</body>
</html>
