<?php

namespace CarbonPHP\Restful;

use CarbonPHP\Abstracts\Composer;
use CarbonPHP\CarbonPHP;
use CarbonPHP\Error\PrivateAlert;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use Throwable;

abstract class RestAutoTargeting extends RestSettings
{

    /**
     * @throws PrivateAlert
     */
    public static function autoTargetTableDirectory(): string
    {

        $composerJson = Composer::getComposerConfig();

        $tableNamespace = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::NAMESPACE] ??= "Tables\\\\";

        $tableDirectory = $composerJson['autoload']['psr-4'][$tableNamespace] ?? false;

        if (false === $tableDirectory) {

            throw new PrivateAlert('Failed to parse composer json for ["autoload"]["psr-4"]["' . $tableNamespace . '"].');

        }

        return CarbonPHP::$app_root . $tableDirectory;

    }

    public static function getDynamicRestClass(string $fullyQualifiedRestClassName, string $mustInterface = null): string
    {
        try {
            static $cache = [];

            if (array_key_exists($fullyQualifiedRestClassName, $cache)) {

                return $cache[$fullyQualifiedRestClassName];

            }

            $prefix = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::TABLE_PREFIX] ?? '';

            $namespace = CarbonPHP::$configuration[CarbonPHP::REST][CarbonPHP::NAMESPACE] ?? '';

            $className = $fullyQualifiedRestClassName::TABLE_NAME;

            if (str_starts_with($className, $prefix)) {

                $className = substr($className, strlen($prefix));

            }

            $custom_prefix_carbon_table = $namespace . ucwords($className, '_');        //  we're using table name and not class name as any different prefix, even a subset of the original, will be appended

            // if this is our custom namespace we skip
            if ($fullyQualifiedRestClassName === $custom_prefix_carbon_table) {

                return $fullyQualifiedRestClassName;

            }

            if (!class_exists($custom_prefix_carbon_table)) {

                throw new PrivateAlert("Could not find the required class ($custom_prefix_carbon_table) in the user defined namespace ($namespace). This is required.");

            }

            if (($mustInterface === iRestSinglePrimaryKey::class || $mustInterface === null)
                && in_array(iRestSinglePrimaryKey::class, class_implements($fullyQualifiedRestClassName), true)) {

                if (!in_array(iRestSinglePrimaryKey::class, class_implements($custom_prefix_carbon_table), true)) {

                    throw new PrivateAlert("Your implementation ($custom_prefix_carbon_table) of ($fullyQualifiedRestClassName) should implement " . iRestSinglePrimaryKey::class . '. You should rerun RestBuilder.');

                }

            } else if (($mustInterface === iRestNoPrimaryKey::class || $mustInterface === null) && in_array(iRestNoPrimaryKey::class, class_implements($fullyQualifiedRestClassName), true)) {

                if (!in_array(iRestNoPrimaryKey::class, class_implements($custom_prefix_carbon_table), true)) {

                    throw new PrivateAlert("Your implementation ($custom_prefix_carbon_table) of ($fullyQualifiedRestClassName) should implement " . iRestNoPrimaryKey::class . '. You should rerun RestBuilder.');

                }

            } else if (($mustInterface === iRestMultiplePrimaryKeys::class || $mustInterface === null) && in_array(iRestMultiplePrimaryKeys::class, class_implements($fullyQualifiedRestClassName), true)
                && !in_array(iRestMultiplePrimaryKeys::class, class_implements($custom_prefix_carbon_table), true)) {

                throw new PrivateAlert("Your implementation ($custom_prefix_carbon_table) of ($fullyQualifiedRestClassName) should implement " . iRestMultiplePrimaryKeys::class . '. You should rerun RestBuilder.');

            } else {

                if ($mustInterface === null) {

                    throw new PrivateAlert("The table '$custom_prefix_carbon_table' we determined to be your implementation of '$fullyQualifiedRestClassName' failed to implement any of the correct interfaces.");

                }

                throw new PrivateAlert("The table '$custom_prefix_carbon_table' we determined to be your implementation of '$fullyQualifiedRestClassName' failed to implement the required '$mustInterface'.");

            }

            return $cache[$fullyQualifiedRestClassName] = $custom_prefix_carbon_table;

        } catch (Throwable $e) {

            ThrowableHandler::generateLogAndExit($e);

        }

    }

    /**
     * @throws PrivateAlert
     */
    public static function getRestNamespaceFromFileList(array $filePaths): string
    {

        foreach ($filePaths as $filename) {

            $fileAsString = file_get_contents($filename);

            $matches = [];

            if (!preg_match('#public const CLASS_NAMESPACE\s?=\s?\'(.*)\';#i', $fileAsString, $matches)) {
                continue;
            }

            if (array_key_exists(1, $matches)) {

                $classNamespace = $matches[1];

                break;

            }

        }

        if (empty($classNamespace)) {

            // filePaths should be from glob
            $tableDirectory = array_key_exists(0, $filePaths) ? dirname($filePaths[0]) : 'NULL';

            throw new PrivateAlert("Failed to parse class namespace from files in ($tableDirectory). ");

        }

        return $classNamespace;

    }

    public static function reformatLoosenedSQL(array $sql) : string
    {

        $last = array_pop($sql);

        $first = array_shift($sql);

        return $first . PHP_EOL . implode(',' . PHP_EOL, $sql) . PHP_EOL . $last . ';';

    }

    public static function parseSchemaSQL(string $sql = null, array $replace = null): array
    {

        $sql = trim($sql);

        $sql = str_replace("\\n", "\n", $sql);

        $sql = trim($sql);

        $replace ??= self::SQL_VERSION_PREG_REPLACE;

        $pattern = array_keys($replace);

        $replacement = array_values($replace);

        $SQLArray = array_map('trim', explode(PHP_EOL, $sql));

        return preg_replace($pattern, $replacement, $SQLArray);

    }

}

