# Ratepay GmbH
============================================

|Repository | Ratepay Module for Magento 2
|------|----------
|Author | Robert Müller
|Module-Version | `2.1.10`
|Compatibility | Magento 2.3.0 - 2.4.x
|Link | http://www.ratepay.com
|Mail | integration@ratepay.com
|Full Documentation | [click here](https://docs.ratepay.com/docs/developer/shop_modules/magento/magento_2/ratepay_payment_plugin_for_magento_2/)|
|Legal Disclaimer   | [click here](https://docs.ratepay.com/docs/developer/shop_modules/overview/)| 

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

### Version 2.1.10 - Released 2023-11-10
* Changed DeviceFingerprint handling

### Version 2.1.9 - Released 2023-09-28
* Fixed profile usage in backend order

### Version 2.1.8 - Released 2023-05-09
* Added compatibility for Magento 2.4.6 and PHP 8.2
* Added prevention mechanism for a Mage2 core vulnerability APSB22-12
* Added B2B check to frontend checkout
* Increased error message display time

### Version 2.1.7 - Released 2023-01-18
* Updated Ratepay GmbH address
* Added offline instalment calculator to product details page

### Version 2.1.6 - Released 2022-09-07
* Fixed error message when entered IBAN was invalid
* Fixed sandbox mode problem for capture-, refund- and cancel-mechanisms with certain configurations
* Added copyright header to all files

### Version 2.1.5 - Released 2022-08-22
* Added proxy mode for IP determination when shop server is behind a proxy
* Visual adjustments to instalment calculator

### Version 2.1.4 - Released 2022-08-03
* Fixed problems with orders in pre 2.0.0 format after update
* Fixed display problem with instalment calculator in some browser zoom levels
* Fixed a problem with bundle products in connection with 0% tax

### Version 2.1.3 - Released 2022-07-18
* Added mechanism to determine shipping vat through m2 core models
* Removed unfunctional payment fee config options
* Changed german translation for instalment payment methods
* Fixed problem in db_schema.xml
* Changed creditor info in payment templates

### Version 2.1.2 - Released 2022-05-04
* Refactored some things for Magento Marketplace

### Version 2.1.0 - Released 2022-04-28
* Added compatibility to Magento 2.4.4 and PHP 8.1
* Added support for the Magento multi shipping feature
* Fixed availability check for Ratepay profile id concerning the shipping country
* Fixed missing error message when invalid IBAN was entered
* Fixed a problem with a checkout layout attribute

### Version 2.0.0 - Released 2022-02-01
* The profile configuration has been refactored completely ( there will be a migration when you are updating from a pre 2.0.0 version )
* 0% instalments now support multiple runtimes
* Bugfix regarding error messages
* Visual adjustments to instalment calculator

### Version 1.2.10 - Released 2021-12-15
* Removed DeviceFingerPrint scripts from backend orders

### Version 1.2.9 - Released 2021-12-06
* Fixed scope context in discount tax detection
* Fixed validation problems for backend orders
* Added missing shipping description to confirmation deliver call

### Version 1.2.8 - Released 2021-11-18
* Fixed transmission of Tracking Id when added directly in invoice
* Added mechanism to register invoice increment id earlier in the process to transmit it in capture request

### Version 1.2.7 - Released 2021-11-02
* Added CSP whitelist file
* Fixed wrong config scope in certain situations when reading config data
* Made Ratepay links in backend error boxes clickable

### Version 1.2.6 - Released 2021-07-21
* Added config option for street line usage
* Added an info box to the frontend when order will be created in sandbox mode and to the backend when sandbox mode was used for selected order.
* Fixed a problem in Magento 2.4.0+ with validation on payment selection on frontend

### Version 1.2.5 - Released 2021-04-19
* Refactored capture-, refund- and cancel-mechanisms to use the mage-core way through the payment model instead of custom event triggers
* Added possibility to transfer multiple tracking codes to Ratepay
* Added possibility to use the tracking code form inside of the create invoice form to transfer tracking codes to Ratepay  
IMPORTANT: Most of the events.xml files have been deleted in the refactoring of the payment events. When updating the module manually you
should delete the old module files and copy the new version in freshly to not have the old event triggers in the system to interfere with the new mechanism.
If the module is installed with Composer the update process should handle this correctly by itself.

### Version 1.2.4 - Released 2021-02-12
* Fixed a problem with config scope not being used correctly in certain situations

### Version 1.2.3 - Released 2021-01-26
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

