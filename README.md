# Magento 2 PCI DSS 4.0 Compatibility

A Magento 2 module to bring the use of admin accounts in-line with the [PCI DSS 4.0 requirements](https://east.pcisecuritystandards.org/document_library?category=pcidss&document=pci_dss), with changes covering the following requirements:
* 8.2.6
   * Inactive user accounts are removed or disabled within 90 days of inactivity
* 8.2.8
   * If a user session has been idle for more than 15 minutes, the user is required to re-authenticate to re-activate the terminal or session.
* 8.3.4
   * Invalid authentication attempts are limited by:
      * Locking out the user ID after not more than 10 attempts.
      * Setting the lockout duration to a minimum of 30 minutes or until the user’s identity is confirmed.
* 8.3.6
   * If passwords/passphrases are used as authentication factors to meet Requirement 8.3.1, they meet the following minimum level of complexity:
      * A minimum length of 12 characters (or IF the system does not support 12 characters, a minimum length of eight characters).
      * Contain both numeric and alphabetic characters

The changes invovled for each requirement are as follows:
* 8.2.6
   * A new cron job (scheduled once per day) will automatically make any account that has not logged in for 90 days inactive
* 8.2.8
   * The configuration setting in admin for idle timeout has been modified to only accept values less than or equal to 900 seconds (15 minutes).
* 8.3.4
   * The configuration setting in admin for the number of incorrect login attempts before an account is locked has been modified to only accept values less than or equal to 10.
   * The configuration setting in admin for the time an account is locked for has been modified to only accept values greater than or equal to 30.
* 8.3.6
   * The minimum number of characters a password must have has been increased from 7 to 12.

## Installation
```shell
composer require aligent/magento2-pci-4-compatibility
bin/magento module:enable Aligent_Pci4Compatibility
bin/magento setup:upgrade

```
