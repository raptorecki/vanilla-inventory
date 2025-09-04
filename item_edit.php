<?php
session_start();
require 'header.php';
require_once 'database.php';

// Function to handle image upload from file or URL
function handleImageUpload($file_input_name, $url_input_name, $item_id, $current_image_path) {
    $upload_dir = 'images/'; // Base directory for images
    $new_image_path = $current_image_path; // Default to current image path

    // Check for file upload
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES[$file_input_name]['tmp_name'];
        $file_name = $_FILES[$file_input_name]['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($file_ext, $allowed_ext)) {
            error_log("Invalid file type for upload: " . $file_ext);
            return null; // Invalid file type
        }

        // Generate unique filename and nested path
        $unique_id = uniqid();
        $subdir1 = substr($unique_id, 0, 2); // First two chars of unique ID
        $subdir2 = substr($unique_id, 2, 2); // Next two chars

        $target_dir = $upload_dir . $subdir1 . '/' . $subdir2 . '/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directories recursively
        }

        $new_file_name = $unique_id . '.' . $file_ext;
        $target_file = $target_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_name, $target_file)) {
            $new_image_path = $target_file;
        } else {
            error_log("Failed to move uploaded file: " . $file_tmp_name . " to " . $target_file);
            return null; // Failed to move file
        }
    }
    // Check for URL input
    else if (!empty($_POST[$url_input_name])) {
        $image_url = trim($_POST[$url_input_name]);

        // Validate URL (basic validation)
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            error_log("Invalid image URL: " . $image_url);
            return null; // Invalid URL
        }

        // Fetch image content
        $image_content = @file_get_contents($image_url);
        if ($image_content === false) {
            error_log("Failed to fetch image from URL: " . $image_url);
            return null; // Failed to fetch
        }

        // Determine file extension from URL or content type
        $file_ext = 'jpg'; // Default to jpg
        $content_type = get_headers($image_url, 1)['Content-Type'] ?? '';
        if (is_array($content_type)) {
            $content_type = $content_type[0];
        }
        if (strpos($content_type, 'image/png') !== false) $file_ext = 'png';
        else if (strpos($content_type, 'image/gif') !== false) $file_ext = 'gif';
        else if (strpos($content_type, 'image/jpeg') !== false) $file_ext = 'jpeg';
        else if (strpos($content_type, 'image/webp') !== false) $file_ext = 'webp';

        // Generate unique filename and nested path
        $unique_id = uniqid();
        $subdir1 = substr($unique_id, 0, 2);
        $subdir2 = substr($unique_id, 2, 2);

        $target_dir = $upload_dir . $subdir1 . '/' . $subdir2 . '/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $new_file_name = $unique_id . '.' . $file_ext;
        $target_file = $target_dir . $new_file_name;

        if (file_put_contents($target_file, $image_content)) {
            $new_image_path = $target_file;
        } else {
            error_log("Failed to save image from URL: " . $image_url . " to " . $target_file);
            return null; // Failed to save
        }
    }
    // Handle image removal
    else if (isset($_POST[$url_input_name]) && $_POST[$url_input_name] === 'REMOVE') {
        // If there was a current image, delete it from the filesystem
        if (!empty($current_image_path) && file_exists($current_image_path)) {
            unlink($current_image_path);
            // Optionally, remove empty parent directories
            $dir = dirname($current_image_path);
            if (is_dir($dir) && count(scandir($dir)) == 2) { // . and ..
                rmdir($dir);
                $parent_dir = dirname($dir);
                if (is_dir($parent_dir) && count(scandir($parent_dir)) == 2) {
                    rmdir($parent_dir);
                }
            }
        }
        $new_image_path = ''; // Set image path to empty in DB
    }

    return $new_image_path;
}

$item = null;
$edit_form_error = '';
$item_images = [];

// Fetch item data if ID is provided
if (isset($_GET['id'])) {
    $item_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM inv_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();

        if (!$item) {
            $edit_form_error = "Item not found.";
        } else {
            // Fetch item images
            $stmt = $pdo->prepare("SELECT * FROM inv_item_images WHERE item_id = ? ORDER BY is_main DESC, id ASC");
            $stmt->execute([$item_id]);
            $item_images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $edit_form_error = "Database Error: " . $e->getMessage();
    }
} else {
    $edit_form_error = "No item ID provided.";
}

// Fetch all categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM inv_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error fetching categories: " . $e->getMessage());
}

// Fetch all subcategories and group them by category_id
$subcategories_by_category = [];
try {
    $stmt = $pdo->query("SELECT id, name, category_id FROM inv_subcategories ORDER BY name");
    $all_subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($all_subcategories as $subcat) {
        $subcategories_by_category[$subcat['category_id']][] = $subcat;
    }
} catch (PDOException $e) {
    error_log("Database Error fetching subcategories: " . $e->getMessage());
}

// Fetch all tags
$all_tags = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM inv_tags ORDER BY name");
    $all_tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Error fetching tags: " . $e->getMessage());
}

// Handle form submission for updating item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
    if ($item) { // Only proceed if item was successfully fetched
        $item_id = $item['id'];
        $name = trim($_POST['name'] ?? '');
        $category_id = trim($_POST['category'] ?? '');
        $subcategory_id = trim($_POST['subcategory'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $quantity = trim($_POST['quantity'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $number_used = trim($_POST['number_used'] ?? '');
        $source_link = trim($_POST['source_link'] ?? '');
        $documentation = $_POST['documentation_html'] ?? '';

        // Handle image upload/URL/removal
        $current_image_path = $_POST['current_main_image'] ?? $item['image'] ?? '';
        $new_image_path = handleImageUpload('main_image_file', 'main_image_url', $item_id, $current_image_path);
        if ($new_image_path === null) {
            $edit_form_error = "Image upload/fetch failed. Please check the file or URL.";
            // Do not proceed with item update if image handling failed
            // You might want to keep the old image path if the new one failed
            $new_image_path = $item['image'];
        }

        // Get category and subcategory names from their IDs
        $category_name = '';
        foreach($categories as $cat) {
            if ($cat['id'] == $category_id) {
                $category_name = $cat['name'];
                break;
            }
        }

        $subcategory_name = '';
        if (isset($subcategories_by_category[$category_id])) {
            foreach($subcategories_by_category[$category_id] as $subcat) {
                if ($subcat['id'] == $subcategory_id) {
                    $subcategory_name = $subcat['name'];
                    break;
                }
            }
        }

        try {
            $pdo->beginTransaction();

            // Update main item data, including the image path
            $sql = "UPDATE inv_items SET name = ?, category = ?, subcategory = ?, tags = ?, quantity = ?, price = ?, number_used = ?, source_link = ?, documentation = ?, image = ?, date_modified = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name,
                $category_name,
                $subcategory_name,
                $tags,
                $quantity,
                $price,
                $number_used,
                $source_link,
                $documentation,
                $new_image_path, // New image path
                $item_id
            ]);

            $pdo->commit();

            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Item updated successfully.'];
            header('Location: inventory.php');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $edit_form_error = "Database Error: " . $e->getMessage();
        }
    }
}

// Get the current item's category and subcategory IDs
$item_category_id = null;
$item_subcategory_id = null;
if ($item) {
    foreach ($categories as $cat) {
        if ($cat['name'] === $item['category']) {
            $item_category_id = $cat['id'];
            break;
        }
    }
    if ($item_category_id && isset($subcategories_by_category[$item_category_id])) {
        foreach ($subcategories_by_category[$item_category_id] as $subcat) {
            if ($subcat['name'] === $item['subcategory']) {
                $item_subcategory_id = $subcat['id'];
                break;
            }
        }
    }
}


?>

<h1>Edit Item: <?= htmlspecialchars($item['name'] ?? 'N/A') ?></h1>

<?php if ($edit_form_error): ?>
    <p class="error"><?= htmlspecialchars($edit_form_error) ?></p>
<?php endif; ?>

<?php if ($item): ?>
    <!-- Updated Form Structure with proper grid layout -->
    <form method="POST" action="item_edit.php?id=<?= htmlspecialchars($item['id']) ?>" class="form-container" enctype="multipart/form-data" onsubmit="console.log(quill.root.innerHTML); document.getElementById('documentation_html').value = quill.root.innerHTML;">
        <input type="hidden" name="update_item" value="1">
        
        <!-- Name field - Full width at top -->
        <div class="form-group form-group-full-width">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($item['name'] ?? '') ?>" required>
        </div>
        
        <!-- Category and Subcategory - Side by side -->
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">Select a Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($item_category_id == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="subcategory">Subcategory *</label>
                <select id="subcategory" name="subcategory" required>
                    <option value="">Select a Subcategory</option>
                    <?php if ($item_category_id && isset($subcategories_by_category[$item_category_id])): ?>
                        <?php foreach ($subcategories_by_category[$item_category_id] as $subcat): ?>
                            <option value="<?= htmlspecialchars($subcat['id']) ?>" <?= ($item_subcategory_id == $subcat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subcat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input type="number" id="quantity" name="quantity" value="<?= htmlspecialchars($item['quantity'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($item['price'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="number_used">Number Used *</label>
                <input type="number" id="number_used" name="number_used" value="<?= htmlspecialchars($item['number_used'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="source_link">Source Link</label>
                <input type="url" id="source_link" name="source_link" value="<?= htmlspecialchars($item['source_link'] ?? '') ?>">
            </div>
        </div>
        
        <!-- Main Image Input -->
        <div class="form-group form-group-full-width image-upload-container">
            <label for="main_image_file">Main Image</label>
            <input type="file" id="main_image_file" name="main_image_file" accept="image/*">
            <p style="text-align: center; margin: 10px 0; color: #c0c0c0;">OR</p>
            <input type="url" id="main_image_url" name="main_image_url" placeholder="Enter image URL">
            <?php if (!empty($item['image'])): ?>
                <div class="image-preview-wrapper">
                    <img id="main_image_preview" src="<?= htmlspecialchars($item['image']) ?>" alt="Main Image Preview" class="image-preview">
                    <button type="button" class="remove-image-btn" id="remove_main_image">Remove Image</button>
                </div>
            <?php else: ?>
                <img id="main_image_preview" src="" alt="Main Image Preview" class="image-preview" style="display: none;">
            <?php endif; ?>
            <input type="hidden" name="current_main_image" id="current_main_image" value="<?= htmlspecialchars($item['image'] ?? '') ?>">
        </div>

        <!-- Tags Display - Full width -->
        <div class="form-group form-group-full-width">
            <label for="tags">Selected Tags</label>
            <div class="tags-display-container" id="tags-display-container"></div>
            <input type="hidden" name="tags" id="hidden-tags">
        </div>
        
        <!-- Tag Input - Full width -->
        <div class="form-group form-group-full-width">
            <label for="tag-input">Add a tag</label>
            <div class="tags-autocomplete">
                <input type="text" id="tag-input" placeholder="Type to search for existing tags or create new ones...">
                <div class="tags-autocomplete-items" id="tags-autocomplete-list"></div>
            </div>
        </div>
        
        <!-- Documentation - Full width at bottom -->
        <div class="form-group form-group-full-width">
            <label for="documentation">Documentation</label>
            <div id="editor-container" style="height: 250px;"></div>
            <input type="hidden" name="documentation_html" id="documentation_html">
        </div>

        <!-- Form Actions -->
        <div class="form-actions form-group-full-width">
            <a href="inventory.php" class="button">Cancel</a>
            <button type="submit">Update Item</button>
        </div>
    </form>

<!-- Quill.js Integration -->
<link href="js/quill/quill.snow.css" rel="stylesheet">
<script src="js/quill/quill.js"></script>
<script>
    var quill;
    document.addEventListener('DOMContentLoaded', function() {
        quill = new Quill('#editor-container', {
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

        const categories = <?= json_encode($categories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const subcategoriesByCategory = <?= json_encode($subcategories_by_category, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const categorySelect = document.getElementById('category');
        const subcategorySelect = document.getElementById('subcategory');

        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            subcategorySelect.innerHTML = '<option value="">Select a Subcategory</option>';

            if (categoryId && subcategoriesByCategory[categoryId]) {
                subcategoriesByCategory[categoryId].forEach(function(subcategory) {
                    const option = document.createElement('option');
                    option.value = subcategory.id;
                    option.textContent = subcategory.name;
                    subcategorySelect.appendChild(option);
                });
            }
        });

        // Tags input functionality
        const allTags = <?= json_encode($all_tags, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const tagsDisplayContainer = document.getElementById('tags-display-container');
        const tagInput = document.getElementById('tag-input');
        const hiddenTagsInput = document.getElementById('hidden-tags');
        const autocompleteList = document.getElementById('tags-autocomplete-list');
        let selectedTags = [];

        function updateHiddenTags() {
            hiddenTagsInput.value = selectedTags.join(', ');
        }

        function createTagElement(tagName) {
            if (selectedTags.includes(tagName)) return;

            const tagEl = document.createElement('div');
            tagEl.className = 'tag-item';
            tagEl.textContent = tagName;

            const removeEl = document.createElement('span');
            removeEl.className = 'remove-tag';
            removeEl.innerHTML = '&times;';
            removeEl.onclick = () => {
                const index = selectedTags.indexOf(tagName);
                if (index > -1) {
                    selectedTags.splice(index, 1);
                }
                tagEl.remove();
                updateHiddenTags();
            };

            tagEl.appendChild(removeEl);
            tagsDisplayContainer.appendChild(tagEl);
            selectedTags.push(tagName);
            updateHiddenTags();
        }

        // Initial tags
        const initialTags = `<?= addslashes($item['tags'] ?? '') ?>`.split(', ').filter(t => t);
        initialTags.forEach(createTagElement);

        tagInput.addEventListener('input', () => {
            const value = tagInput.value.toLowerCase();
            autocompleteList.innerHTML = '';
            if (!value) return;

            const filteredTags = allTags.filter(tag => tag.name.toLowerCase().includes(value) && !selectedTags.includes(tag.name));

            filteredTags.forEach(tag => {
                const item = document.createElement('div');
                item.textContent = tag.name;
                item.dataset.tagName = tag.name;
                autocompleteList.appendChild(item);
            });

            // Add 'Create new tag' option
            const createNewTagItem = document.createElement('div');
            createNewTagItem.innerHTML = `<strong>Create "${tagInput.value}"</strong>`;
            createNewTagItem.dataset.newTag = tagInput.value;
            autocompleteList.appendChild(createNewTagItem);
        });

        autocompleteList.addEventListener('click', (e) => {
            let target = e.target;
            if (target.tagName === 'STRONG') {
                target = target.parentElement;
            }

            if (target.dataset.tagName) {
                createTagElement(target.dataset.tagName);
                tagInput.value = '';
                autocompleteList.innerHTML = '';
            } else if (target.dataset.newTag) {
                const newTagName = target.dataset.newTag.trim();
                if (newTagName) {
                    fetch('ajax_add_tag.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `tag_name=${encodeURIComponent(newTagName)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (!allTags.find(t => t.name === data.name)) {
                                allTags.push({id: data.id, name: data.name});
                            }
                            createTagElement(data.name);
                        } else {
                            alert(data.message);
                        }
                    });
                    tagInput.value = '';
                    autocompleteList.innerHTML = '';
                }
            }
        });

        tagInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const firstItem = autocompleteList.querySelector('div');
                if (firstItem) {
                    firstItem.click();
                }
            }
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.tags-autocomplete')) {
                autocompleteList.innerHTML = '';
            }
        });
    });
</script>

<?php endif; ?>

<?php require 'footer.php'; ?>