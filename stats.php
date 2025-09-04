<?php
require 'header.php';
require_once 'database.php';

$stats = [
    'total_items' => 0,
    'total_items_cost' => 0,
    'total_items_used' => 0,
    'total_cost_items_used' => 0,
    'esp32_boards_available' => 0,
    'esp8266_boards_available' => 0,
];
$error_message = '';

try {
    // Fetch statistics from inv_items table
    $stmt = $pdo->query("
        SELECT
            COUNT(id) AS total_items,
            SUM(quantity * price) AS total_items_cost,
            SUM(number_used) AS total_items_used,
            SUM(number_used * price) AS total_cost_items_used
        FROM inv_items
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $stats['total_items'] = $result['total_items'] ?? 0;
        $stats['total_items_cost'] = $result['total_items_cost'] ?? 0;
        $stats['total_items_used'] = $result['total_items_used'] ?? 0;
        $stats['total_cost_items_used'] = $result['total_cost_items_used'] ?? 0;
    }

    // Fetch ESP32 Boards Available
    $stmt = $pdo->query("
        SELECT
            SUM(quantity - number_used) AS esp32_boards_available
        FROM inv_items
        WHERE subcategory = 'ESP32' AND (quantity - number_used) > 0
    ");
    $esp32_result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($esp32_result) {
        $stats['esp32_boards_available'] = $esp32_result['esp32_boards_available'] ?? 0;
    }

    // Fetch ESP8266 Boards Available
    $stmt = $pdo->query("
        SELECT
            SUM(quantity - number_used) AS esp8266_boards_available
        FROM inv_items
        WHERE subcategory = 'ESP8266' AND (quantity - number_used) > 0
    ");
    $esp8266_result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($esp8266_result) {
        $stats['esp8266_boards_available'] = $esp8266_result['esp8266_boards_available'] ?? 0;
    }

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}

// Helper function for currency formatting (assuming it doesn't exist yet)
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return number_format($amount, 2) . ' PLN';
    }
}

?>

<h1>Inventory Statistics</h1>

<?php if ($error_message): ?>
    <p class="error"><?= htmlspecialchars($error_message) ?></p>
<?php else: ?>
    <div class="stats-grid">
        <div class="stat-card"><h3>Total Items</h3><p><?= number_format($stats['total_items']) ?></p></div>
        <div class="stat-card"><h3>Total Items Cost</h3><p><?= formatCurrency($stats['total_items_cost']) ?></p></div>
        <div class="stat-card"><h3>Total Items Used</h3><p><?= number_format($stats['total_items_used']) ?></p></div>
        <div class="stat-card"><h3>Total Cost of Items Used</h3><p><?= formatCurrency($stats['total_cost_items_used']) ?></p></div>
        <div class="stat-card"><h3>ESP32 Boards Available</h3><p><?= number_format($stats['esp32_boards_available']) ?></p></div>
        <div class="stat-card"><h3>ESP8266 Boards Available</h3><p><?= number_format($stats['esp8266_boards_available']) ?></p></div>
    </div>
<?php endif; ?>

<?php require 'footer.php'; ?>