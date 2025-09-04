<?php
require_once 'database.php';
session_start();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tag_name'])) {
    $new_tag_name = trim($_POST['tag_name']);
    if (!empty($new_tag_name)) {
        try {
            // Check if tag already exists
            $stmt = $pdo->prepare("SELECT id FROM inv_tags WHERE name = ?");
            $stmt->execute([$new_tag_name]);
            $existing_tag = $stmt->fetch();

            if ($existing_tag) {
                $response = ['success' => true, 'id' => $existing_tag['id'], 'name' => $new_tag_name, 'message' => 'Tag already exists.'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO inv_tags (name) VALUES (?)");
                $stmt->execute([$new_tag_name]);
                $new_tag_id = $pdo->lastInsertId();
                $response = ['success' => true, 'id' => $new_tag_id, 'name' => $new_tag_name, 'message' => 'Tag added successfully.'];
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database Error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Tag name cannot be empty.';
    }
}

echo json_encode($response);
?>