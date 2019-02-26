# Carbon PHP  7.2+ Tool Kit and Performance Library

CarbonPHP.com 

### Alpha Notice

Carbon PHP is currently in Alpha. There are three important things to remember during this period:

Alpha means that CarbonPHP is in active development and should NOT be considered stable. Using this in a production environment is not recommended. Anyone who attempts this should be capable of altering the code immediately if it breaks. And if you do, please consider submitting those changes on GitHub!
There will be NO DEPRECATION during Alpha. Changed function/class/etc names and definitions will be called out in the Changelog with every release, so please read the Changelog carefully when updating.
Refer to the guide for documentation -- it will be updated with every new release starting in the beta.


## Introduction

CarbonPHP is a lightweight PHP 7.2+ toolkit to simplify the building of custom, dynamic web applications. Its main focus is on making webapps run ridiculously fast, with performance and high-traffic scalability being the absolute highest concern. CarbonPHP has clocked in with impressive statistics, sometimes doubling the traffic that small servers with MySQL-intensive sites can handle.

CarbonPHP's other main focus is portability, allowing your webapps to be installed on servers with different operating systems, Full MySQL ORM REST generator, and php written database tools designed around the mysql dump. CarbonPHP's features are fully supported in windows and macintosh excluding the Windows Websocket Server. Their seems to be no solution for the async input and port scan Select in Windows PHP. I hope to contribute a php library written in C to support this task, however time is a factor.
 Should your development require Windows computers look into Websocketd.com and the file name "./programs/Websocketd.php". Please see the documentation at Carbonphp.com for more information.

## Requirements

CarbonPHP requires PHP 7.2 or later. It makes use of return type object notation, and should not be ported back to earlier PHP versions. 

CarbonPHP will always try to stay upto date with the latest version of PHP. Use of an opcode cache such as XCache is highly recommended, as Carbon is able to run entirely without stat() calls when paired with an opcode cache. Also recommended (but optional) is a RAM-caching engine such as memcached.

## Documentation

All function should have PHPDoc-style documentation in the code, though some are more thorough than others. The best up-to-date c6 guide available is here: carbonphp.com

## CarbonPHP.com

While not required, CarbonPHP highly recommends a strict programmatic flow in development. We recommend that every request uses an MVC structure. The controller must validate all input data and return the variable(s) needed for the model to manipulate. This is a sample from the code library which backbones all of our dynamic (input driven) requests.


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


# Builtin Command Line Interface

Much like laravel's artisan, any file that invokes CarbonPHP from the command line will execute the CLI Interface. I plan to make a system in place for user commands in Beta. See all available commands with:

    php index.php


## Support

Informal support for CarbonPHP is currently offered on https://github.com/RichardTMiles/CarbonPHP.

## Legal

Use of CarbonPHP implies agreement with its software license, available in the LICENSE file. This license is subject to change from release to release, so before upgrading to a new version of C6, please review its license.

## Credits

CarbonPHP was created by Richard Tyler Miles and inspired by Tom Frost.

Contributors can be found in the GitHub Contributor Listing.
