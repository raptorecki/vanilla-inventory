<?php
require 'header.php';

// --- Sorting Logic ---
$allowed_columns = ['id', 'name', 'category', 'subcategory', 'tags', 'quantity', 'price', 'number_used', 'source_link', 'date_added', 'date_modified'];
$sort_column = $_GET['sort'] ?? 'id';
if (!in_array($sort_column, $allowed_columns)) $sort_column = 'id';
$sort_direction = strtolower($_GET['dir'] ?? 'asc');
if (!in_array($sort_direction, ['asc', 'desc'])) $sort_direction = 'asc';

// --- Search Logic ---
$search_term = trim($_GET['search'] ?? '');
$searchable_columns = ['name', 'category', 'subcategory', 'tags', 'source_link'];

$items = [];
$error_message = '';

try {
    // Include database connection
    require_once 'database.php';

    // Fetch categories, subcategories, and tags
    $categories = $pdo->query("SELECT * FROM inv_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $subcategories = $pdo->query("SELECT * FROM inv_subcategories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $tags = $pdo->query("SELECT * FROM inv_tags ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM inv_items";
    $params = [];

    if (!empty($search_term)) {
        $search_conditions = [];
        foreach ($searchable_columns as $column) {
            $search_conditions[] = "{$column} LIKE ?";
            $params[] = "%{$search_term}%";
        }
        $sql .= " WHERE (" . implode(' OR ', $search_conditions) . ")";
    }

    $sql .= " ORDER BY {$sort_column} {$sort_direction}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}
?>

<h1>Inventory</h1>
<form method="GET" action="inventory.php" class="search-container">
    <input type="text" name="search" placeholder="Search for items..." value="<?= htmlspecialchars($search_term) ?>">
    <button type="submit">Search</button>
    <?php if (!empty($search_term)): ?>
        <a href="inventory.php">Clear</a>
    <?php endif; ?>
</form>

<details <?= !empty($add_form_error) ? 'open' : '' ?>> 
    <summary>Add New Item</summary>
    <form method="POST" action="item_add.php" class="form-container" enctype="multipart/form-data">
        <div class="form-fields-container">
            <input type="hidden" name="add_item" value="1">
            <?php if ($add_form_error): ?>
                <div class="error" style="grid-column: 1 / -1;"><?= htmlspecialchars($add_form_error) ?></div>
            <?php endif; ?>
            <div class="form-group form-group-name"><label for="name">Name *</label><input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">Select a Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="subcategory">Subcategory *</label>
                <select id="subcategory" name="subcategory" required>
                    <option value="">Select a Subcategory</option>
                    <?php foreach ($subcategories as $subcat): ?>
                        <option value="<?= htmlspecialchars($subcat['id']) ?>" data-category-id="<?= htmlspecialchars($subcat['category_id']) ?>"><?= htmlspecialchars($subcat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tags">Tags</label>
                <select id="tags" name="tags[]" multiple>
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?= htmlspecialchars($tag['id']) ?>"><?= htmlspecialchars($tag['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group"><label for="quantity">Quantity *</label><input type="number" id="quantity" name="quantity" value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>" required></div>
            <div class="form-group"><label for="price">Price *</label><input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required></div>
            <div class="form-group"><label for="number_used">Number Used *</label><input type="number" id="number_used" name="number_used" value="0" required></div>
            <div class="form-group"><label for="source_link">Source Link</label><input type="url" id="source_link" name="source_link" value="<?= htmlspecialchars($_POST['source_link'] ?? '') ?>"></div>
            <div class="form-group"><label for="main_image_url">Main Image URL</label><input type="url" id="main_image_url" name="main_image_url" placeholder="Enter image URL"></div>
        </div>
        <div class="form-actions">
            <button type="submit">Add Item</button>
            <a href="inventory.php" class="button">Cancel</a>
        </div>
    </form>
</details>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const subcategorySelect = document.getElementById('subcategory');
    const subcategoryOptions = Array.from(subcategorySelect.options);

    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        
        // Clear current options
        subcategorySelect.innerHTML = '<option value="">Select a Subcategory</option>';

        // Filter and add relevant options
        subcategoryOptions.forEach(function(option) {
            if (option.dataset.categoryId === categoryId || option.value === '') {
                subcategorySelect.appendChild(option.cloneNode(true));
            }
        });
    });
});
</script>

<?php if ($error_message): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
<?php elseif (empty($items)): ?>
    <p>No items found matching your criteria.</p>
<?php else: ?>
    <?php
    $headers = [
        'id' => 'ID', 'name' => 'Name', 'category' => 'Category', 'subcategory' => 'Sub-category',
        'tags' => 'Tags', 'quantity' => 'Quantity', 'price' => 'Price', 'number_used' => 'Used',
        'source_link' => 'Source', 'date_added' => 'Added', 'date_modified' => 'Modified',
        'actions' => 'Actions'
    ];
    
    ?>
    <table>
        <colgroup>
            <col style="width: 5%;"> <!-- ID -->
            <col style="width: 25%;"> <!-- Name -->
            <col style="width: 10%;"> <!-- Category -->
            <col style="width: 10%;"> <!-- Sub-category -->
            <col style="width: 10%;"> <!-- Tags -->
            <col style="width: 5%;"> <!-- Quantity -->
            <col style="width: 5%;"> <!-- Price -->
            <col style="width: 5%;"> <!-- Used -->
            <col style="width: 5%;"> <!-- Source -->
            <col style="width: 10%;"> <!-- Added -->
            <col style="width: 10%;"> <!-- Modified -->
            <col style="width: 10%;"> <!-- Actions -->
        </colgroup>
        <thead>
            <tr>
                <?php
                foreach ($headers as $col => $title):
                    $is_sorted_column = ($sort_column === $col);
                    $next_direction = ($is_sorted_column && $sort_direction === 'asc') ? 'desc' : 'asc';
                    $sort_indicator = $is_sorted_column ? (($sort_direction === 'asc') ? ' &#9650;' : ' &#9660;') : ' ';
                    $class = $class_map[$col] ?? '';
                ?>
                    <th><a href="?sort=<?= $col ?>&dir=<?= $next_direction ?>&search=<?= htmlspecialchars($search_term) ?>"><?= $title ?></a><?= $sort_indicator ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item):
                ?>
                <tr>
                    <?php foreach ($headers as $col => $title):
                        $class = $class_map[$col] ?? '';
                        ?>
                        <td>
                            <?php
                            switch ($col) {
                                case 'source_link':
                                    echo !empty($item[$col]) ? '<a href="' . htmlspecialchars($item[$col]) . '" target="_blank">Link</a>' : '—';
                                    break;
                                case 'name':
                                    echo '<a href="item_details.php?id=' . htmlspecialchars($item['id']) . '">' . htmlspecialchars($item['name']) . '</a>';
                                    break;
                                case 'actions':
                                    echo '<a href="item_edit.php?id=' . htmlspecialchars($item['id']) . '">Edit</a> | ';
                                    echo '<a href="item_delete.php?id=' . htmlspecialchars($item['id']) . '" onclick="return confirm(\'Are you sure you want to delete this item?\');">Delete</a>';
                                    break;
                                default:
                                    echo htmlspecialchars($item[$col] ?? '—');
                                    break;
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require 'footer.php'; ?>
