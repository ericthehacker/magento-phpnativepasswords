# EW_NativePasswords

Adds support to Magento to optionally use 
[PHP's native password hashing](http://php.net/manual/en/function.password-hash.php) functionality
for customer and admin password hashing and validation.

## Overview

According to [PHP password hashing FAQ](http://php.net/manual/en/faq.passwords.php#faq.passwords.fasthash), the hashing 
algorithms used by Magento (MD5 on Community Edition, SHA256 on Enterprise Edition) are not optimal; it's safer to use instead 
PHP's native password API.

## Requirements

The module will use native PHP API for PHP versions 5.5.0 and greater, and a 
[compatibility library](https://github.com/ircmaxell/password_compat) to add support for versions as old as 5.3.7.
If the system does not meet the PHP requirements, the system configuration option will throw an exception
if it is enabled in system configuration.

## Build Status

- Master: [![Master Build Status](https://api.travis-ci.org/ericthehacker/magento-phpnativepasswords.svg?branch=master)](https://travis-ci.org/ericthehacker/magento-phpnativepasswords)
- Develop: [![Develop Build Status](https://api.travis-ci.org/ericthehacker/magento-phpnativepasswords.svg?branch=develop)](https://travis-ci.org/ericthehacker/magento-phpnativepasswords)
- Code Climate Grade: [![Code Climate Grade](https://codeclimate.com/github/ericthehacker/magento-phpnativepasswords/badges/gpa.svg)](https://codeclimate.com/github/ericthehacker/magento-phpnativepasswords)

## Installation via [modman](https://github.com/colinmollenhour/modman)

```
$ cd <magento root>
$ modman init # if you've never used modman on this Magento instance
$ modman clone https://github.com/ericthehacker/magento-phpnativepasswords.git
```

Be sure to flush your cache after installation!

## Usage

This module's functionality is disabled by default. To use, visit *System -> Configuration -> Customers -> 
Customer Configuration -> Password Options -> Use PHP Native Password Hashing* and set to Yes.

Any new customer or admin accounts will use the native hashing API.

## Configuration Options

- Use PHP Native Password Hashing: This setting fundamentally enables or disables the native password API functionality.
  *Disabled by default*.
- Password Hashing Cost: Password hash cost, in the range [4-31]. See 
  [crypt() documentation](http://php.net/manual/en/function.crypt.php) for more information. *10 by default*.
- Rehash Legacy Passwords: If set to yes, customer passwords hashed using legacy algorithms will be rehashed using 
  PHP native API after successful authentication. For stores migrating to more secure password storage, this
  is essential. NOTE: this does not affect admin password hashes. To improve
  admin password security, admins should change password at the soonest opportunity. 

## Caveat

Any passwords created while the module is enabled will be hashed using the PHP API. If the module is subsequently 
disabled (either by app/etc/modules or system configuration), the passwords will not validate until they are either
reset or the module is reenabled.
  
## FAQ

### Why is your app/etc/modules file named "Ew_NativePasswords" when the module's namespace is "EW_NativePasswords"?

Magento Enterprise edition hijacks the encryption model. In order to further hijack it to use the PHP
native API, the module must be loaded after the Enterprise_Pci module (if it exists).
Due to an implementation detail of Magento module loading, slightly renaming the app/etc/module 
filename accomplishes this. While this is a workaround, it avoid rewriting the core/data helper,
so I found it to be an acceptable trade off, so I will use it until I discover a better way.

See `Mage_Core_Helper_Data::getEncryptor()` and config node `global/helpers/core/encryption_model`. 
