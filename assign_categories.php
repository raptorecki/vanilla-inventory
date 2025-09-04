<?php
require_once 'database.php';

// Set a higher time limit for the script
set_time_limit(300);

echo "Starting category and subcategory assignment...\n";

try {
    // Fetch all categories and subcategories for lookup
    $categories_map = [];
    $stmt = $pdo->query("SELECT id, name FROM inv_categories");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories_map[$row['name']] = $row['id'];
    }

    $subcategories_map = [];
    $stmt = $pdo->query("SELECT id, category_id, name FROM inv_subcategories");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $subcategories_map[$row['name']] = ['id' => $row['id'], 'category_id' => $row['category_id']];
    }

    // Fetch all items
    $stmt = $pdo->query("SELECT id, name FROM inv_items");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updated_count = 0;
    foreach ($items as $item) {
        $item_id = $item['id'];
        $item_name = strtolower($item['name']);
        $assigned_category_name = ''; // Store name, not ID
        $assigned_subcategory_name = ''; // Store name, not ID

        // --- Categorization Logic (similar to analyze_inventory.php) ---
        // Prioritize more specific matches

        // Modules
        if (str_contains($item_name, 'moduł') || str_contains($item_name, 'module')) {
            $assigned_category_name = 'Modules';
            if (str_contains($item_name, 'wifi') || str_contains($item_name, 'esp')) {
                $assigned_subcategory_name = 'WiFi';
            } elseif (str_contains($item_name, 'rs485')) {
                $assigned_subcategory_name = 'RS485';
            } elseif (str_contains($item_name, 'przekaźnik') || str_contains($item_name, 'relay')) {
                $assigned_subcategory_name = 'Relay';
            } elseif (str_contains($item_name, 'ładowania') || str_contains($item_name, 'charger')) {
                $assigned_subcategory_name = 'Charger';
            } elseif (str_contains($item_name, 'gps')) {
                $assigned_subcategory_name = 'GPS';
            }
        }
        // Sensors
        elseif (str_contains($item_name, 'czujnik') || str_contains($item_name, 'sensor')) {
            $assigned_category_name = 'Sensors';
            if (str_contains($item_name, 'temperatury') || str_contains($item_name, 'temp')) {
                $assigned_subcategory_name = 'Temperature';
            } elseif (str_contains($item_name, 'wilgotności') || str_contains($item_name, 'humidity')) {
                $assigned_subcategory_name = 'Humidity';
            } elseif (str_contains($item_name, 'ruchu') || str_contains($item_name, 'pir')) {
                $assigned_subcategory_name = 'Motion (PIR)';
            } elseif (str_contains($item_name, 'magnetyczny') || str_contains($item_name, 'kontaktron')) {
                $assigned_subcategory_name = 'Magnetic (Reed Switch)';
            } elseif (str_contains($item_name, 'napięcia') || str_contains($item_name, 'voltage')) {
                $assigned_subcategory_name = 'Voltage';
            } elseif (str_contains($item_name, 'prądu') || str_contains($item_name, 'current')) {
                $assigned_subcategory_name = 'Current';
            } elseif (str_contains($item_name, 'światła') || str_contains($item_name, 'light')) {
                $assigned_subcategory_name = 'Light';
            }
        }
        // Power Supplies
        elseif (str_contains($item_name, 'zasilacz') || str_contains($item_name, 'power supply') || str_contains($item_name, 'zasilania') || str_contains($item_name, 'przetwornica')) {
            $assigned_category_name = 'Power Supplies';
            if (str_contains($item_name, 'din')) {
                $assigned_subcategory_name = 'DIN Rail';
            }
        }
        // Cables & Connectors
        elseif (str_contains($item_name, 'kabel') || str_contains($item_name, 'przewód') || str_contains($item_name, 'cable') || str_contains($item_name, 'złącze') || str_contains($item_name, 'connector')) {
            $assigned_category_name = 'Cables & Connectors';
            if (str_contains($item_name, 'usb')) {
                $assigned_subcategory_name = 'USB Cables';
            } elseif (str_contains($item_name, 'dc')) {
                $assigned_subcategory_name = 'DC Power Cables';
            }
        }
        // Displays
        elseif (str_contains($item_name, 'wyświetlacz') || str_contains($item_name, 'display') || str_contains($item_name, 'oled') || str_contains($item_name, 'lcd')) {
            $assigned_category_name = 'Displays';
        }
        // Antennas
        elseif (str_contains($item_name, 'antena') || str_contains($item_name, 'antenna')) {
            $assigned_category_name = 'Antennas';
        }
        // Enclosures
        elseif (str_contains($item_name, 'obudowa') || str_contains($item_name, 'case')) {
            $assigned_category_name = 'Enclosures';
        }
        // LEDs & Diodes
        elseif (str_contains($item_name, 'dioda') || str_contains($item_name, 'led')) {
            $assigned_category_name = 'LEDs & Diodes';
        }
        // Capacitors
        elseif (str_contains($item_name, 'kondensator') || str_contains($item_name, 'capacitor')) {
            $assigned_category_name = 'Capacitors';
        }
        // Transistors & MOSFETs
        elseif (str_contains($item_name, 'tranzystor') || str_contains($item_name, 'mosfet')) {
            $assigned_category_name = 'Transistors & MOSFETs';
        }
        // Soldering Supplies
        elseif (str_contains($item_name, 'lutowniczy') || str_contains($item_name, 'solder') || str_contains($item_name, 'cyna') || str_contains($item_name, 'topnik')) {
            $assigned_category_name = 'Soldering Supplies';
        }
        // Switches
        elseif (str_contains($item_name, 'przełącznik') || str_contains($item_name, 'switch')) {
            $assigned_category_name = 'Switches';
        }
        // Development Boards
        elseif (str_contains($item_name, 'raspberry pi') || str_contains($item_name, 'esp32') || str_contains($item_name, 'nodemcu') || str_contains($item_name, 'arduino') || str_contains($item_name, 'stm32')) {
            $assigned_category_name = 'Development Boards';
        }

        // Update the item if a category was assigned
        if (!empty($assigned_category_name)) {
            $update_sql = "UPDATE inv_items SET category = ?, subcategory = ? WHERE id = ?";
            $stmt = $pdo->prepare($update_sql);
            $stmt->execute([$assigned_category_name, $assigned_subcategory_name, $item_id]);
            $updated_count++;
            echo "Updated item ID $item_id (\"$item[name]\" ): Category: $assigned_category_name, Subcategory: $assigned_subcategory_name\n";
        } else {
            echo "Skipped item ID $item_id (\"$item[name]\" ): No matching category found.\n";
        }
    }

    echo "\nAssignment complete. Total items updated: $updated_count\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

?>