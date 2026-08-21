<?php
include('includes/config.php');
include('includes/database.php');
include('includes/functions.php');
// secure();

$result = $connection->query("SELECT id, title, slug, status, updated_at FROM pages ORDER BY updated_at DESC");

include('includes/header.php');
?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6">Sider</h1>
        <a href="builder.php" class="btn btn-primary">+ Ny side</a>
    </div>

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Titel</th>
                <th>Status</th>
                <th>Sidst opdateret</th>
                <th>Handlinger</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($page = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($page['title']) ?></td>
                    <td>
                        <span class="badge bg-<?= $page['status'] === 'published' ? 'success' : 'secondary' ?>">
                            <?= $page['status'] === 'published' ? 'Publiceret' : 'Kladde' ?>
                        </span>
                    </td>
                    <td><?= $page['updated_at'] ?></td>
                    <td>
                        <a href="builder.php?id=<?= $page['id'] ?>" class="btn btn-sm btn-outline-primary">Rediger</a>

                        <button class="btn btn-sm btn-outline-<?= $page['status'] === 'published' ? 'warning' : 'success' ?> toggle-status" data-id="<?= $page['id'] ?>">
                            <?= $page['status'] === 'published' ? 'Afpublicer' : 'Publicer' ?>
                        </button>

                        <?php if ($page['status'] === 'published'): ?>
                            <a href="page.php?slug=<?= $page['slug'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Se</a>
                        <?php endif; ?>

                        <button class="btn btn-sm btn-outline-danger delete-page" data-id="<?= $page['id'] ?>">Slet</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
document.querySelectorAll('.delete-page').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!confirm('Er du sikker på du vil slette siden?')) return;
        fetch('delete_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: btn.dataset.id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Fejl: ' + data.message);
        });
    });
});

document.querySelectorAll('.toggle-status').forEach(btn => {
    btn.addEventListener('click', () => {
        fetch('toggle_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: btn.dataset.id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Fejl: ' + data.message);
        });
    });
});
</script>

<?php
include('includes/footer.php');
?>