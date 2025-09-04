# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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