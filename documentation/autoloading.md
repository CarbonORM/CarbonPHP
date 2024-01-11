# Autoloading

It is recommended to use composers built in [PSR-4](https://www.php-fig.org/psr/psr-4/) autoloader. 
It is required to load CarbonPHP.  If you need to install CarbonPHP [view the setup guide here](https://carbonorm.dev/#/documentation/CarbonPHP/).

# Using Composer for PSR-4 Autoloading in PHP

Follow these steps to set up Composer for PSR-4 autoloading in your PHP project:

## 1. Install Composer

First, install Composer if you haven't already. Composer is a dependency management tool for PHP. You can download it from [getcomposer.org](https://getcomposer.org/). 

## 2. Create/Locate a `composer.json` File

Create a `composer.json` file in the root directory of your project. This file defines your project's dependencies and autoloading configuration.

## 3. Configure PSR-4 Autoloading

In your `composer.json` file, add an autoload section for PSR-4. Here's an example:

```json
{
    "autoload": {
        "psr-4": {
            "YourNamespace\\": "src/"
        }
    }
}
```

Replace ```YourNamespace\\``` with your namespace prefix and src/ with the directory where your namespaced classes are located.

4. Dump Autoload
Run composer dump-autoload in the terminal. This generates the ```vendor/autoload.php``` file, enabling autoloading.

5. Use Namespaces in Your Classes
Declare your namespace at the top of each PHP class file. For example:

6. Include Composer's Autoloader
In your PHP entry script (like index.php), include Composer's autoloader:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

// Now you can use your classes
$myObject = new \YourNamespace\MyClass();
```

7. Update Autoloading as Needed
Run composer dump-autoload again whenever you add new classes or namespaces to update the configuration.


View a complete [CarbonPHP setup guide here](https://carbonorm.dev/#/documentation/CarbonPHP/).

