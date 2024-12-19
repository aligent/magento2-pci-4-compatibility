# Magento 2 PCI DSS 4.0 Compatibility

A Magento 2 module to bring it in-line with the [PCI DSS 4.0 requirements](https://east.pcisecuritystandards.org/document_library?category=pcidss&document=pci_dss), with changes including:
* Automatic disabling of admin accounts with 90 days of inactivity.
   * Functionality added via new cron job that runs once per day.
* Restriction of admin session timeout to be no more than 15 minutes.
* Restriction of admin lockout functionality:
   * No more than 10 attempts before lockout.
   * Lockout duration of no less than 30 minutes.
* Enforce than admin passwords must contain at least 12 characters.

## Installation
```shell
composer require aligent/magento2-pci-4-compatibility
bin/magento module:enable Aligent_Pci4Compatibility
bin/magento setup:upgrade

```
