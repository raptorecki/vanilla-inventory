<?php
session_start();
require 'header.php';
require_once 'database.php';

$categories = [];
$subcategories = [];
$form_error = '';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $new_category_name = trim($_POST['category_name'] ?? '');
    if (!empty($new_category_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO inv_categories (name) VALUES (?)");
            $stmt->execute([$new_category_name]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Category added successfully.'];
            header('Location: categories.php');
            exit();
        } catch (PDOException $e) {
            $form_error = "Database Error: " . $e->getMessage();
        }
    } else {
        $form_error = "Category name cannot be empty.";
    }
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $category_id = $_POST['category_id'] ?? 0;
    $new_category_name = trim($_POST['category_name'] ?? '');
    if ($category_id > 0 && !empty($new_category_name)) {
        try {
            $stmt = $pdo->prepare("UPDATE inv_categories SET name = ? WHERE id = ?");
            $stmt->execute([$new_category_name, $category_id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Category updated successfully.'];
            header('Location: categories.php');
            exit();
        } catch (PDOException $e) {
            $form_error = "Database Error: " . $e->getMessage();
        }
    } else {
        $form_error = "Invalid category ID or name cannot be empty.";
    }
}

// Handle Delete Category
if (isset($_GET['delete_category'])) {
    $category_id = $_GET['delete_category'] ?? 0;
    if ($category_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM inv_categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Category deleted successfully.'];
            header('Location: categories.php');
            exit();
        } catch (PDOException $e) {
            $form_error = "Database Error: " . $e->getMessage();
        }
    } else {
        $form_error = "Invalid category ID.";
    }
}

// Handle Add Subcategory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subcategory'])) {
    $category_id = $_POST['parent_category_id'] ?? 0;
    $new_subcategory_name = trim($_POST['subcategory_name'] ?? '');
    if ($category_id > 0 && !empty($new_subcategory_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO inv_subcategories (category_id, name) VALUES (?, ?)");
            $stmt->execute([$category_id, $new_subcategory_name]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Subcategory added successfully.'];
            header('Location: categories.php');
            exit();
        } catch (PDOException $e) {
            $form_error = "Database Error: " . $e->getMessage();
        }
    } else {
        $form_error = "Invalid parent category or subcategory name cannot be empty.";
    }
}

// Handle Edit Subcategory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_subcategory'])) {
    $subcategory_id = $_POST['subcategory_id'] ?? 0;
    $new_subcategory_name = trim($_POST['subcategory_name'] ?? '');
    if ($subcategory_id > 0 && !empty($new_subcategory_name)) {
        try {
            $stmt = $pdo->prepare("UPDATE inv_subcategories SET name = ? WHERE id = ?");
            $stmt->execute([$new_subcategory_name, $subcategory_id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Subcategory updated successfully.'];
            header('Location: categories.php');
            exit();
        } catch (PDOException $e) {
            $form_error = "Database Error: " . $e->getMessage();
        }
    } else {
        $form_error = "Invalid subcategory ID or name cannot be empty.";
    }
}

// Handle Delete Subcategory
if (isset($_GET['delete_subcategory'])) {
    $subcategory_id = $_GET['delete_subcategory'] ?? 0;
    if ($subcategory_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM inv_subcategories WHERE id = ?");
            $stmt->execute([$subcategory_id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Subcategory deleted successfully.'];
            header('Location: categories.php');
            exit();
        } catch (PDOException $e) {
            $form_error = "Database Error: " . $e->getMessage();
        }
    }
}

// Fetch all categories and their subcategories
try {
    $stmt = $pdo->query("SELECT * FROM inv_categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as &$category) {
        $stmt = $pdo->prepare("SELECT * FROM inv_subcategories WHERE category_id = ? ORDER BY name ASC");
        $stmt->execute([$category['id']]);
        $category['subcategories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $form_error = "Database Error: " . $e->getMessage();
}

?>

<h1>Manage Categories & Subcategories</h1>

<?php if ($form_error): ?>
    <p class="error"><?= htmlspecialchars($form_error) ?></p>
<?php endif; ?>

<details>
    <summary>Add New Category</summary>
    <form method="POST" action="categories.php" class="form-container">
        <input type="hidden" name="add_category" value="1">
        <div class="form-group"><label for="category_name">Category Name *</label><input type="text" id="category_name" name="category_name" required></div>
        <div class="form-actions"><button type="submit">Add Category</button></div>
    </form>
</details>

<?php if (empty($categories)): ?>
    <p>No categories found. Add one above!</p>
<?php else: ?>
    <?php foreach ($categories as $category): ?>
        <div class="category-section" style="background-color: #1f1f1f; border: 1px solid #333; border-radius: 5px; margin-bottom: 20px; padding: 15px;">
            <h2 style="margin-top: 0; color: #a9ffb8;"><?= htmlspecialchars($category['name']) ?> 
                <small>(ID: <?= $category['id'] ?>)</small>
                <a href="#" onclick="
                    let newName = prompt('Edit category name:', '<?= addslashes(htmlspecialchars($category['name'])) ?>');
                    if (newName !== null && newName.trim() !== '') {
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'categories.php';
                        let input1 = document.createElement('input');
                        input1.type = 'hidden';
                        input1.name = 'edit_category';
                        input1.value = '1';
                        form.appendChild(input1);
                        let input2 = document.createElement('input');
                        input2.type = 'hidden';
                        input2.name = 'category_id';
                        input2.value = '<?= $category['id'] ?>';
                        form.appendChild(input2);
                        let input3 = document.createElement('input');
                        input3.type = 'hidden';
                        input3.name = 'category_name';
                        input3.value = newName.trim();
                        form.appendChild(input3);
                        document.body.appendChild(form);
                        form.submit();
                    }
                ">Edit</a> |
                <a href="categories.php?delete_category=<?= $category['id'] ?>" onclick="return confirm('Are you sure you want to delete this category and all its subcategories?');">Delete</a>
            </h2>

            <details style="margin-left: 20px;">
                <summary>Add New Subcategory for <?= htmlspecialchars($category['name']) ?></summary>
                <form method="POST" action="categories.php" class="form-container">
                    <input type="hidden" name="add_subcategory" value="1">
                    <input type="hidden" name="parent_category_id" value="<?= $category['id'] ?>">
                    <div class="form-group"><label for="subcategory_name_<?= $category['id'] ?>">Subcategory Name *</label><input type="text" id="subcategory_name_<?= $category['id'] ?>" name="subcategory_name" required></div>
                    <div class="form-actions"><button type="submit">Add Subcategory</button></div>
                </form>
            </details>

            <?php if (empty($category['subcategories'])): ?>
                <p style="margin-left: 20px;">No subcategories found for this category.</p>
            <?php else: ?>
                <ul style="list-style-type: none; padding-left: 20px;">
                    <?php foreach ($category['subcategories'] as $subcategory): ?>
                        <li style="margin-bottom: 5px;">
                            <?= htmlspecialchars($subcategory['name']) ?> 
                            <small>(ID: <?= $subcategory['id'] ?>)</small>
                            <a href="#" onclick="
                                let newSubName = prompt('Edit subcategory name:', '<?= addslashes(htmlspecialchars($subcategory['name'])) ?>');
                                if (newSubName !== null && newSubName.trim() !== '') {
                                    let form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = 'categories.php';
                                    let input1 = document.createElement('input');
                                    input1.type = 'hidden';
                                    input1.name = 'edit_subcategory';
                                    input1.value = '1';
                                    form.appendChild(input1);
                                    let input2 = document.createElement('input');
                                    input2.type = 'hidden';
                                    input2.name = 'subcategory_id';
                                    input2.value = '<?= $subcategory['id'] ?>';
                                    form.appendChild(input2);
                                    let input3 = document.createElement('input');
                                    input3.type = 'hidden';
                                    input3.name = 'subcategory_name';
                                    input3.value = newSubName.trim();
                                    form.appendChild(input3);
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            ">Edit</a> |
                            <a href="categories.php?delete_subcategory=<?= $subcategory['id'] ?>" onclick="return confirm('Are you sure you want to delete this subcategory?');">Delete</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require 'footer.php'; ?>