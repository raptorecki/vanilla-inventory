<?php
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
?>