<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/9/19
 * Time: 8:38 PM
 */

namespace CarbonPHP\Programs;

use MatthiasMullie\Minify as Run;
use CarbonPHP\interfaces\iCommand;
use Patchwork\JSqueeze;

class Minify implements iCommand
{
    private $PHP;

    public function __construct($PHP)
    {
        $this->PHP = $PHP;
    }

    public function usage() : void
    {
        //TODO - adjust for app view
        print <<<USE

                The PHP configuration array passed to CarbonPHP must have an array attribute named "MINIFY".
                
                \$PHP['MINIFY'] = [
                
                    'CSS' => [
                        
                        'pathToCSS1.css',
                        'pathToCSS2.css',
                        'pathToCSS3.css',
                        ...
                    
                    ],
                    
                    'JS' => [
                    
                        'pathToJS1.js',
                        'pathToJS2.js',
                        'pathToJS3.js',
                        ...
                    ],
               
                ];

                
                Run as a CarbonPHP iCommand with the following :: 
                
                >> php index.php minify             -- This will minify both JS and CSS
                >> php index.php minify css         -- Only minify the css
                >> php index.php minify js          -- Only minify the js


                The output path(s) will be :: 
                
                    APP_ROOT . 'view/carbon.css'
                    APP_ROOT . 'view/carbon.js'

                
                
USE;
        exit(1);
    }

    private function CSS(array $files): void
    {
        if (empty($files)) {
            $this->usage();
        }
        $minifiedPath = $files['OUT'] ?? APP_ROOT . APP_VIEW . 'CSS/style.css';

        unset($files['OUT']);

        $files = array_values($files);

        foreach ($files as $file) {
            if (!file_exists($file)){
                print "\tFailed to find\n\t\t$file\n\n\n";
                exit(1);
            }
        }

        if (file_exists($minifiedPath)) {
            unlink($minifiedPath);
        }

        $min = new Run\CSS(... $files);
        $min->minify($minifiedPath);
        print "\tThe minified cascading style sheet (css) was stored to ::\n\n\t\t\t $minifiedPath\n\n";
    }

    private function JS(array $files): void
    {
        if (empty($files)) {
            $this->usage();
        }
        $minifiedPath = $files['OUT'] ?? APP_ROOT . APP_VIEW . 'js/javascript.js';

        unset($files['OUT']);

        foreach ($files as $file) {
            if (!file_exists($file)){
                print "\tFailed to find\n\t\t$file\n\n\n";
                exit(1);
            }
        }

        if (file_exists($minifiedPath)) {
            unlink($minifiedPath);
        }

        $buffer = '';

        $jz = new JSqueeze;

        foreach ($files as $file) {
            $buffer .= PHP_EOL . $jz->squeeze(
                    file_get_contents($file),
                true,   // $singleLine
                true,   // $keepImportantComments
                false   // $specialVarRx
            );
        }

        if (!file_put_contents($minifiedPath, $buffer)) {
            print "Failed to save the minified javascript!!\n\n\n";
        }

        /*
        $min = new Run\CSS(... $files);
        $min->minify($minifiedPath);
        */
        print "\tThe minified javascript (js) was stored to ::\n\n\t\t\t $minifiedPath\n\n";
    }


    /**
     * @param $argv
     */
    public function run($argv) : void
    {
        switch ($argv) {
            case 'css':
                $this->CSS($this->PHP['MINIFY']['CSS']);
                break;
            case 'js':
                $this->JS($this->PHP['MINIFY']['JS']);
                break;
            default:
                $this->JS($this->PHP['MINIFY']['JS']);
                $this->CSS($this->PHP['MINIFY']['CSS']);
        }
    }

    public function cleanUp($argv) : void
    {
        
    }
}