<?php
require_once 'database.php';

// Set a higher time limit for the script to prevent timeouts on large files
set_time_limit(300);

$csv_file = 'inventory.csv';
$row = 0;
$imported_count = 0;
$skipped_count = 0;
$errors = [];

if (($handle = fopen($csv_file, "r")) !== FALSE) {
    // Skip the first line (header)
    fgetcsv($handle);

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row++;
        // Skip empty rows
        if (empty(array_filter($data))) {
            $skipped_count++;
            continue;
        }

        // Map CSV data to inv_items columns
        $name = trim($data[1] ?? '');
        $quantity = (int)($data[2] ?? 0);
        
        // Convert comma decimal to dot decimal for price
        $price_str = str_replace(',', '.', trim($data[3] ?? '0'));
        $price = (float)$price_str;

        $number_used = (int)($data[5] ?? 0);
        $documentation = trim($data[7] ?? '');

        // Default values for missing columns
        $category = '';
        $subcategory = '';
        $tags = '';
        $source_link = '';
        $image = '';

        // Basic validation for required fields (name, quantity, price, number_used)
        if (empty($name) || !is_numeric($quantity) || !is_numeric($price) || !is_numeric($number_used)) {
            $errors[] = "Skipping row $row: Missing or invalid data for Name, Quantity, Price, or Number Used. Data: " . implode(", ", $data);
            $skipped_count++;
            continue;
        }

        try {
            $sql = "INSERT INTO inv_items (name, category, subcategory, tags, quantity, price, number_used, source_link, image, documentation, date_added, date_modified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name,
                $category,
                $subcategory,
                $tags,
                $quantity,
                $price,
                $number_used,
                $source_link,
                $image,
                $documentation
            ]);
            $imported_count++;
        } catch (PDOException $e) {
            $errors[] = "Error importing row $row: " . $e->getMessage() . ". Data: " . implode(", ", $data);
            $skipped_count++;
        }
    }
    fclose($handle);
} else {
    die("Error: Could not open the CSV file.");
}

echo "Import complete.\n";
echo "Total rows processed: " . ($row + $skipped_count) . "\n"; // + skipped_count because fgetcsv advances row even if skipped
echo "Items imported: $imported_count\n";
echo "Rows skipped/failed: $skipped_count\n";

if (!empty($errors)) {
    echo "\nErrors/Skipped details:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

?>