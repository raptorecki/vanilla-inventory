<?php
require 'header.php';

// --- Sorting Logic ---
$allowed_columns = ['id', 'name', 'subcategory', 'tags', 'quantity', 'number_used', 'description', 'voltage', 'current', 'pinout', 'usecase', 'notes'];
$sort_column = $_GET['sort'] ?? 'name';
if (!in_array($sort_column, $allowed_columns)) $sort_column = 'name';
$sort_direction = strtolower($_GET['dir'] ?? 'asc');
if (!in_array($sort_direction, ['asc', 'desc'])) $sort_direction = 'asc';

$items = [];
$error_message = '';

try {
    require_once 'database.php';

    // 1. Get the category name from inv_categories where id is 9
    $stmt = $pdo->prepare("SELECT name FROM inv_categories WHERE id = ?");
    $stmt->execute([9]);
    $category_name = $stmt->fetchColumn();

    if ($category_name) {
        // 2. Get the subcategory names from inv_subcategories where id is in (24, 50, 119, 120)
        $subcategory_ids = [24, 50, 119, 120];
        $placeholders = rtrim(str_repeat('?,' , count($subcategory_ids)), ',');
        
        $stmt = $pdo->prepare("SELECT name FROM inv_subcategories WHERE id IN ($placeholders)");
        $stmt->execute($subcategory_ids);
        $subcategory_names = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($subcategory_names) {
            // 3. Use the fetched category and subcategory names to query the inv_items table
            $placeholders = rtrim(str_repeat('?,' , count($subcategory_names)), ',');
            $sql = "SELECT i.*, d.description, d.voltage, d.current, d.pinout, d.usecase, d.notes
                    FROM inv_items i
                    LEFT JOIN inv_details d ON i.id = d.item_id
                    WHERE i.category = ? AND i.subcategory IN ($placeholders)
                    ORDER BY {$sort_column} {$sort_direction}";
            
            $stmt = $pdo->prepare($sql);
            $params = array_merge([$category_name], $subcategory_names);
            $stmt->execute($params);
            $items = $stmt->fetchAll();
        }
    }

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}
?>

<h1>Components</h1>

<?php if ($error_message): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
<?php elseif (empty($items)): ?>
    <p>No components found.</p>
<?php else: ?>
    <details open>
        <summary><h2>Transistors and MOSFET's</h2></summary>
        <table>
            <thead>
                <tr>
                    <?php
                    $headers = [
                        'id' => 'ID', 'name' => 'Name', 'subcategory' => 'Subcategory', 'tags' => 'Tags', 'quantity' => 'Quantity', 'number_used' => 'Used',
                        'description' => 'Description', 'voltage' => 'Voltage', 'current' => 'Current', 'pinout' => 'Pinout', 'usecase' => 'Usecase', 'notes' => 'Notes',
                        'actions' => 'Actions'
                    ];
                    foreach ($headers as $col => $title):
                        $is_sorted_column = ($sort_column === $col);
                        $next_direction = ($is_sorted_column && $sort_direction === 'asc') ? 'desc' : 'asc';
                        $sort_indicator = $is_sorted_column ? (($sort_direction === 'asc') ? ' &#9650;' : ' &#9660;') : ' ';
                    ?>
                        <th><a href="?sort=<?= $col ?>&dir=<?= $next_direction ?>"><?= $title ?></a><?= $sort_indicator ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item):
                    // Ensure the 'actions' case is correctly formatted and terminated with a semicolon
                    ?>
                    <tr>
                        <?php foreach ($headers as $col => $title):
                            // Ensure the 'actions' case is correctly formatted and terminated with a semicolon
                            ?>
                            <td>
                                <?php
                                switch ($col) {
                                    case 'name':
                                        echo '<a href="item_details.php?id=' . htmlspecialchars($item['id']) . '">' . htmlspecialchars($item['name']) . '</a>';
                                        break;
                                    case 'actions':
                                        echo '<a href="components_details_edit.php?item_id=' . htmlspecialchars($item['id']) . '">Edit Details</a> | ';
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
    </details>
<?php endif; ?>

<?php require 'footer.php'; ?>