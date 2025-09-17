# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [1.0.11] - 2025-09-17
### Fixed
- UI: Changes in inventory table UI design.


## [1.0.10] - 2025-09-17
### Changed
- Refactored "Add New Item" form in `inventory.php` to improve user experience and functionality.
- Replaced text inputs for "Category" and "Subcategory" with dropdown menus.
- Implemented a multi-select dropdown for "Tags".
- Set the default value of "Number Used" to 0.
- Removed "Documentation Link", "Main Image", and "Additional Image" fields.
- Added a "Main Image URL" field for importing images from a URL.
- Created `helpers.php` and centralized the `handleImageUpload` function.
- Updated `item_add.php` to process the new form data and use the centralized image upload function.


## [1.0.8] - 2025-09-07
### Added
- Feature: Added "Development Boards" table to `components.php` (category ID 1, no subcategory limitation).
### Changed
- In `components.php`, refactored table rendering to be more dynamic based on `$category_configs`.
- In `components.php`, "Others" table now displays the same detailed information as "Transistors and MOSFET's".
- In `components.php`, "Edit Details" link now appears for all detailed categories.
- In `components.php`, removed "Subcategory" column from all tables.
- In `components.php`, made folding tables folded by default.
### Fixed
- Bug: Attempted to fix UI bug in `components.php` where table elements went beyond the unfolded box by adding `white-space: normal;`, `word-break: break-word;`, `table-layout: fixed;` to table styles, and `overflow-x: auto;` to `details` element.


## [1.0.7] - 2025-09-07
### Added
- Feature: Created `inv_details` table to store additional information for specific components.
- Feature: Implemented `components_details_edit.php` for editing component-specific details.
- Feature: Added "Edit Details" link to `components.php` for relevant items.
### Changed
- In `components.php` for "Transistors and MOSFET's", replaced "Price", "Source", "Added", and "Modified" columns with the new detailed fields.
### Fixed
- Bug: Corrected PHP parse error in `components.php` due to missing semicolon.


## [1.0.6] - 2025-09-07
### Added
- Feature: Created a new "Components" page to display specific component categories.
- Feature: Added a "Components" link to the main navigation menu.

## [1.0.5] - 2025-09-05
### Fixed
- Bug: Corrected an issue in `item_edit.php` where the Quill editor was not retaining saved content.
- Bug: Resolved a problem in `categories.php` that caused incorrect display of category IDs, including duplicates and omissions.
### Added
- Feature: The main image is now displayed at full size in `item_edit.php`.

## [1.0.3] - 2025-09-04
### Added
- Feature: Implemented main image import functionality in item_edit.php, allowing upload from file or URL.
- Feature: Added image preview and removal options in item_edit.php.
- Feature: Implemented nested directory structure for image storage (images/XX/YY/).
- Feature: Added "ESP8266 Boards Available" and "ESP32 Boards Available" statistics to stats.php.
### Fixed
- UI: Corrected currency display in stats.php to PLN.

## [1.0.2] - 2025-09-04
### Fixed
- UI: Ensured consistent height and alignment for "Add Item" and "Cancel" buttons in forms.
- UI: Arranged form fields in item_edit.php to display "Category", "Subcategory", "Quantity", "Price", "Number Used", and "Source Link" on a single line.
- UI: Ensured uniform spacing between form elements.

## [1.0.1] - 2025-09-04
### Fixed
- Corrected CSS errors in style.css (text_decoration, missing # in color codes).
### Removed
- Unused CSS classes from style.css based on analysis of index.php, inventory.php, categories.php, tags.php, header.php, and footer.php.

## [1.0.0] - 2025-09-04
### Added
- Initial release of the Workshop Inventory web application.