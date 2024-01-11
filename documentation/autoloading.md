# Autoloading

It is recommended to use composers built in [PSR-4](https://www.php-fig.org/psr/psr-4/) autoloader. 
It is required to load CarbonPHP.  If you need to install CarbonPHP [view the setup guide here](https://carbonorm.dev/#/documentation/CarbonPHP/).


1) ***Using PSR-4**, which stands for PHP Standard Recommendation 4, offers several benefits for modern PHP development. PSR-4 is a standard for autoloading classes from file paths, and it is widely adopted in the PHP community. Here are some of the key benefits:

2) **Streamlined Autoloading:** PSR-4 provides a consistent and straightforward way to autoload classes. This eliminates the need for manual require or include statements for each class file, simplifying the code and reducing the risk of errors.

3) **Namespacing Support:** PSR-4 is built with namespacing in mind, which is crucial for organizing large applications. Namespaces prevent class name conflicts between different libraries or modules within your application, akin to having separate directories for files.

4) **Improved Directory Structure:** PSR-4 encourages a cleaner and more intuitive directory structure. The namespace of a class directly maps to the file path, making it easier to locate the corresponding file for a given class.

5) **Efficiency in Large-Scale Applications:** In large applications with many classes, PSR-4 autoloading can be more efficient than PSR-0 (its predecessor) because it avoids unnecessary directory scans.

6) **Enhanced Collaboration and Maintenance:** By adhering to a widely-accepted standard, PSR-4 makes it easier for other developers to understand and contribute to your project. This consistency is especially beneficial in open-source projects or team environments.

7) **Integration with Modern Tools:** Many modern PHP frameworks and tools, like Composer, support PSR-4. This compatibility makes it easier to integrate third-party libraries and manage dependencies in your projects.

8) **Scalability:** PSR-4 allows for scalable project structures. As your application grows, you can easily add new namespaces and directory structures without impacting existing code.

9) **Ease of Refactoring:** Refactoring becomes simpler with PSR-4. For instance, if you need to rename a namespace or move classes around, the changes in the filesystem are directly mirrored in the namespace structure, reducing the effort required for such modifications.

In summary, PSR-4 autoloading standardizes and simplifies class loading in PHP. It promotes a better organizational structure, makes code easier to maintain and collaborate on, and integrates seamlessly with other PHP tools and frameworks, enhancing the overall development experience.


# Using Composer for PSR-4 Autoloading in PHP

Follow these steps to set up Composer for PSR-4 autoloading in your PHP project:

1) Install Composer
First, install Composer if you haven't already. Composer is a dependency management tool for PHP. You can download it from [getcomposer.org](https://getcomposer.org/). 

2) Create/Locate a `composer.json` File

Create a `composer.json` file in the root directory of your project. This file defines your project's dependencies and autoloading configuration.

3) Configure PSR-4 Autoloading

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

4) Dump Autoload
Run composer dump-autoload in the terminal. This generates the ```vendor/autoload.php``` file, enabling autoloading.

5) Use Namespaces in Your Classes
Declare your namespace at the top of each PHP class file. For example:

6) Include Composer's Autoloader
In your PHP entry script (like index.php), include Composer's autoloader:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

// Now you can use your classes
$myObject = new \YourNamespace\MyClass();
```

7) Update Autoloading as Needed
Run composer dump-autoload again whenever you add new classes or namespaces to update the configuration.


View a complete [CarbonPHP setup guide here](https://carbonorm.dev/#/documentation/CarbonPHP/).

