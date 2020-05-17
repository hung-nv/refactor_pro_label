# ProLabels

## Installation

### Swisssuplabs and Argento customer

Check this article - http://docs.swissuplabs.com/m2/extensions/prolabels/installation/composer/

### Internal Swissuplabs developer

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-pro-labels --prefer-source
bin/magento module:enable Swissup_ProLabels Swissup_Core
bin/magento setup:upgrade
```

# Reindex Labels From Command Line
```bash
cd <magento_root>
bin/magento prolabels:reindex:all
```
