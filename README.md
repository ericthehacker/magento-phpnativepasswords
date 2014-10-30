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

## Installation

### Modman

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

## Caveat

Any passwords created while the module is enabled will be hashed using the PHP API. If the module is subsequently 
disabled (either by app/etc/modules or system configuration), the passwords will not validate until they are either
reset or the module is reenabled.
  
## FAQ

### Why do I get the error *Module "EW_NativePasswords" requires module "Enterprise_Pci".*? I really want to use this module on my Community Edition store!

Magento Enterprise edition hijacks the encryption model. In order to further hijack it to use the PHP
native API, the module must depend on the Enterprise_Pci module *when used on Enterprise Edition*.
Until I discover a way to resolve this conflict, you will have to modify the app/etc/modules/EW_NativePasswords.xml
file to remove the Enterprise dependency, like so:

```
<?xml version="1.0"?>
<config>
    <modules>
        <EW_NativePasswords>
            <active>true</active>
            <codePool>community</codePool>
            <depends>
                <Mage_Adminhtml/>
                <Mage_Admin/>
                <Mage_Customer/>
            </depends>
        </EW_NativePasswords>
    </modules>
</config>
```
