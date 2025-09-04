# Vanilla Storage Tracker

A simple, yet effective, PHP and MySQL-based inventory management system designed to help you keep track of your workshop inventory. This project provides a searchable database of items, allowing for easy management and overview of your stock.

## Features

-   **Inventory Management**: Add, edit, and delete inventory items with detailed information.
-   **Categorization**: Organize items using a flexible category and subcategory system.
-   **Tagging**: Assign multiple tags to items for enhanced searchability and organization.
-   **Rich Text Documentation**: Utilize a rich text editor (Quill.js) to add comprehensive documentation for each item.
-   **Basic Statistics**: Get insights into your inventory with simple statistical overviews.
-   **CSV Import**: Easily import inventory data from CSV files.

## Technologies Used

-   **Backend**: PHP
-   **Database**: MySQL (MariaDB recommended)
-   **Frontend**: HTML, CSS, minimal JavaScript
-   **Rich Text Editor**: Quill.js

## Setup and Installation

Follow these steps to get the project up and running on your local machine:

1.  **Clone the Repository**:
    ```bash
    git clone https://github.com/raptorecki/vanilla-inventory.git
    cd vanilla-inventory
    ```

2.  **Web Server Configuration**: Ensure you have a web server (like Apache or Nginx) with PHP installed and configured to serve the project files.

3.  **Database Setup**:
    *   Create a new MySQL database (e.g., `inventory`).
    *   Import the database schema:
        ```bash
        mysql -u your_username -p inventory < database.sql
        ```
    *   (Optional) Import initial categories:
        ```bash
        mysql -u your_username -p inventory < insert_categories.sql
        ```

4.  **Configure Database Connection**: Open `config.php` and update the database connection details:
    ```php
    <?php
    // config.php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'inventory');
    define('DB_USER', 'your_db_username');
    define('DB_PASS', 'your_db_password'); // DO NOT MODIFY THIS VALUE IN PRODUCTION
    
    // Other configurations...
    ?>
    ```
    **Note**: The `DB_PASS` value should not be modified directly in the `config.php` file if you are using a version-controlled environment where this file is ignored. For local development, you can set your password here.

5.  **Access the Application**: Open your web browser and navigate to the project's root URL (e.g., `http://localhost/vanilla-inventory`).

## Usage

-   **Browse Inventory**: View all your items on the main page.
-   **Add/Edit Items**: Click on items to view details or add new ones. The rich text editor is available for detailed documentation.
-   **Manage Categories/Tags**: Use the dedicated sections to organize your inventory.
-   **Search**: Utilize the search bar to quickly find items by name, category, subcategory, or tags.

## Contributing

Contributions are welcome! If you have suggestions for improvements or bug fixes, please open an issue or submit a pull request on the GitHub repository.

## License

This project is open-source and available under the [MIT License](LICENSE.md). (Note: A `LICENSE.md` file is not currently included in the repository. Please create one if you wish to specify a license.)

## Contact

For any questions or feedback, please open an issue on the GitHub repository.