<?php
session_start();
require_once 'database.php';

if (isset($_GET['id'])) {
    $item_id = $_GET['id'];

    try {
        $pdo->beginTransaction();

        // Fetch item name for confirmation message
        $stmt = $pdo->prepare("SELECT name FROM inv_items WHERE id = ?");
        $stmt->execute([$item_id]);
        $item_name = $stmt->fetchColumn();

        if ($item_name) {
            // Fetch associated image filenames
            $stmt = $pdo->prepare("SELECT filename FROM inv_item_images WHERE item_id = ?");
            $stmt->execute([$item_id]);
            $images_to_delete = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Delete image files from filesystem
            $image_dir = __DIR__ . '/images/items/' . $item_id . '/';
            foreach ($images_to_delete as $filename) {
                $file_path = $image_dir . $filename;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            // Remove the item's image directory if it exists and is empty
            if (is_dir($image_dir) && count(scandir($image_dir)) == 2) { // . and .. are always present
                rmdir($image_dir);
            }

            // Delete the item (this will cascade delete from inv_item_images)
            $stmt = $pdo->prepare("DELETE FROM inv_items WHERE id = ?");
            $stmt->execute([$item_id]);

            $pdo->commit();

            $_SESSION['flash_message'] = ['type' => 'success', 'text' => 'Item '' . htmlspecialchars($item_name) . '' deleted successfully.'];
        } else {
            $pdo->rollBack();
            $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Item not found.'];
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'Database Error: ' . $e->getMessage()];
    }
} else {
    $_SESSION['flash_message'] = ['type' => 'error', 'text' => 'No item ID provided.'];
}

header('Location: inventory.php');
exit();
?>