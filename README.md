![PHP Version](https://img.shields.io/packagist/php-v/richardtmiles/carbonphp)
![CarbonPHP Feature Test Suit](https://github.com/RichardTMiles/CarbonPHP/workflows/CarbonPHP%20Feature%20Test%20Suit/badge.svg) 
![Monthly Downloads](https://img.shields.io/packagist/dm/richardtmiles/carbonphp)
![Daily Downloads](https://img.shields.io/packagist/dd/richardtmiles/carbonphp)
![All Downloads](https://img.shields.io/packagist/dt/richardtmiles/carbonphp)
![Star](https://img.shields.io/github/stars/richardtmiles/carbonphp?style=social)


# CarbonPHP Tool Kit and Performance Library

[CarbonPHP.com](http://carbonphp.com/)

CarbonPHP has reached a stable level of trust in its own features through PHPUnit Tests + 
Github Actions but is still in active development. CarbonPHP is being used in production environments. Anyone, or team, who attempts using this code
will find support on Github through issues and forums. We generally expect any issue you many encountered to be minor.
If you edit the codebase, please consider submitting those changes on GitHub! C6 uses [Semantic Versioning 2.0.0](https://semver.org). 
Generally: MAJOR version when you make incompatible API changes, MINOR version when you add functionality in a backwards 
compatible manner, and PATCH version when you make backwards compatible bug fixes. Changes to function/class/etc will be 
called out in the Changelog with every minor release. Please read the changelog or code changes carefully when updating. 
Refer to the guide at [carbonphp.com](https://carbonphp.com)


## Introduction




CarbonPHP is a PHP 7.4+ library to simplify the building of custom, dynamic web applications. Its main focus is to make
webapps run ridiculously fast, with performance and high-traffic scalability being the absolute highest concern. CarbonPHP 
has clocked in with impressive statistics, sometimes doubling the traffic that small servers with MySQL-intensive sites can handle.
C6 works as a standalone backbone for your dev needs or in the corporation with other popular frameworks like [Wordpress](https://developer.wordpress.org)
or [Laravel](https://laravel.com).
CarbonPHP's other goals include portability; allowing your webapps to be installed on servers with different operating 
systems (Windows, Mac, and Linux Support). Full MySQL ORM REST generator, and php written database tools designed around 
the MySQL. Windows PHP currently lacks a library capable of forking. Should your development require Windows 
computers look into [Websocketd.com](Websocketd.com) and the file name "./programs/Websocketd.php". I have written a few 
wiki's in the repo above explaining how to Use sockets in this way. I hope to contribute a php library
written in C (PHP is written in C) to support this task, however time is a factor. Should you feel compelled to help in 
this goal please contact me at Richard@Miles.Systems. Please see the documentation at Carbonphp.com for more information.
For the rest of us who live outside windows Hell C6 has a Websocket Library Class for Standard use and Wordpress use. 

## Quick Start
### Existing projects

    composer install richardtmiles/carbonphp

## Standards 

C6 should follow the [PHP Standards Recommendations](https://www.php-fig.org/psr/) listed below.

[PSR-4](https://www.php-fig.org/psr/psr-4/)

[PSR-12](https://www.php-fig.org/psr/psr-12/)

[SEMVAR](https://semver.org)


## Requirements

CarbonPHP requires PHP 7.4 or later. It makes use of return type object notation, and should not be ported back to earlier PHP versions.
CarbonPHP will always try to stay upto date with the latest version of PHP. 
Use of an opcode cache such as XCache is highly recommended, as Carbon is able to run entirely without stat() 
calls when paired with an opcode cache. Also recommended (but optional) is a RAM-caching engine such as memcached.
PHP8, if not already supported, will receive support WITH C6's FIRST Backwards Compatible Release. Plans to drop 7.4 
are currently scheduled for the summer of 2022. 

## Documentation

All function should have PHPDoc-style documentation in the code. [CarbonPHP.com](https://carbonphp.com/) also has full 
explanations of the codebase. 

### MVC
We recommend that each request use the MVC structure, Pure ORM callback(s), or a combination of the two. 
The controller must validate all input data and return the variable(s) needed for the model to manipulate. 
This is a sample from the code library which backbones all mvc requests.

```php
    /**Stands for Controller -> Model .
     *
     * This will run the controller/$class.$method().
     * If the method returns !empty() the model/$class.$method() will be
     * invoked. If an array is returned from the controller its values
     * will be passed as parameters to our model.
     * @link http://php.net/manual/en/function.call-user-func-array.php
     *
     * @param string $class This class name to autoload
     * @param string $method The method within the provided class
     * @param array $argv Arguments to be passed to method
     * @return mixed the returned value from model/$class.$method() or false | void
     */
    function CM(string $class, string &$method, array &$argv = []): callable
    {
        $class = ucfirst(strtolower($class));   // Prevent malformed class names
        $controller = "Controller\\$class";     // add namespace for autoloader
        $model = "Model\\$class";
        $method = strtolower($method);          // Prevent malformed method names

        // Make sure our class exists
        if (!class_exists($controller)) {
            print "Invalid Controller ({$controller}) Passed to MVC. Please ensure your namespace mappings are correct!";
        }

        if (!class_exists($model)) {
            print "Invalid Model ({$model}) Passed to MVC. Please ensure your namespace mappings are correct!";
        }

        // the array $argv will be passed as arguments to the method requested, see link above
        $exec = function &(string $class, array &$argv) use ($method) {
            $argv = \call_user_func_array([new $class, $method], $argv);
            return $argv;
        };

        return function () use ($exec, $controller, $model, &$argv) {    
            if (!empty($argv = $exec($controller, $argv))) {
                if (\is_array($argv)) {
                    return $exec($model, $argv);        // array passed
                }
                $controller = [&$argv];                 // allow return by reference
                return $exec($model, $controller);
            }
            return $argv;
        };
    }
```

### RESTFUL ORM
CarbonPHP's largest feature is the MySQL ORM. By running a customizable CLI command our program 
will analyze your database schema and generate powerful classes used to manipulate your tables. The auto generated files 
may be used in conjunction for an incredibly pleasing RESTFUL semantics structure. Below are examples for using the REST 
ORM. You can [see the generated source here](https://github.com/RichardTMiles/CarbonPHP/blob/master/src/tables/Carbon_Users.php).

```php
    $id = Users::Post([
            Users::USER_TYPE => 'Athlete',
            Users::USER_IP => '127.0.0.1',
            Users::USER_SPORT => 'GOLF',
            Users::USER_EMAIL_CONFIRMED => 1,
            Users::USER_USERNAME => Config::ADMIN_USERNAME,
            Users::USER_PASSWORD => Config::ADMIN_PASSWORD,
            Users::USER_EMAIL => 'richard@miles.systems',
            Users::USER_FIRST_NAME => 'Richard',
            Users::USER_LAST_NAME => 'Miles',
            Users::USER_GENDER => 'Male'
        ]);
```

Joining across multiple tables. 

```php
    Users::Get($user, $uid, [
            Users::SELECT => [
                Users::USER_USERNAME,
                Carbon_Locations::STATE
            ],
            Users::JOIN => [
                Users::INNER => [
                    Carbon_Location_References::TABLE_NAME => [
                        Users::USER_ID => Carbon_Location_References::ENTITY_REFERENCE
                    ],
                    Carbon_Locations::TABLE_NAME => [
                        Carbon_Locations::ENTITY_ID => Carbon_Location_References::LOCATION_REFERENCE
                    ]
                ]
            ],
            Users::PAGINATION => [
                Users::LIMIT => 1,
                Users::ORDER => [Users::USER_USERNAME => Users::ASC]
            ]
        ]);
```

Using the ORM from the Frontend. This example showcases multiple table joins, as well as the use of aggregate function(s) 
GROUP_CONCAT.

```typescript
    const { axios } = this.props;

    axios.get('/rest/' + C6.carbon_users.TABLE_NAME, {
      params: {
        [C6.SELECT]: [
          C6.carbon_users.USER_USERNAME,
          C6.carbon_users.USER_FIRST_NAME,
          C6.carbon_users.USER_LAST_NAME,
          C6.carbon_users.USER_ID,
          [C6.GROUP_CONCAT, C6.carbon_features.FEATURE_CODE],
          [C6.GROUP_CONCAT, C6.carbon_groups.GROUP_NAME]
        ],
        [C6.JOIN]: {
          [C6.LEFT]: {
            [C6.carbon_user_groups.TABLE_NAME]: [
              C6.carbon_users.USER_ID,
              C6.carbon_user_groups.USER_ID
            ],
            [C6.carbon_groups.TABLE_NAME]: [
              C6.carbon_user_groups.GROUP_ID,
              C6.carbon_groups.ENTITY_ID
            ],
            [C6.carbon_feature_group_references.TABLE_NAME]: [
              C6.carbon_groups.ENTITY_ID,
              C6.carbon_feature_group_references.GROUP_ENTITY_ID
            ],
            [C6.carbon_features.TABLE_NAME]: [
              C6.carbon_features.FEATURE_ENTITY_ID,
              C6.carbon_feature_group_references.FEATURE_ENTITY_ID
            ]
          }
        },
        [C6.PAGINATION]: {
          [C6.LIMIT]: 100
        }
      }
    }).then(response => this.setState({ users: (response.data.rest || []) }));
```


# Builtin Command Line Interface

Much like laravel's artisan, any file that invokes CarbonPHP from the command line will execute the CLI Interface. I plan to make a system in place for user commands in Beta. See all available commands with:

    php index.php help

## Support

Informal support for CarbonPHP is currently offered on https://github.com/RichardTMiles/CarbonPHP.

## Legal

Use of CarbonPHP implies agreement with its software license, available in the LICENSE file. This license is subject to change from release to release, so before upgrading to a new version of C6, please review its license.

## Credits

CarbonPHP was created by Richard Tyler Miles, the BDFL, and inspired by [Tom Frost's](https://github.com/TomFrost) [Hydrogen](https://github.com/TomFrost/Hydrogen).

Contributors can be found in the GitHub Contributor Listing.

## NOTES 
### Common TSX types 
   
    something: PropTypes.arrayOf(PropTypes.node)
    ****classes: PropTypes.object.isRequired,
    message: PropTypes.node.isRequired,

To update the version number on the FED

1) update the header menu

    view/assets/react/src/components/HeaderTop/HeaderLinks.tsx
    components/HeaderTop/HeaderLinks.tsx

2) version number

    variables/carbonphp.jsx
