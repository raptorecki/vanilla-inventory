<?php
require_once 'database.php';

$categories = [];
$subcategories = [];

try {
    $stmt = $pdo->query("SELECT name FROM inv_items");
    $item_names = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($item_names as $name) {
        // Convert to lowercase for consistent analysis
        $lower_name = strtolower($name);

        // --- Category Suggestions (broad terms) ---
        if (str_contains($lower_name, 'czujnik') || str_contains($lower_name, 'sensor')) {
            $categories['Sensors'] = true;
            if (str_contains($lower_name, 'temperatury') || str_contains($lower_name, 'temp')) {
                $subcategories['Sensors']['Temperature'] = true;
            } elseif (str_contains($lower_name, 'wilgotności') || str_contains($lower_name, 'humidity')) {
                $subcategories['Sensors']['Humidity'] = true;
            } elseif (str_contains($lower_name, 'ruchu') || str_contains($lower_name, 'pir')) {
                $subcategories['Sensors']['Motion (PIR)'] = true;
            } elseif (str_contains($lower_name, 'magnetyczny') || str_contains($lower_name, 'kontaktron')) {
                $subcategories['Sensors']['Magnetic (Reed Switch)'] = true;
            } elseif (str_contains($lower_name, 'napięcia') || str_contains($lower_name, 'voltage')) {
                $subcategories['Sensors']['Voltage'] = true;
            } elseif (str_contains($lower_name, 'prądu') || str_contains($lower_name, 'current')) {
                $subcategories['Sensors']['Current'] = true;
            } elseif (str_contains($lower_name, 'światła') || str_contains($lower_name, 'light')) {
                $subcategories['Sensors']['Light'] = true;
            }
        } elseif (str_contains($lower_name, 'moduł') || str_contains($lower_name, 'module')) {
            $categories['Modules'] = true;
            if (str_contains($lower_name, 'wifi') || str_contains($lower_name, 'esp')) {
                $subcategories['Modules']['WiFi'] = true;
            } elseif (str_contains($lower_name, 'rs485')) {
                $subcategories['Modules']['RS485'] = true;
            } elseif (str_contains($lower_name, 'przekaźnik') || str_contains($lower_name, 'relay')) {
                $subcategories['Modules']['Relay'] = true;
            } elseif (str_contains($lower_name, 'gps')) {
                $subcategories['Modules']['GPS'] = true;
            } elseif (str_contains($lower_name, 'ładowania') || str_contains($lower_name, 'charger')) {
                $subcategories['Modules']['Charger'] = true;
            }
        } elseif (str_contains($lower_name, 'zasilacz') || str_contains($lower_name, 'power supply') || str_contains($lower_name, 'zasilania')) {
            $categories['Power Supplies'] = true;
            if (str_contains($lower_name, 'din')) {
                $subcategories['Power Supplies']['DIN Rail'] = true;
            } elseif (str_contains($lower_name, 'step-down') || str_contains($lower_name, 'buck')) {
                $subcategories['Power Supplies']['Step-Down (Buck)'] = true;
            } elseif (str_contains($lower_name, 'step-up') || str_contains($lower_name, 'boost')) {
                $subcategories['Power Supplies']['Step-Up (Boost)'] = true;
            }
        } elseif (str_contains($lower_name, 'kabel') || str_contains($lower_name, 'przewód') || str_contains($lower_name, 'cable')) {
            $categories['Cables & Connectors'] = true;
            if (str_contains($lower_name, 'usb')) {
                $subcategories['Cables & Connectors']['USB Cables'] = true;
            } elseif (str_contains($lower_name, 'dc')) {
                $subcategories['Cables & Connectors']['DC Power Cables'] = true;
            } elseif (str_contains($lower_name, 'rj45') || str_contains($lower_name, 'ethernet')) {
                $subcategories['Cables & Connectors']['Ethernet Cables/Adapters'] = true;
            }
        } elseif (str_contains($lower_name, 'wyświetlacz') || str_contains($lower_name, 'display') || str_contains($lower_name, 'oled') || str_contains($lower_name, 'lcd')) {
            $categories['Displays'] = true;
        } elseif (str_contains($lower_name, 'antena') || str_contains($lower_name, 'antenna')) {
            $categories['Antennas'] = true;
        } elseif (str_contains($lower_name, 'obudowa') || str_contains($lower_name, 'case')) {
            $categories['Enclosures'] = true;
        } elseif (str_contains($lower_name, 'dioda') || str_contains($lower_name, 'led')) {
            $categories['LEDs & Diodes'] = true;
        } elseif (str_contains($lower_name, 'kondensator') || str_contains($lower_name, 'capacitor')) {
            $categories['Capacitors'] = true;
        } elseif (str_contains($lower_name, 'tranzystor') || str_contains($lower_name, 'mosfet')) {
            $categories['Transistors & MOSFETs'] = true;
        } elseif (str_contains($lower_name, 'lutowniczy') || str_contains($lower_name, 'solder')) {
            $categories['Soldering Supplies'] = true;
        } elseif (str_contains($lower_name, 'przełącznik') || str_contains($lower_name, 'switch')) {
            $categories['Switches'] = true;
        } elseif (str_contains($lower_name, 'złącze') || str_contains($lower_name, 'connector')) {
            $categories['Cables & Connectors'] = true; // Already covered, but good to reinforce
        } elseif (str_contains($lower_name, 'raspberry pi') || str_contains($lower_name, 'esp32') || str_contains($lower_name, 'nodemcu') || str_contains($lower_name, 'arduino') || str_contains($lower_name, 'stm32')) {
            $categories['Development Boards'] = true;
        }
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

echo "Suggested Categories:\n";
foreach (array_keys($categories) as $cat) {
    echo "- $cat\n";
}

echo "\nSuggested Subcategories:\n";
foreach ($subcategories as $cat => $subs) {
    echo "$cat:\n";
    foreach (array_keys($subs) as $sub) {
        echo "  - $sub\n";
    }
}

?>