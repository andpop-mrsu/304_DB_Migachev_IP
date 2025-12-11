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

// список услуг (как источник данных для выбора услуги и цены)
$services = $pdo->query(
    'SELECT 
         s.services_id      AS id,
         s.name             AS service_name,
         cc.name            AS car_type,
         s.price
     FROM services s
     JOIN car_categories cc ON s.car_category_id = cc.car_categories_id
     ORDER BY s.name, cc.name'
)->fetchAll(PDO::FETCH_ASSOC);

// редактируемая выполненная работа (completed_works)
$apt = null;
if ($id) {
    $stmt = $pdo->prepare(
        'SELECT cw.*, b.client_name 
         FROM completed_works cw
         LEFT JOIN bookings b ON cw.booking_id = b.bookings_id
         WHERE cw.completed_works_id = ?'
    );
    $stmt->execute([$id]);
    $apt = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['appointment_start'];               // формат: YYYY-MM-DDTHH:MM
    $start_dt = str_replace('T', ' ', $start);          // для SQLite: YYYY-MM-DD HH:MM
    $duration = (int)$_POST['appointment_duration_minutes'];

    $end_dt = date('Y-m-d H:i:s', strtotime($start_dt . " + $duration minutes")); 

    $service_id = (int)$_POST['service_id'];
    $price = (float)$_POST['appointment_price'];
    $client_name = $_POST['customer_name'] ?? null;

    if (!empty($_POST['id'])) {

        $stmt = $pdo->prepare(
            'UPDATE completed_works
             SET work_date = ?, 
                 work_time = ?, 
                 actual_duration_minutes = ?, 
                 actual_price = ?
             WHERE completed_works_id = ?'
        );
        $stmt->execute([
            substr($start_dt, 0, 10),                // work_date
            substr($start_dt, 11, 8),               // work_time (HH:MM:SS)
            $duration,
            $price,
            $_POST['id']
        ]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO completed_works (
                 booking_id,
                 employee_id,
                 box_id,
                 service_id,
                 work_date,
                 work_time,
                 actual_duration_minutes,
                 actual_price,
                 notes
             ) VALUES (
                 NULL, ?, 1, ?, ?, ?, ?, ?, NULL
             )'
        );
        $stmt->execute([
            $employee_id,
            $service_id,
            substr($start_dt, 0, 10),             // дата
            substr($start_dt, 11, 8),            // время
            $duration,
            $price
        ]);

    }

    header('Location: appointments.php?employee_id=' . urlencode($employee_id));
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $apt ? 'Редактировать' : 'Добавить' ?> работу</title>
</head>
<body>
    <h1><?= $apt ? 'Редактировать' : 'Добавить' ?> работу</h1>

    <form method="POST">
        <?php if ($apt): ?>
            <input type="hidden" name="id" value="<?= htmlspecialchars($apt['completed_works_id']) ?>">
        <?php endif; ?>

        <label>Мастер:
            <input type="text" value="<?= htmlspecialchars($employee['name'] ?? '') ?>" readonly>
        </label>

        <label>Клиент:
            <?php if ($apt): ?>
                <input type="text" value="<?= htmlspecialchars($apt['client_name'] ?? '') ?>" readonly>
            <?php else: ?>
                <input type="text" name="customer_name" value="">
            <?php endif; ?>
        </label>

        <label>Дата и время начала:
            <input type="datetime-local" name="appointment_start"
                   value="<?= $apt ? htmlspecialchars($apt['work_date'] . 'T' . substr($apt['work_time'], 0, 5)) : '' ?>" required>
        </label>

        <label>Услуга:
            <select name="service_id" required>
                <?php foreach ($services as $srv): ?>
                    <option value="<?= $srv['id'] ?>"
                        <?= ($apt && $apt['service_id'] == $srv['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($srv['service_name']) ?> -
                        <?= htmlspecialchars($srv['car_type']) ?>
                        (<?= $srv['price'] ?> руб.)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Длительность (мин):
            <input type="number" name="appointment_duration_minutes"
                   value="<?= htmlspecialchars($apt['actual_duration_minutes'] ?? 30) ?>" min="1" required>
        </label>

        <label>Цена:
            <input type="number" name="appointment_price" step="0.01"
                   value="<?= htmlspecialchars($apt['actual_price'] ?? '') ?>" required>
        </label>

        <button type="submit">Сохранить</button>
        <a href="appointments.php?employee_id=<?= htmlspecialchars($employee_id) ?>">Отмена</a>
    </form>
</body>
</html>
