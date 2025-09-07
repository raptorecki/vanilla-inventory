<?php
require 'header.php';

// --- Sorting Logic ---
$allowed_columns = ['id', 'name', 'tags', 'quantity', 'number_used', 'description', 'voltage', 'current', 'pinout', 'usecase', 'notes', 'price', 'source_link', 'date_added', 'date_modified']; // Add back price, source, added, modified for sorting
$sort_column = $_GET['sort'] ?? 'name';
if (!in_array($sort_column, $allowed_columns)) $sort_column = 'name';
$sort_direction = strtolower($_GET['dir'] ?? 'asc');
if (!in_array($sort_direction, ['asc', 'desc'])) $sort_direction = 'asc';

$error_message = '';
$categories_data = [];

// Define categories and their subcategories/headers
$category_configs = [
    'Transistors and MOSFET\'s' => [
        'category_id' => 9,
        'subcategory_ids' => [24, 50, 119, 120],
        'headers' => [
            'id' => 'ID', 'name' => 'Name', 'tags' => 'Tags', 'quantity' => 'Quantity', 'number_used' => 'Used',
            'description' => 'Description', 'voltage' => 'Voltage', 'current' => 'Current', 'pinout' => 'Pinout', 'usecase' => 'Usecase', 'notes' => 'Notes',
            'actions' => 'Actions'
        ],
        'sql_join_details' => true // Indicates if inv_details should be joined
    ],
    'Diodes' => [
        'category_id' => 6,
        'subcategory_ids' => [51, 52],
        'headers' => [
            'id' => 'ID', 'name' => 'Name', 'tags' => 'Tags', 'quantity' => 'Quantity', 'number_used' => 'Used',
            'description' => 'Description', 'voltage' => 'Voltage', 'current' => 'Current', 'pinout' => 'Pinout', 'usecase' => 'Usecase', 'notes' => 'Notes',
            'actions' => 'Actions'
        ],
        'sql_join_details' => true
    ],
    'Others' => [
        'category_id' => 9, // Assuming 'Others' also falls under category 9 for now, adjust if needed
        'subcategory_ids' => [117, 118, 121, 114, 115, 116],
        'headers' => [
            'id' => 'ID', 'name' => 'Name', 'tags' => 'Tags', 'quantity' => 'Quantity', 'number_used' => 'Used',
            'description' => 'Description', 'voltage' => 'Voltage', 'current' => 'Current', 'pinout' => 'Pinout', 'usecase' => 'Usecase', 'notes' => 'Notes',
            'actions' => 'Actions'
        ],
        'sql_join_details' => true // Join inv_details for 'Others'
    ],
    'Development Boards' => [
        'category_id' => 1,
        'subcategory_ids' => null, // No subcategory limitation
        'headers' => [
            'id' => 'ID', 'name' => 'Name', 'tags' => 'Tags', 'quantity' => 'Quantity', 'number_used' => 'Used',
            'description' => 'Description', 'voltage' => 'Voltage', 'current' => 'Current', 'pinout' => 'Pinout', 'usecase' => 'Usecase', 'notes' => 'Notes',
            'actions' => 'Actions'
        ],
        'sql_join_details' => true
    ]
];

try {
    require_once 'database.php';

    foreach ($category_configs as $title => $config) {
        $category_id = $config['category_id'];
        $subcategory_ids = $config['subcategory_ids'];
        $join_details = $config['sql_join_details'];

        // Get the category name from inv_categories
        $stmt = $pdo->prepare("SELECT name FROM inv_categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category_name = $stmt->fetchColumn();

        $items = [];
        if ($category_name) {
            $subcategory_condition = "";
            $subcategory_params = [];

            if ($subcategory_ids !== null && !empty($subcategory_ids)) {
                // Get the subcategory names from inv_subcategories
                $placeholders = rtrim(str_repeat('?,' , count($subcategory_ids)), ",");
                $stmt = $pdo->prepare("SELECT name FROM inv_subcategories WHERE id IN ($placeholders)");
                $stmt->execute($subcategory_ids);
                $subcategory_names = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($subcategory_names)) {
                    $subcategory_placeholders = rtrim(str_repeat('?,' , count($subcategory_names)), ",");
                    $subcategory_condition = " AND i.subcategory IN ({$subcategory_placeholders})";
                    $subcategory_params = $subcategory_names;
                }
            }

            $select_cols = "i.*";
            $join_clause = "";
            if ($join_details) {
                $select_cols .= ", d.description, d.voltage, d.current, d.pinout, d.usecase, d.notes";
                $join_clause = "LEFT JOIN inv_details d ON i.id = d.item_id";
            }

            $sql = "SELECT {$select_cols} FROM inv_items i {$join_clause}
                    WHERE i.category = ? {$subcategory_condition}
                    ORDER BY {$sort_column} {$sort_direction}";
            
            $stmt = $pdo->prepare($sql);
            $params = array_merge([$category_name], $subcategory_params);
            $stmt->execute($params);
            $items = $stmt->fetchAll();
        }
        $categories_data[$title] = ['items' => $items, 'headers' => $config['headers']];
    }

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}
?>

<h1>Components</h1>

<?php if ($error_message): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
<?php else: ?>
    <?php foreach ($categories_data as $title => $data): ?>
        <details>
            <summary><h2><?= htmlspecialchars($title) ?></h2></summary>
            <?php if (empty($data['items'])): ?>
                <p>No <?= htmlspecialchars(strtolower($title)) ?> found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <?php
                            $headers = $data['headers'];
                            foreach ($headers as $col => $header_title):
                                $is_sorted_column = ($sort_column === $col);
                                $next_direction = ($is_sorted_column && $sort_direction === 'asc') ? 'desc' : 'asc';
                                $sort_indicator = $is_sorted_column ? (($sort_direction === 'asc') ? ' &#9650;' : ' &#9660;') : ' ';
                            ?>
                                <th><a href="?sort=<?= $col ?>&dir=<?= $next_direction ?>"><?= $header_title ?></a><?= $sort_indicator ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['items'] as $item):
                            echo '<tr>';
                            foreach ($headers as $col => $header_title):
                                echo '<td>';
                                switch ($col) {
                                    case 'name':
                                        echo '<a href="item_details.php?id=' . htmlspecialchars($item['id']) . '">' . htmlspecialchars($item['name']) . '</a>';
                                        break;
                                    case 'source_link': // Only for default headers
                                        echo !empty($item[$col]) ? '<a href="' . htmlspecialchars($item[$col]) . '" target="_blank">Link</a>' : '—';
                                        break;
                                    case 'actions':
                                        // Conditionally show "Edit Details" link only for Transistors and MOSFET's
                                        echo '<a href="components_details_edit.php?item_id=' . htmlspecialchars($item['id']) . '">Edit Details</a> | ';
                                        echo '<a href="item_edit.php?id=' . htmlspecialchars($item['id']) . '">Edit</a> | ';
                                        echo '<a href="item_delete.php?id=' . htmlspecialchars($item['id']) . '" onclick="return confirm(\'Are you sure you want to delete this item?\');">Delete</a>';
                                        break;
                                    default:
                                        echo htmlspecialchars($item[$col] ?? '—');
                                        break;
                                }
                                echo '</td>';
                            endforeach;
                            echo '</tr>';
                        endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </details>
    <?php endforeach; ?>
<?php endif; ?>

<?php require 'footer.php'; ?>