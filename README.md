# RatePAY GmbH
============================================

|Repository | RatePAY Module for Magento 2
|------|----------
|Author | Robert Müller
|Module-Version | `1.1.6`
|Compatibility | Magento 2.1.0 - 2.3.3
|Link | http://www.ratepay.com
|Mail | integration@ratepay.com
|Documentation | https://ratepay.gitbook.io/magento2/

## Installation
### Option 1 - Composer
Enter the following commands in your terminal from the root of you shopdirectory.
````bash
composer require ratepay/magento2-payment
````

### Option 2 - Manual installation
* Copy all files from this GitHub repository into app/code/RatePAY/Payment/ of your shopdirectory.
* Create the folders if not existing.

Enter the following commands in your terminal from the root of you shopdirectory.
````bash
composer require ratepay/php-library
````

## Enable the module
Enter the following commands in your terminal from the root of you shopdirectory.
````bash
php bin/magento module:enable RatePAY_Payment
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
````

## Changelog

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

