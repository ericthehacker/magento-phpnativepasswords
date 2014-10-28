# EW_NativePasswords

Adds support to Magento to optionally use 
[PHP's native password hashing](http://php.net/manual/en/function.password-hash.php) functionality.

**WARNING**: This is a work in progress, and although I plan to finish it quickly, it is not currently well tested.

## Overview

According to [PHP password hashing FAQ](http://php.net/manual/en/faq.passwords.php#faq.passwords.fasthash), the hashing 
algorithms used by Magento (MD5 on community, SHA256 on enterprise) are not optimal, it's safer to use instead 
PHP's native password API.

## Requirements

The module will use native PHP API for PHP versions 5.5.0 and greater, and a 
[compatibility library](https://github.com/ircmaxell/password_compat) for versions 5.3.7 and greater.
If the system does not meet the PHP requirements, the system configuration option will throw an exception
if it is enabled in system configuration.

## Installation

### Modman

```
$ cd <web root>
$ modman init # if you've never used modman on this Magento instance
$ modman clone https://github.com/ericthehacker/magento-phpnativepasswords.git
``

Be sure to flush your cache after installation!

## Usage

This module's functionality is disabled by default. To use, visit System -> Configuration -> Customers -> 
Customer Configuration -> Password Options -> Use PHP Native Password Hashing and set to Yes.

Any new customer accounts will use the native hashing API.

## Configuration Options

- Use PHP Native Password Hashing: This setting fundamentally enables or disables the native password API functionality.
  *Disabled by default*.
- Use Backwards-Compatible Hash Verification: This setting allows hash verification using Magento's implementation 
  if the PHP API verification fails. This allows the site to continue to use old password hashes. *Enabled by default*.
- Password Hashing Cost: Password hash cost, in the range [4-31]. See 
  [crypt() documentation](http://php.net/manual/en/function.crypt.php) for more information. *10 by default*.

## Caveats

- Any passwords created while the module is enabled will be hashed using the PHP API. If the module is subsequently 
  disabled (either by app/etc/modules or system configuration), the passwords will not validate until they are either
  reset or the module is reenabled.
