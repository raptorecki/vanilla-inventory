<?php
session_start();
require 'header.php';
require_once 'database.php';

$item = null;
$details = null;
$edit_form_error = '';

// Fetch item and details data if ID is provided
if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    try {
        // Fetch item name
        $stmt = $pdo->prepare("SELECT name FROM inv_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();

        if (!$item) {
            $edit_form_error = "Item not found.";
        } else {
            // Fetch details from inv_details
            $stmt = $pdo->prepare("SELECT * FROM inv_details WHERE item_id = ?");
            $stmt->execute([$item_id]);
            $details = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $edit_form_error = "Database Error: " . $e->getMessage();
    }
} else {
    $edit_form_error = "No item ID provided.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_details'])) {
    if ($item) {
        $item_id = $_GET['item_id'];
        $description = trim($_POST['description'] ?? '');
        $voltage = trim($_POST['voltage'] ?? '');
        $current = trim($_POST['current'] ?? '');
        $pinout = trim($_POST['pinout'] ?? '');
        $usecase = trim($_POST['usecase'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        try {
            // Check if details already exist
            $stmt = $pdo->prepare("SELECT id FROM inv_details WHERE item_id = ?");
            $stmt->execute([$item_id]);
            $existing_details = $stmt->fetch();

            if ($existing_details) {
                // Update existing details
                $sql = "UPDATE inv_details SET description = ?, voltage = ?, current = ?, pinout = ?, usecase = ?, notes = ? WHERE item_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$description, $voltage, $current, $pinout, $usecase, $notes, $item_id]);
            } else {
                // Insert new details
                $sql = "INSERT INTO inv_details (item_id, description, voltage, current, pinout, usecase, notes) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$item_id, $description, $voltage, $current, $pinout, $usecase, $notes]);
            }

            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Component details updated successfully.'];
            header('Location: components.php');
            exit();
        } catch (PDOException $e) {
            $edit_form_error = "Database Error: " . $e->getMessage();
        }
    }
}

?>

<h1>Edit Component Details: <?= htmlspecialchars($item['name'] ?? 'N/A') ?></h1>

<?php if ($edit_form_error): ?>
    <p class="error"><?= htmlspecialchars($edit_form_error) ?></p>
<?php endif; ?>

<?php if ($item): ?>
    <form method="POST" action="components_details_edit.php?item_id=<?= htmlspecialchars($_GET['item_id']) ?>" class="form-container">
        <input type="hidden" name="update_details" value="1">

        <div class="form-group form-group-full-width">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($details['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="voltage">Voltage</label>
                <input type="text" id="voltage" name="voltage" value="<?= htmlspecialchars($details['voltage'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="current">Current</label>
                <input type="text" id="current" name="current" value="<?= htmlspecialchars($details['current'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group form-group-full-width">
            <label for="pinout">Pinout</label>
            <textarea id="pinout" name="pinout" rows="3"><?= htmlspecialchars($details['pinout'] ?? '') ?></textarea>
        </div>

        <div class="form-group form-group-full-width">
            <label for="usecase">Usecase</label>
            <textarea id="usecase" name="usecase" rows="3"><?= htmlspecialchars($details['usecase'] ?? '') ?></textarea>
        </div>

        <div class="form-group form-group-full-width">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3"><?= htmlspecialchars($details['notes'] ?? '') ?></textarea>
        </div>

        <div class="form-actions form-group-full-width">
            <a href="components.php" class="button">Cancel</a>
            <button type="submit">Update Details</button>
        </div>
    </form>
<?php endif; ?>

<?php require 'footer.php'; ?>
