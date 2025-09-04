<?php
session_start();
require 'header.php';
require_once 'database.php';

$item = null;
$documentation_content = 'No documentation available for this item.';

if (isset($_GET['id'])) {
    $item_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM inv_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();

        if ($item) {
            $documentation_content = !empty($item['documentation']) ? $item['documentation'] : 'No documentation available for this item.';
        }
    } catch (PDOException $e) {
        $documentation_content = "Database Error: " . $e->getMessage();
    }
} else {
    $documentation_content = "No item ID provided.";
}

?>

<h1>Documentation for: <?= htmlspecialchars($item['name'] ?? 'Item') ?></h1>

<div class="documentation-container">
    <?= $documentation_content // Assuming documentation is HTML or plain text ?>
</div>

<div class="form-actions" style="margin-top: 20px;">
    <a href="inventory.php" class="button">Back to Inventory</a>
    <?php if ($item): // Only show edit button if item exists ?>
        <a href="item_doc_edit.php?id=<?= htmlspecialchars($item['id']) ?>" class="button" style="background-color: #3a884c;">Edit Documentation</a>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>