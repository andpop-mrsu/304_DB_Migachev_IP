<?php
require 'config.php';
$db = new Database('../data/db.sqlite');
$pdo = $db->getConnection();

$id = $_GET['id'] ?? null;
$employee_id = $_GET['employee_id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare('DELETE FROM completed_works WHERE completed_works_id = ?');
    $stmt->execute([$id]);
}

header('Location: appointments.php?employee_id=' . urlencode($employee_id));
exit;
