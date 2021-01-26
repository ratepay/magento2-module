# Ratepay GmbH
============================================

|Repository | Ratepay Module for Magento 2
|------|----------
|Author | Robert Müller
|Module-Version | `1.2.3`
|Compatibility | Magento 2.1.0 - 2.4.0
|Link | http://www.ratepay.com
|Mail | integration@ratepay.com
|Documentation | https://ratepay.gitbook.io/magento2/
|Legal Disclaimer|https://ratepay.gitbook.io/docs/#legal-disclaimer

## Installation with Composer
Enter the following commands in your terminal from the root of you shopdirectory.
````bash
composer require ratepay/magento2-payment
php bin/magento module:enable RatePAY_Payment
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
````

## Changelog

### Version 1.2.3 - Released 2021-11-02
* Added mechanism to create a unique sku when sku is duplicate
* Fixed problem with event triggers in cronjobs

### Version 1.2.2 - Released 2020-11-02
* Added True Offline Mode configuration
* Capture will not be sent if no capture was selected
* Changed handling of street array for address parameters
* B2B max order amount will now be considered correctly for b2b orders

### Version 1.2.1 - Released 2020-10-06
* Unified Ratepay legal texts
* Removed privacy policy configuration because it was not used
* Increased the time payment errors are shown
* Page will now scroll to payment error if not in view
* Changed declaration of consent functionality
* Instalment amount will now be preselected if only one runtime is available
* Accountholder can now be selected between name and company from a dropdownbox in b2b mode
* Fixed config scope for backend orders

### Version 1.2.0 - Released 2020-08-12
* Added compatibility for Magento 2.4.0
* Ratepay Payment method- and ProfileID selection now based on billing address
* Added tracking code to confirmation deliver request if present
* Added feature to return previous order price adjustments
* Magento2 REST API calls will now trigger Ratepay API communication
* Enabled the "Online" Capture and Refund workflow in Magento2
* Goodwill refund item type can be configured now
* Added a try-catch block to prevent a order rollback when a database error occures
* Removed vat id validation for B2B orders
* Display company name as account holder for b2b orders with direct-debit
* Added missing creditor id in language files
* Updated legaltexts

### Version 1.1.8 - Released 2019-12-19
* Fixed problem with backend orders and different countries
* Fixed PaymentInformationManagement fix-plugins to only work for the Magento2 version where it's needed
* Refactored request logging to prevent serialization-problems

### Version 1.1.7 - Released 2019-10-30
* Added API log to backend
* Fixed multistore compatibility
* Updated privacy policy links

### Version 1.1.6 - Released 2019-10-16
* Changed folder structure to standard Github Repo structure for Magento2 modules
* Added feature to choose between direct-debit and banktransfer for installment methods
* Added success notification when installment plan was updated in checkout
* Added check and errormessage for partial bundle refunds
* Fixed translations

### Version 1.1.5 - Released 2019-07-31
* Implemented the opportunity to configure backend orders
* Implemented the processing of backend orders

### Version 1.1.4 - Released 2019-07-25
* Added payment method installment
* Added payment method zero percent installments
* Added currency validation to isAvailable mechanic
* Fixed checkout crash problem
* Fixed orders in different currencies
* Fixed creditmemo with only adjustment refund and no items
* Fixed tax calculation for discounts
* Fixed several bugs with B2B mode

### Version 1.1.3 - Released 2019-02-11
* Fix Validator Exception Import

### Version 1.1.2 - Released 2018-05-23
* change path of block class messages
* show profile request error message
* fix bundle article discount problem
* add magento payment available check
* fix invalid template error
* add ratepay terms and conditions
* handle multiple (bundle) items with same sku and qty = 1
* support gift card amounts

### Version 1.1.1 - Released 2018-01-11
* mock phone number if customer phone is missing

### Version 1.1.0.1 - Released 2017-11-30
* change ratepay company address

### Version 1.1.0 - Released 2017-11-08
* add country netherlands
* add country belgium

### Version 1.0.0 - Released 2017-08-22
* add device fingerprint

### Version 0.9.2 - Released 2017-08-07
* add TrxId and Descriptor to payment information in order view
* fix profile request issue
* remove dynamic due date and iban only options

### Version 0.9.1 - Released 2017-08-04
* implement Payment Change Credit on credit memo
* implement Payment Change Return on credit memo
* implement Payment Change Cancellation on cancel
* implement Confirmation Deliver on invoice event

### Version 0.9.0 - Released 2017-07-19
* initial Release

## Nutzungsbedingungen
Bitte beachten Sie die Nutzungsbedingungen unter http://www.ratepay.com/nutzungsbedingungen/

