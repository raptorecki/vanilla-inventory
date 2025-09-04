<?php
session_start();
require 'header.php';
require_once 'database.php';

$tags = [];
$form_error = '';

// Handle Add Tag
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tag'])) {
    $new_tag_name = trim($_POST['tag_name'] ?? '');
    if (!empty($new_tag_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO inv_tags (name) VALUES (?)");
            $stmt->execute([$new_tag_name]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Tag added successfully.'];
            header('Location: tags.php');
            exit();
        } catch (PDOException $e) {
            $form_error = "Database Error: " . $e->getMessage();
        }
    } else {
        $form_error = "Tag name cannot be empty.";
    }
}

// Handle Edit Tag
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_tag'])) {
    $tag_id = $_POST['tag_id'] ?? 0;
    $new_tag_name = trim($_POST['tag_name'] ?? '');
    if ($tag_id > 0 && !empty($new_tag_name)) {
        try {
            $stmt = $pdo->prepare("UPDATE inv_tags SET name = ? WHERE id = ?");
            $stmt->execute([$new_tag_name, $tag_id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Tag updated successfully.'];
            header('Location: tags.php');
            exit();
        } catch (PDOException $e) {
            $form_error = "Database Error: " . $e->getMessage();
        }
    } else {
        $form_error = "Invalid tag ID or name cannot be empty.";
    }
}

// Handle Delete Tag
if (isset($_GET['delete_tag'])) {
    $tag_id = $_GET['delete_tag'] ?? 0;
    if ($tag_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM inv_tags WHERE id = ?");
            $stmt->execute([$tag_id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Tag deleted successfully.'];
            header('Location: tags.php');
            exit();
        } catch (PDOException $e) {
            $form_error = "Database Error: " . $e->getMessage();
        }
    } else {
        $form_error = "Invalid tag ID.";
    }
}

// Fetch all tags
try {
    $stmt = $pdo->query("SELECT * FROM inv_tags ORDER BY name ASC");
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $form_error = "Database Error: " . $e->getMessage();
}

?>

<h1>Manage Tags</h1>

<?php if ($form_error): ?>
    <p class="error"><?= htmlspecialchars($form_error) ?></p>
<?php endif; ?>

<details>
    <summary>Add New Tag</summary>
    <form method="POST" action="tags.php" class="form-container">
        <input type="hidden" name="add_tag" value="1">
        <div class="form-group"><label for="tag_name">Tag Name *</label><input type="text" id="tag_name" name="tag_name" required></div>
        <div class="form-actions"><button type="submit">Add Tag</button></div>
    </form>
</details>

<?php if (empty($tags)): ?>
    <p>No tags found. Add one above!</p>
<?php else: ?>
    <ul style="list-style-type: none; padding-left: 0;">
        <?php foreach ($tags as $tag): ?>
            <li style="background-color: #1f1f1f; border: 1px solid #333; border-radius: 5px; margin-bottom: 10px; padding: 10px;">
                <?= htmlspecialchars($tag['name']) ?> 
                <small>(ID: <?= $tag['id'] ?>)</small>
                <a href="#" onclick="
                    let newName = prompt('Edit tag name:', '<?= addslashes(htmlspecialchars($tag['name'])) ?>');
                    if (newName !== null && newName.trim() !== '') {
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'tags.php';
                        let input1 = document.createElement('input');
                        input1.type = 'hidden';
                        input1.name = 'edit_tag';
                        input1.value = '1';
                        form.appendChild(input1);
                        let input2 = document.createElement('input');
                        input2.type = 'hidden';
                        input2.name = 'tag_id';
                        input2.value = '<?= $tag['id'] ?>';
                        form.appendChild(input2);
                        let input3 = document.createElement('input');
                        input3.type = 'hidden';
                        input3.name = 'tag_name';
                        input3.value = newName.trim();
                        form.appendChild(input3);
                        document.body.appendChild(form);
                        form.submit();
                    }
                ">Edit</a> |
                <a href="tags.php?delete_tag=<?= $tag['id'] ?>" onclick="return confirm('Are you sure you want to delete this tag?');">Delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require 'footer.php'; ?>