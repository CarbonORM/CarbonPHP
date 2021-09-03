<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/7/19
 * Time: 1:58 AM
 */

namespace Tests\Browser;

use PHPUnit\Extensions\Selenium2TestCase;
use Tests\Config;

/** Selenium2TestCase
 * It should be considered that chrome is constantly updating and so is chromedriver
 * brew install chromedriver
 * composer dependencies will need to be manually updated.
 * @link https://github.com/giorgiosironi/phpunit-selenium/blob/master/Tests/Selenium2TestCaseTest.php
 * @link http://apigen.juzna.cz/doc/sebastianbergmann/phpunit-selenium/class-PHPUnit_Extensions_Selenium2TestCase.html
 */
class Selenium2Test extends Selenium2TestCase
{

    private static string $ROOT_DIRECTORY;

    private static function startSeleniumServer(): void
    {
        static $run = false;

        if ($run) {

            return;

        }

        $run = true;

        // java -Dwebdriver.chrome.driver =  -jar selenium-server.jar
        $chromeDriver = self::$ROOT_DIRECTORY . 'bin' . DS . 'chromedriver';
        $selenium = self::$ROOT_DIRECTORY . 'vendor' . DS . 'bin' . DS . 'selenium-server-standalone' . DS . 'bin' . DS . 'selenium-server-standalone.jar';
        // print $selenium . PHP_EOL;die;
        print "\nThe following code should be running in another terminal\n\t";
        print ("\n\njava -Dwebdriver.chrome.driver=$chromeDriver -jar $selenium &");

        print <<<LINKS

// fml
brew upgrade --cask chromedriver



// win

curl -o chromedriver_mac64.zip https://chromedriver.storage.googleapis.com/` curl https://chromedriver.storage.googleapis.com/LATEST_RELEASE `/chromedriver_mac64.zip
unzip chromedriver_mac64.zip
rm -f chromedriver_mac64.zip

Use the command above to download the correct cd

[ https://chromedriver.chromium.org/chromedriver-canary ]

[ https://chromedriver.storage.googleapis.com/index.html?path=93.0.4577.15/ ]

[ https://chromedriver.storage.googleapis.com/92.0.4515.107/chromedriver_mac64.zip ]

Linux (64-bit): https://commondatastorage.googleapis.com/chromium-browser-snapshots/index.html?prefix=Linux_x64/

Mac OS X (64-bit): https://commondatastorage.googleapis.com/chromium-browser-snapshots/index.html?prefix=Mac/

Windows (32-bit): https://commondatastorage.googleapis.com/chromium-browser-snapshots/index.html?prefix=Win/
LINKS;

    }


    public function setUp(): void
    {

        if (!defined('DS')) {

            define('DS', DIRECTORY_SEPARATOR);

        }

        self::$ROOT_DIRECTORY = dirname(__DIR__) . DS;

        //self::startSeleniumServer();

        // self::shareSession(true);

        $this->setDesiredCapabilities([
            'chromeOptions' => [
                'w3c' => false
            ]
        ]);

        $this->setHost('localhost');
        $this->setPort(4444);
        $this->setBrowser('chrome');
        $this->setBrowserUrl('http://127.0.0.1:8080/');
        $this->prepareSession()->currentWindow()->maximize();
        $this->setSeleniumServerRequestsTimeout(10);

    }

    public function testSetupNavigationAndTitle(): void
    {
        $this->url('/');

        self::assertEquals(1, version_compare(Selenium2TestCase::VERSION, '9.0.0'));

        $string = $this->title();

        self::assertEquals('', $string);
    }

}
