<?php
require 'config.php';
$db = new Database('../data/db.sqlite');
$pdo = $db->getConnection();

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare('DELETE FROM employees WHERE employees_id = ?');
    $stmt->execute([$id]);
}

header('Location: index.php');
