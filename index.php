<?php
require 'header.php';
?>
<!-- The main content for index.php starts here, header.php has already been included -->
<h1>Welcome to Vanilla Inventory</h1>

<p style="text-align: center; font-size: 1.2em; margin-bottom: 40px;">
    Your central hub for managing and searching your workshop inventory.
</p>

<div class="search-forms-container">
    <div class="search-form-wrapper">
        <h2>Search Inventory</h2>
        <p>Find a specific item by its name, category, tags, and more.</p>
        <form method="GET" action="inventory.php" class="search-container">
            <input type="text" name="search" placeholder="Search for an item...">
            <button type="submit">Search Inventory</button>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>