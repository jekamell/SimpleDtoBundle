# SimpleDtoBundle documentation
## Installation

### Step 1: Download Bundle
Open command terminal, enter the project root and run the following command:
```
composer require jekamell/simple-dto-bundle
```
This command requires you to have [Composer](https://getcomposer.org/) installed [globally](https://getcomposer.org/doc/00-intro.md#globally).

### Step 2: Active Bundle in AppKernel

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Mell\Bundle\SimpleDtoBundle\SimpleDtoBundle(),
        );

        // ...
    }

    // ...
}
```

### Step3: Configure the Bundle
```yml
# app/config/config.yml
simple_dto:
    dto_config_path: "@AppBundle/Resources/config/dto.yml" # provide path to your dto configration file
```
