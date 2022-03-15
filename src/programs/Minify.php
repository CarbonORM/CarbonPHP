<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/9/19
 * Time: 8:38 PM
 */

namespace CarbonPHP\Programs;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;
use MatthiasMullie\Minify as Run;
use Patchwork\JSqueeze;

class Minify implements iCommand
{
    use Composer;

    private array $PHP;

    public function __construct(array $PHP)
    {
        [$this->PHP] = $PHP;
    }

    public function usage(): void
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
                
                    CarbonPHP::\$app_root . 'view/carbon.css'
                    CarbonPHP::\$app_root . 'view/carbon.js'

                
                
USE;
        exit(1);
    }

    private function CSS(): callable
    {
        return function (array $files) {

            if (empty($files)) {

                $this->usage();

            }

            $minifiedPath = $files['OUT'] ?? CarbonPHP::$app_root . CarbonPHP::$app_view . 'CSS' . DS . 'style.css';

            unset($files['OUT']);

            $files = array_values($files);

            foreach ($files as $file) {
                if (!file_exists($file)) {
                    ColorCode::colorCode("\tFailed to find\n\t\t$file\n\n\n", iColorCode::RED);
                    exit(1);
                }
            }

            if (file_exists($minifiedPath)) {

                unlink($minifiedPath);

            }

            $min = new Run\CSS(... $files);

            $min->minify($minifiedPath);

            ColorCode::colorCode("\tThe minified cascading style sheet (css) was stored to ::\n\n\t\t\t $minifiedPath\n\n", iColorCode::CYAN);

        };
    }

    private function JS(): callable
    {
        return function (array $files) {
            if (empty($files)) {
                $this->usage();
            }
            $minifiedPath = $files['OUT'] ?? CarbonPHP::$app_root . CarbonPHP::$app_view . 'js/javascript.js';

            unset($files['OUT']);

            foreach ($files as $file) {
                if (!file_exists($file)) {
                    ColorCode::colorCode("\tFailed to find\n\t\t$file\n\n\n", 'red');
                    exit(1);
                }
            }

            if (file_exists($minifiedPath)) {

                unlink($minifiedPath);

            }

            $buffer = '';

            $jz = new JSqueeze;

            foreach ($files as $break => $file) {

                $codeFile = file_get_contents($file);

                if (false === $codeFile) {

                    ColorCode::colorCode("Failed to get file contents of (file://" . $file . ')', iColorCode::BACKGROUND_RED);

                    exit(4);

                }

                if (empty($codeFile)) {

                    ColorCode::colorCode("Contents of (file://" . $file . ') was empty.', iColorCode::BACKGROUND_RED);

                    exit(5);

                }

                $buffer .= PHP_EOL . $jz->squeeze(
                    file_get_contents($file) . ';',
                        true,   // $singleLine
                        true,   // $keepImportantComments
                        false   // $specialVarRx
                    );
            }

            if (!file_put_contents($minifiedPath, $buffer)) {

                ColorCode::colorCode("Failed to save the minified javascript!!\n\n\n", 'red');

            }

            ColorCode::colorCode("\tThe minified javascript (js) was stored to ::\n\n\t\t\t $minifiedPath\n\n", 'cyan');
        };
    }


    /**
     * @param $argv
     */
    public function run($argv): void
    {
        switch (array_shift($argv)) {
            case 'watch':

                function endsWith($haystack, $needle)
                {
                    $length = strlen($needle);
                    if (!$length) {
                        return true;
                    }
                    return substr($haystack, -$length) === $needle;
                }

                $this->JS()($this->PHP['MINIFY']['JS']);
                $this->CSS()($this->PHP['MINIFY']['CSS']);
                ColorCode::colorCode('Starting Watch');
                $tracking = [];

                $whichFilesToTrack = function (bool $refresh = false) {
                    if ($refresh) {
                        $this->PHP = self::getComposerConfig();
                    }
                    $php = [$this->PHP['SITE']['CONFIG']];
                    $css = $this->PHP['MINIFY']['CSS'];
                    $js = $this->PHP['MINIFY']['JS'];
                    unset($js['MINIFY']['CSS']['OUT'], $css['MINIFY']['JS']['OUT']);
                    return array_merge($php, $css, $js);
                };

                $files = $whichFilesToTrack();

                ColorCode::colorCode('Starting Watch');
                while (true) {
                    ColorCode::colorCode('.', 'blue');
                    foreach ($files as $file) {
                        if (!($tracking[$file] ?? false)) {
                            $tracking[$file]['time'] = filemtime($file);
                            $tracking[$file]['md5'] = md5_file($file);
                            ColorCode::colorCode($tracking[$file]['time'] . ' ' . $tracking[$file]['md5'] . ' ' . $file);
                            continue;
                        }

                        if ($tracking[$file]['md5'] !== md5_file($file)) {
                            $tracking[$file]['time'] = filemtime($file);
                            $tracking[$file]['md5'] = md5_file($file);
                            ColorCode::colorCode("Detected Change (MD5) in $file", 'red');

                            if (endsWith($file, 'Bootstrap.php')) {
                                $files = $whichFilesToTrack();
                                ColorCode::colorCode(print_r($files, true));
                                break;
                            }

                            ColorCode::colorCode('File Updated :: ' . $tracking[$file]['time'] . ' ' . $tracking[$file]['md5'] . ' ' . $file, 'blue');
                            $this->JS()($this->PHP['MINIFY']['JS']);
                            $this->CSS()($this->PHP['MINIFY']['CSS']);
                            sleep(3);
                            continue;
                        }
                    }
                    sleep(3);
                }
                break;
            case 'css':
                $this->CSS()($this->PHP['MINIFY']['CSS']);
                break;
            case 'js':
                $this->JS()($this->PHP['MINIFY']['JS']);
                break;
            default:
                $this->JS()($this->PHP['MINIFY']['JS']);
                $this->CSS()($this->PHP['MINIFY']['CSS']);
        }
    }

    public function cleanUp(): void
    {

    }
}