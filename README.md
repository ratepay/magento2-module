# RatePAY GmbH
============================================

|Repository | RatePAY Module for Magento 2
|------|----------
|Author | Sebastian Neumann
|Version | `1.0.0`
|Link | http://www.ratepay.com
|Mail | integration@ratepay.com

## Installation
Copy all files into app/code/ of you shopdirectory.
Afterwords you follow one of the following options.
### Option 1 (terminal):
Enter the following commands in your terminal from the root of you shopdirectory.
````bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
````

### Option 2 (Web Setup Wizard):
Navigate to the component manager in adminarea of the Shop. Choose the RatePAY Payment Module and activate it.
For further information and prerequisites depending on module installation from adminarea please follow the official Magento 2 documentation : http://devdocs.magento.com/guides/v2.1/comp-mgr/module-man/compman-checklist.html

## Changelog

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

