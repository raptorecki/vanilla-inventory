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
            <div class="form-group"><label for="category">Category *</label><input type="text" id="category" name="category" value="<?= htmlspecialchars($_POST['category'] ?? '') ?>" required></div>
            <div class="form-group"><label for="subcategory">Subcategory *</label><input type="text" id="subcategory" name="subcategory" value="<?= htmlspecialchars($_POST['subcategory'] ?? '') ?>" required></div>
            <div class="form-group"><label for="tags">Tags *</label><input type="text" id="tags" name="tags" value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>" required></div>
            <div class="form-group"><label for="quantity">Quantity *</label><input type="number" id="quantity" name="quantity" value="<?= htmlspecialchars($_POST['quantity'] ?? '') ?>" required></div>
            <div class="form-group"><label for="price">Price *</label><input type="number" step="0.01" id="price" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required></div>
            <div class="form-group"><label for="number_used">Number Used *</label><input type="number" id="number_used" name="number_used" value="<?= htmlspecialchars($_POST['number_used'] ?? '') ?>" required></div>
            <div class="form-group"><label for="source_link">Source Link</label><input type="url" id="source_link" name="source_link" value="<?= htmlspecialchars($_POST['source_link'] ?? '') ?>"></div>
            <div class="form-group"><label for="documentation">Documentation Link</label><input type="url" id="documentation" name="documentation" value="<?= htmlspecialchars($_POST['documentation'] ?? '') ?>"></div>
            
            <div class="form-group"><label for="main_image">Main Image</label><input type="file" id="main_image" name="main_image"></div>
            <div class="form-group"><label for="additional_images">Additional Images</label><input type="file" id="additional_images" name="additional_images[]" multiple></div>
        </div>
        <div class="form-actions">
            <button type="submit">Add Item</button>
            <a href="inventory.php" class="button">Cancel</a>
        </div>
    </form>
</details>

<?php if ($error_message): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
<?php elseif (empty($items)): ?>
    <p>No items found matching your criteria.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <?php
                $headers = [
                    'id' => 'ID', 'name' => 'Name', 'category' => 'Category', 'subcategory' => 'Sub-category',
                    'tags' => 'Tags', 'quantity' => 'Quantity', 'price' => 'Price', 'number_used' => 'Used',
                    'source_link' => 'Source', 'date_added' => 'Added', 'date_modified' => 'Modified',
                    'actions' => 'Actions'
                ];
                foreach ($headers as $col => $title):
                    $is_sorted_column = ($sort_column === $col);
                    $next_direction = ($is_sorted_column && $sort_direction === 'asc') ? 'desc' : 'asc';
                    $sort_indicator = $is_sorted_column ? (($sort_direction === 'asc') ? ' &#9650;' : ' &#9660;') : ' ';
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