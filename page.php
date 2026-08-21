<?php
include('includes/config.php');
include('includes/database.php');
include('includes/functions.php');

$slug = $_GET['slug'] ?? '';

$stmt = $connection->prepare("SELECT title, content FROM pages WHERE slug = ? AND status = 'published'");
$stmt->bind_param('s', $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Siden findes ikke eller er ikke publiceret endnu');
}

$page = $result->fetch_assoc();

include('includes/header.php');
?>
<div class="container mt-5">
    <?= $page['content'] ?>
</div>
<?php
include('includes/footer.php');
?>