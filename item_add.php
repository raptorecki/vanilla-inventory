<?php
session_start();
require_once 'database.php';

$add_form_error = '';

// Function to handle image uploads
function handle_image_upload($file_input_name, $item_id, $is_main = false) {
    global $pdo;
    $uploaded_files = [];

    if (isset($_FILES[$file_input_name]) && !empty($_FILES[$file_input_name]['name'][0])) {
        $files = $_FILES[$file_input_name];

        // Ensure the target directory exists
        $target_directory = __DIR__ . '/images/items/' . $item_id . '/';
        if (!is_dir($target_directory)) {
            mkdir($target_directory, 0777, true);
        }

        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$key];
                $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                
                if ($is_main) {
                    $filename = 'main.' . $file_extension;
                } else {
                    // Generate a unique filename for additional images
                    $filename = 'additional_' . uniqid() . '.' . $file_extension;
                }

                $target_file = $target_directory . $filename;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $uploaded_files[] = [
                        'filename' => $filename,
                        'is_main' => $is_main,
                    ];
                } else {
                    // Handle move error
                    error_log("Failed to move uploaded file: " . $tmp_name . " to " . $target_file);
                }
            } else {
                // Handle upload error
                error_log("File upload error for " . $name . ": " . $files['error'][$key]);
            }
        }
    }
    return $uploaded_files;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $required_fields = ['name', 'category', 'subcategory', 'tags', 'quantity', 'price', 'number_used', 'source_link'];
    $form_data = [];
    foreach ($required_fields as $field) {
        $form_data[$field] = trim($_POST[$field] ?? '');
    }
    $form_data['documentation'] = $_POST['documentation_html'] ?? ''; // Get HTML from hidden input

    // Basic validation
    foreach ($required_fields as $field) {
        if (empty($form_data[$field])) {
            $add_form_error = 'Please fill in all required fields.';
            break;
        }
    }

    if (empty($add_form_error)) {
        try {
            $pdo->beginTransaction();

            // Insert main item data
            $sql = "INSERT INTO inv_items (name, category, subcategory, tags, quantity, price, number_used, source_link, documentation, date_added, date_modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $form_data['name'],
                $form_data['category'],
                $form_data['subcategory'],
                $form_data['tags'],
                $form_data['quantity'],
                $form_data['price'],
                $form_data['number_used'],
                $form_data['source_link'],
                $form_data['documentation']
            ]);
            
            $new_item_id = $pdo->lastInsertId();

            // Handle main image upload
            $main_image_uploaded = handle_image_upload('main_image', $new_item_id, true);
            if (!empty($main_image_uploaded)) {
                $main_image_filename = $main_image_uploaded[0]['filename'];
                // Update inv_items with the main image filename
                $stmt = $pdo->prepare("UPDATE inv_items SET image = ? WHERE id = ?");
                $stmt->execute([$main_image_filename, $new_item_id]);

                // Insert into inv_item_images
                $stmt = $pdo->prepare("INSERT INTO inv_item_images (item_id, filename, is_main, date_added) VALUES (?, ?, 1, NOW())");
                $stmt->execute([$new_item_id, $main_image_filename]);
            }

            // Handle additional images upload
            $additional_images_uploaded = handle_image_upload('additional_images', $new_item_id);
            foreach ($additional_images_uploaded as $img) {
                $stmt = $pdo->prepare("INSERT INTO inv_item_images (item_id, filename, is_main, date_added) VALUES (?, ?, 0, NOW())");
                $stmt->execute([$new_item_id, $img['filename']]);
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

<form method="POST" action="item_add.php" class="form-container" enctype="multipart/form-data" onsubmit="document.getElementById('documentation_html').value = quill.root.innerHTML;">
    <input type="hidden" name="add_item" value="1">
    
    <div class="form-group"><label for="name">Name *</label><input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></div>
    <div class="form-group"><label for="category">Category *</label><input type="text" id="category" name="category" value="<?= htmlspecialchars($_POST['category'] ?? '') ?>" required></div>
    <div class="form-group"><label for="subcategory">Subcategory *</label><input type="text" id="subcategory" name="subcategory" value="<?= htmlspecialchars($_POST['subcategory'] ?? '') ?>" required></div>
    <div class="form-group"><label for="tags">Tags *</label><input type="text" id="tags" name="tags" value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>" required></div>
    <div class="form-group"><label for="quantity">Quantity *</label><input type="number" id="quantity" name="quantity" value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>" required></div>
    <div class="form-group"><label for="price">Price *</label><input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required></div>
    <div class="form-group"><label for="number_used">Number Used *</label><input type="number" id="number_used" name="number_used" value="<?= htmlspecialchars($_POST['number_used'] ?? '') ?>" required></div>
    <div class="form-group"><label for="source_link">Source Link</label><input type="url" id="source_link" name="source_link" value="<?= htmlspecialchars($_POST['source_link'] ?? '') ?>"></div>
    
    <div class="form-group"><label for="documentation">Documentation</label><div id="editor-container" style="height: 200px;"></div><input type="hidden" name="documentation_html" id="documentation_html"></div>
    
    <div class="form-group"><label for="main_image">Main Image</label><input type="file" id="main_image" name="main_image"></div>
    <div class="form-group"><label for="additional_images">Additional Images</label><input type="file" id="additional_images" name="additional_images[]" multiple></div>

    <div class="form-actions">
        <button type="submit">Add Item</button>
        <a href="inventory.php" class="button">Cancel</a>
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

    // Set initial content if available (e.g., after form submission error)
    var initialContent = `<?= addslashes($_POST['documentation_html'] ?? '') ?>`;
    if (initialContent) {
        quill.root.innerHTML = initialContent;
    }
</script>

<?php require 'footer.php'; ?>