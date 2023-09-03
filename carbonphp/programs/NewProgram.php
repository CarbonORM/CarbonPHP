<?php


namespace CarbonPHP\Programs;

use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Abstracts\Composer;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Interfaces\iCommand;


/* @author Richard Tyler Miles
 *
 *      Special thanks to the following people//resources
 *
 * @link https://gist.github.com/pbojinov/8965299
 */
class NewProgram extends Composer implements iCommand
{

    public static function getProgramsNamespacesAndDirectories(): array
    {
        $json = self::getComposerConfig();

        // loop through psr-4 and find namespaces and directories which have names ending with Programs\\
        $programs = [];

        foreach ($json['autoload']['psr-4'] as $namespace => $directory) {
            if ($namespace === 'Programs\\' || str_ends_with($namespace, '\\Programs\\')) {
                $programs[$namespace] = $directory;
            }
        }

        if (empty($programs)) {

            ColorCode::colorCode('No Programs namespace found. Please add a namespace ending in Programs to your composer.json file to define custom C6 programs.', iColorCode::BACKGROUND_YELLOW);

            return [];

        }

        return $programs;

    }

    public function usage(): void
    {

        print "\n\n\tThis creates a new file in the appropriate directory.  >> index.php newprogram [program_name]\n\n";

        exit(1);

    }

    public function cleanUp(): void
    {
        // nothing
    }

    public function run($argv): void
    {
        if (empty($argv)) {
            ColorCode::colorCode('Please provide a program name.', iColorCode::RED);
            $this->usage();
        }

        $programName = $argv[0];

        if (empty($programName)) {
            ColorCode::colorCode("Failed to parse program name.  >> index.php newprogram [program_name]\n\n", iColorCode::RED);
            exit(1);
        }

        $programPsr4 = self::getProgramsNamespacesAndDirectories();

        $file = self::programFile($programPsr4, $programName);

        print "\n\tThe new program file ($file) was created successfully!\n\n";

    }

    private static function programFile(array $programDirectories, string $programName): string
    {

        if (1 === count($programDirectories)) {
            $namespace = array_key_first($programDirectories);
            $programDirectory = $programDirectories[$namespace];
        } else {

            ColorCode::colorCode("Please enter the number of the namespace you would like to use for your program.\n\n", iColorCode::YELLOW);

            $namespaceKeys = array_keys($programDirectories);

            foreach ($programDirectories as $key => $value) {
                ColorCode::colorCode(array_search($key, $namespaceKeys, true) . ")\t$key => $value\n", iColorCode::BACKGROUND_GREEN);
            }

            $selection = readline('Enter the number of the namespace you would like to use for your program: ');

            $namespace = $namespaceKeys[$selection] ?? '';

            if (empty($namespace)) {
                ColorCode::colorCode("Failed to parse namespace. >> index.php newprogram [program_name]\n\n", iColorCode::RED);
                exit(1);
            }

            $programDirectory = $programDirectories[$namespace];

        }

        $namespace = "namespace " . rtrim($namespace, '\\') . ";";

        $template = <<<PROGRAM
<?php

$namespace

use CarbonPHP\CarbonPHP;
use CarbonPHP\Abstracts\ColorCode;
use CarbonPHP\Interfaces\iCommand;
use CarbonPHP\Interfaces\iColorCode;

class $programName implements iCommand
{

    private array \$CONFIG;

    public function __construct(\$CONFIG)
    {
        \$this->CONFIG = \$CONFIG;
    }

    public static function description(): string
    {
        return 'Sample program for CarbonPHP; todo - add description for $programName';
    }

    public function usage(): void
    {
        // TODO - improve documentation
            print <<<END
\n
\t           Question Marks Denote Optional Parameters
\t           Order does not matter.
\t           Flags do not stack ie. not -edf, this -e -f -d
\t Usage::
\t  php index.php $programName [options]  

\t       -help                        - this dialogue                
\n
END;
        exit(1);
    }

    public function cleanUp(): void
    {
        // do something or nothing.. up to you
    }

    public function run(\$argv): void
    {
        \$C6 = CarbonPHP::CARBON_ROOT === CarbonPHP::\$app_root . 'src' . DS;
        \$argc = count(\$argv);
        for (\$i = 0; \$i < \$argc; \$i++) {
            switch (\$argv[\$i]) {
                default:
                case '-help':
                    if (\$C6) {
                        ColorCode::colorCode("\\tYou da bomb :)\\n", iColorCode::CYAN);
                        break;
                    }
                    \$this->usage();
                    break;
                
            }
        }
       // todo - add program code
    }

}

PROGRAM;

        if (!file_put_contents($file = $programDirectory . $programName . '.php', $template)) {
            print 'Failed to create program file. Check directory permissions.';
            exit(1);
        }

        return $file;

    }

    public static function description(): string
    {
        return 'Creates a new program file in the appropriate directory.';
    }
}