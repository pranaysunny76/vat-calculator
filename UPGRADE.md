# UPGRADE GUIDE 
## Upgrading from V2 to V3
### Minimum Versions
PR: https://github.com/driesvints/vat-calculator/pull/131
All support for PHP 7.2 and below has been dropped as well as support for Laravel 5.8 and below.
### Removed Countries
PR: https://github.com/driesvints/vat-calculator/pull/130
Previously, VatCalculator calculated VAT taxes for GB, NO and TR. Since these countries do not belong to the EU, these have been removed. If you were relying on the previous behavior and want to keep it, you should re-add them to your `config.php` file:
```php
```
### Refactored Validation Rule
PR: https://github.com/driesvints/vat-calculator/pull/133
The internal validation rule for a VAT number has been refactored to a new rule object. You may use it as follows:
```php
```
### Removed Functionality
All functionality for the front-end capabilities, as well as the IP lookup functionality have been removed. Additionally, all shipped translations of the VatNumber validation rule have been removed. No migration path is offered for these so if you rely on these you can either remain on v2 of the package or you can recreate the functionality in your app. 
## Upgrading from v1 to v2
Version 2 of VatCalculator provides a new method to get a more precise VAT rate result. It's recommended to use the new `getTaxRateForLocation` method instead of `getTaxRateForCountry`.
This method expects 3 arguments:
- country code - The country code of the customer.
- postal code - The postal code of the customer.
- company - Flag to indicate if the customer is a company.
