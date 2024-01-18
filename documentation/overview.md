# Setup Overview

Historically, CarbonPHP specifically started as a simple Controller -> Model -> View framework. With my first solo 
application attempt I found the model layer increasingly verbose with repetitions difficult to abstract in a way which 
stayed readable. I analysed what it would take to simplify the logic for PDO operations in a semantically pleasing and 
programmatically safe way. As this dream evolved into a full REST MySQL ORM it was necessary to include functional hooks
that could modify or validate queries further than what an automated system could possibly provide. The art of generating
code to be tracked in a public repository, especially one with multiple team members, is a subtle beauty. Through many 
generations the models added to your code are generally considered final with no need for code changes or feature 
degradation with iterations of CarbonPHP and its inner workings.

1) Create/Edit your database schema and tables with all columns and relations you would like
2) Add CarbonPHP to your project.
    - Install with Composer using `composer require carbonphp/carbonphp` in your terminal.
    - Add the following to your `composer.json` file.
        ```json
        {
          "require": {
            "carbonphp/carbonphp": "19.*"
          },
          "autoload": {
            "psr-4": {
              "Tables\\": "Tables/"
            }
          }
        }
        ```
3) Create an entry point invoke to your CarbonPHP instance such as the `index.php`. Below is a minimal example ```mvp.php```. Note: CarbonPHP is non-invasive and will not interfere with your existing code. It will not exit assuming the setup was successful. If you run the code using `php -S 0.0.0.0:8000 mvp.php` you will see the output `Hello World!` in your browser.
   ```php
   <?php
   
   use CarbonPHP\Abstracts\Composer;
   use CarbonPHP\Application;
   use CarbonPHP\CarbonPHP;
   use CarbonPHP\Interfaces\iConfig;
   use CarbonPHP\Programs\CLI;
   use CarbonPHP\Rest;
   use CarbonPHP\Tables\Carbons;
   
   // Composer autoload
   if (false === ($loader = include $autoloadFile = 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
   
       print "<h1>Failed loading Composer at ($autoloadFile). Please run <b>composer install</b>.</h1>";
   
       die(1);
   
   }
   
   Composer::$loader = $loader;
   
   (new CarbonPHP(new class extends Application implements iConfig
   {
   
       public function defaultRoute(): void
       {
   
       }
   
       /**
        *
        * this should always be public and not static
        *
        * @param string $uri
        * @return bool
        */
       public function startApplication(string $uri): bool
       {
           if (Rest::MatchRestfulRequests('', Carbons::CLASS_NAMESPACE)) {
               return true;
           }
   
           return false;
       }
   
       public static function configuration(): array
       {
           return [
               CarbonPHP::REST => [
                   CarbonPHP::NAMESPACE => 'Tables\\',
                   CarbonPHP::TABLE_PREFIX => Carbons::TABLE_PREFIX
               ],
               CarbonPHP::DATABASE => [
                   CarbonPHP::REBUILD_WITH_CARBON_TABLES => true,
                   CarbonPHP::DB_HOST => '127.0.0.1',
                   CarbonPHP::DB_PORT => '3306',
                   CarbonPHP::DB_NAME => 'CarbonPHPExamples',                       // Schema
                   CarbonPHP::DB_USER => 'root',
                   CarbonPHP::DB_PASS => 'password',
                   CarbonPHP::REBUILD => false
               ],
               CarbonPHP::SITE => [
                   CarbonPHP::PROGRAM_DIRECTORIES => [
                       CLI::class
                   ],
                   CarbonPHP::CACHE_CONTROL => [
                       'ico|pdf|flv' => 'Cache-Control: max-age=29030400, public',
                       'jpg|jpeg|png|gif|swf|xml|txt|css|woff2|tff|ttf|svg' => 'Cache-Control: max-age=604800, public',
                       'html|htm|hbs|js' => 'Cache-Control: max-age=0, private, public',   // It is not recommended to add php as an extension as explicitly hitting the .php would output its contents without compilation.
                       // This can be a valid use, but for 99% of users it will seem like a bug with apache.
                   ],
                   CarbonPHP::CONFIG => __FILE__,               // Send to sockets
                   CarbonPHP::TIMEZONE => 'America/Phoenix',    //  Current timezone
                   CarbonPHP::TITLE => 'CarbonPHP • C6',        // Website title
                   CarbonPHP::VERSION => '0.0.0',               // Add link to semantic versioning
                   CarbonPHP::SEND_EMAIL => 'richard@miles.systems',
                   CarbonPHP::REPLY_EMAIL => 'richard@miles.systems',
                   CarbonPHP::HTTP => true, //CarbonPHP::$app_local
               ],
               // ERRORS on point
               CarbonPHP::ERROR => [
                   CarbonPHP::LOCATION => CarbonPHP::$app_root . 'logs' . DIRECTORY_SEPARATOR,
                   CarbonPHP::LEVEL => E_ALL | E_STRICT,  // php ini level
                   CarbonPHP::STORE => false,      // Database if specified and / or File 'LOCATION' in your system
                   CarbonPHP::SHOW => true,       // Show errors on browser
                   CarbonPHP::FULL => true        // Generate custom stacktrace will high detail - DO NOT set to TRUE in PRODUCTION
               ]
           ];
   
       }
   }, __DIR__ . DIRECTORY_SEPARATOR))();
   
   
   print 'Hello World!';
   
   ```
4) Generate you PHP bindings.
   ```bash
   php mvp.php restbuilder -prefix carbon_ -dontQueryWithDatabaseName -excludeTablesRegex '#_mig_.*#' -json -namespace 'Tables' -target tables/
   ```
5) Profit. You can now use the generated code to interact with your database. Below is a simple example of a GET request. To see how this code was setup, please see the [full example repo](https://github.com/RichardTMiles/CarbonPHPExamples).
   ```php
   <?php

   use CarbonPHP\Abstracts\Composer;
   use CarbonPHP\CarbonPHP;
   use CarbonPHP\Interfaces\iRest;
   use Examples\Sample;
   use Examples\Tables\Users;
   
   // Composer autoload
   if (false === ($loader = include $autoloadFile = 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
   
       print "<h1>Failed loading Composer at ($autoloadFile). Please run <b>composer install</b>.</h1>";
   
       die(1);
   
   }
   
   Composer::$loader = $loader;
   
   new CarbonPHP(Sample::class, __DIR__ . DIRECTORY_SEPARATOR);
   
   $randomUserName = 'example_username' . random_int(0, 1000000);
   
   $post = [
       Users::USER_USERNAME => $randomUserName,
       Users::USER_PASSWORD => $randomUserName,
       Users::USER_SPORT => 'golf',
       Users::USER_FIRST_NAME => 'Richard',
       Users::USER_LAST_NAME => 'Miles',
       Users::USER_PROFILE_PIC => 'user_profile_pic',
       Users::USER_PROFILE_URI => $randomUserName,
       Users::USER_COVER_PHOTO => 'user_cover_photo',
       Users::USER_GENDER => 'user_gender',
       Users::USER_ABOUT_ME => 'user_about_me',
       Users::USER_RANK => 'user_rank',
       Users::USER_EMAIL => 'user_email',
       Users::USER_EMAIL_CODE => 'user_email_code',
       Users::USER_EMAIL_CONFIRMED => 'user_email_confirmed',
       Users::USER_GENERATED_STRING => 'user_generated_string',
       Users::USER_MEMBERSHIP => 'user_membership',
       Users::USER_DEACTIVATED => 'user_deactivated',
       Users::USER_IP => '0.0.0.0',
       Users::USER_EDUCATION_HISTORY => 'user_education_history',
   ];
   
   
   // dont be shy, drop into post, I wrote it
   if (false === Users::post($post)) {
   
       throw new Exception('Failed to create a new user.');
   
   }
   
   $results = [];
   
   if (false === Users::get($results, null, [
       iRest::SELECT => [
       Users::USER_USERNAME
   ],
       iRest::PAGINATION => [
       iRest::LIMIT => 100,
   ]
   ])) {
   
       throw new Exception('Failed to get users');
   
   }
   
   $results = json_encode($results, JSON_PRETTY_PRINT);
   
   $json = json_encode($GLOBALS['json'], JSON_PRETTY_PRINT);
   
   print <<<HTML
   <html lang="en">
   <head>
       <title>CarbonPHP Example - PHP Querying</title>
   </head>
   <body>
       <h1>Successfully created a new user ($randomUserName).</h1>
       <pre>
           $results
       </pre>
       <h2>global \$json;</h2>
       <pre>
           $json
       </pre>
       <script>console.log('$json')</script>
   </body>
   </html>
   HTML;

   ```
