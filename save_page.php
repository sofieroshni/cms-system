<?php
include('includes/config.php');
include('includes/database.php');
include('includes/functions.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$title = trim($data['title'] ?? '');
$html = $data['html'] ?? '';

if (!$title || !$html) {
    echo json_encode(['success' => false, 'message' => 'Titel eller indhold mangler']);
    exit;
}

$slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));

if ($id) {
    // OPDATER eksisterende side
    $stmt = $connect->prepare("UPDATE pages SET title = ?, content = ? WHERE id = ?");
    $stmt->bind_param('ssi', $title, $html, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        echo json_encode(['success' => false, 'message' => $connection->error]);
    }
} else {
    // Sikr unikt slug
    $check = $connection->prepare("SELECT id FROM pages WHERE slug = ?");
    $check->bind_param('s', $slug);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $slug .= '-' . time();
    }

    $stmt = $connection->prepare("INSERT INTO pages (title, slug, content, status) VALUES (?, ?, ?, 'draft')");
    $stmt->bind_param('sss', $title, $slug, $html);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $connection->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $connection->error]);
    }
}