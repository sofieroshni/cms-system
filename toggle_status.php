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

$stmt = $connect->prepare("SELECT status FROM pages WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Siden findes ikke']);
    exit;
}

$current = $result->fetch_assoc()['status'];
$new = $current === 'published' ? 'draft' : 'published';

$update = $connect->prepare("UPDATE pages SET status = ? WHERE id = ?");
$update->bind_param('si', $new, $id);

if ($update->execute()) {
    echo json_encode(['success' => true, 'status' => $new]);
} else {
    echo json_encode(['success' => false, 'message' => $connect->error]);
}