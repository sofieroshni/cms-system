<?php
include('includes/config.php');
include('includes/database.php');
include('includes/functions.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Ugyldigt id']);
    exit;
}

$stmt = $connect->prepare("DELETE FROM pages WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $connection->error]);
}