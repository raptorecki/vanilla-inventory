<?php
session_start();
require 'header.php';
require_once 'database.php';

$item = null;
$edit_form_error = '';

// Fetch item data if ID is provided
if (isset($_GET['id'])) {
    $item_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT id, name, documentation FROM inv_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();

        if (!$item) {
            $edit_form_error = "Item not found.";
        }
    } catch (PDOException $e) {
        $edit_form_error = "Database Error: " . $e->getMessage();
    }
} else {
    $edit_form_error = "No item ID provided.";
}

// Handle form submission for updating documentation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_documentation'])) {
    if ($item) { // Only proceed if item was successfully fetched
        $new_documentation = $_POST['documentation_html'] ?? ''; // Get HTML from hidden input

        try {
            $sql = "UPDATE inv_items SET documentation = ?, date_modified = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $new_documentation,
                $item_id
            ]);

            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Documentation updated successfully.'];
            header('Location: item_details.php?id=' . $item_id);
            exit();
        } catch (PDOException $e) {
            $edit_form_error = "Database Error: " . $e->getMessage();
        }
    }
}

?>

<h1>Edit Documentation for: <?= htmlspecialchars($item['name'] ?? 'N/A') ?></h1>

<?php if ($edit_form_error): ?>
    <p class="error"><?= htmlspecialchars($edit_form_error) ?></p>
<?php endif; ?>

<?php if ($item): ?>
    <form method="POST" action="item_doc_edit.php?id=<?= htmlspecialchars($item['id']) ?>" class="form-container" onsubmit="document.getElementById('documentation_html').value = quill.root.innerHTML;">
        <input type="hidden" name="update_documentation" value="1">
        
        <div class="form-group" style="grid-column: 1 / -1;">
            <label for="documentation">Documentation</label>
            <div id="editor-container" style="height: 200px;"></div><input type="hidden" name="documentation_html" id="documentation_html">
        </div>

        <div class="form-actions">
            <button type="submit">Update Documentation</button>
            <a href="item_details.php?id=<?= htmlspecialchars($item['id']) ?>" class="button">Cancel</a>
        </div>
    </form>

<!-- Quill.js Integration -->
<link href="js/quill/quill.snow.css" rel="stylesheet">
<script src="js/quill/quill.js"></script> <!-- Changed from quill.min.js to quill.js -->
<script>
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // Set initial content
    var initialContent = `<?= addslashes($item['documentation'] ?? '') ?>`;
    if (initialContent) {
        quill.root.innerHTML = initialContent;
    }
</script>

<?php endif; ?>

<?php require 'footer.php'; ?>