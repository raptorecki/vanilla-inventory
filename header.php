<?php
session_start(); // Start the session to handle flash messages

require_once __DIR__ . '/helpers/error_logger.php';
require_once 'database.php';
require_once 'helpers.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vanilla Inventory</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<pre class="logo">
 ___      ___  __      _____  ___    __    ___      ___            __            __    _____  ___  ___      ___  _______  _____  ___  ___________  ______     _______   ___  ___ 
|"  \    /"  |/""\    (\"   \|"  \  |" \  |"  |    |"  |          /""\          |" \  (\"   \|"  \|"  \    /"  |/"     "|(\"   \|"  \("     _   ")/""""""\   /""""""\ |"  \/"  |
 \   \  //  //    \   |.\   \    | ||  | ||  |    ||  |         /    \         ||  | |.\   \    |\   \  //  /(: ______)|.\   \    |)__/  \\__/// ____  \ |:        | \   \  /
  \\  \/. .//' /\  \  |: \.   \\  | |:  | |:  |    |:  |        /' /\  \        |:  | |: \.   \\  | \\  \/. ./  \/    |  |: \.   \\  |   \\_ /  /  /    ) :)|_____/   )  \\  \/ 
   \.    ////  __'  \ |.  \    \. | |.  |  \  |___  \  |___    //  __'  \       |.  | |.  \    \. |  \.    //   // ___)_ |.  \    \. |   |.  | (: (____/ //  //      /   /   /   
    \\   //   /  \\  \|    \    \ | /\  |\( \_|:  \( \_|:  \  /   /  \\  \      /\  |\|    \    \ |   \\   /   (:      "||"    \    \ |   \:  |  \        /  |:  __   \  /   /
     \__/(___/    \___)\___|\____\)(__\|_)\_______)\_______)(___/    \___)    (__\|_)\___|\____\)    \__/     \_______) \___|\____\)    \__|   \"_____/   |__|  \___)|___/
</pre>

<nav>
    <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a>
    <a href="inventory.php" class="<?= $current_page == 'inventory.php' ? 'active' : '' ?>">Inventory</a>
    <a href="categories.php" class="<?= $current_page == 'categories.php' ? 'active' : '' ?>">Categories</a>
    <a href="tags.php" class="<?= $current_page == 'tags.php' ? 'active' : '' ?>">Tags</a>
</nav>
<div class="container">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?= htmlspecialchars($_SESSION['flash_message']['type']) ?>">
            <?= htmlspecialchars($_SESSION['flash_message']['text']) ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
