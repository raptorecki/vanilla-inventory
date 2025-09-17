<?php
session_start();
require_once 'database.php';
require_once 'helpers.php';

$add_form_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = trim($_POST['name'] ?? '');
    $category_id = trim($_POST['category'] ?? '');
    $subcategory_id = trim($_POST['subcategory'] ?? '');
    $tags_string = trim($_POST['tags'] ?? ''); // Tags are now a direct string
    $quantity = trim($_POST['quantity'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $number_used = trim($_POST['number_used'] ?? '0');
    $source_link = trim($_POST['source_link'] ?? '');
    $main_image_url = trim($_POST['main_image_url'] ?? '');
    $documentation = $_POST['documentation_html'] ?? '';

    // Basic validation
    if (empty($name) || empty($category_id) || empty($subcategory_id) || empty($quantity) || empty($price)) {
        $add_form_error = 'Please fill in all required fields.';
    }

    if (empty($add_form_error)) {
        try {
            $pdo->beginTransaction();

            // Get category name
            $stmt = $pdo->prepare("SELECT name FROM inv_categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $category_name = $stmt->fetchColumn();

            // Get subcategory name
            $stmt = $pdo->prepare("SELECT name FROM inv_subcategories WHERE id = ?");
            $stmt->execute([$subcategory_id]);
            $subcategory_name = $stmt->fetchColumn();

            // Get tags names
            // No longer needed as tags are a direct string input

            // Insert main item data
            $sql = "INSERT INTO inv_items (name, category, subcategory, tags, quantity, price, number_used, source_link, documentation, image, date_added, date_modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name,
                $category_name,
                $subcategory_name,
                $tags_string,
                $quantity,
                $price,
                $number_used,
                $source_link,
                $documentation,
                '' // Placeholder for image path
            ]);
            
            $new_item_id = $pdo->lastInsertId();

            // Handle image upload
            $new_image_path = handleImageUpload('main_image', 'main_image_url', $new_item_id, '');
            if ($new_image_path) {
                $stmt = $pdo->prepare("UPDATE inv_items SET image = ? WHERE id = ?");
                $stmt->execute([$new_image_path, $new_item_id]);
            }

            $pdo->commit();

            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Item added successfully.'];
            header('Location: inventory.php');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $add_form_error = "Database Error: " . $e->getMessage();
        }
    }
}

// If there was an error, we need to display the form again with the error message
// So, we include header and footer here.
require 'header.php';
?>

<h1>Add New Item</h1>

<?php if ($add_form_error): ?>
    <p class="error"><?= htmlspecialchars($add_form_error) ?></p>
<?php endif; ?>

<!-- The form is now in inventory.php -->
<p>Go back to <a href="inventory.php">inventory</a> to add a new item.</p>


<?php require 'footer.php'; ?>