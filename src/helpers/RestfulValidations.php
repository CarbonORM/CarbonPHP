<?php


namespace CarbonPHP\Helpers;

use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Interfaces\iRestfulReferences;
use CarbonPHP\Interfaces\iRestMultiplePrimaryKeys;
use CarbonPHP\Interfaces\iRestNoPrimaryKey;
use CarbonPHP\Interfaces\iRestSinglePrimaryKey;
use CarbonPHP\Rest;
use CarbonPHP\Session;

trait RestfulValidations
{

    /**
     * @param string $columnValue
     * @param string $className
     * @param string $columnName
     * @throws PublicAlert
     */
    public static function validateUnique(string $columnValue, string $className, string $columnName): void
    {

        if (!class_exists($className)) {

            throw new PublicAlert('Rest validation error. Parameters given to validate unique incorrect.');

        }

        $return = [];

        $options = [
            iRest::class,
            iRestfulReferences::class,
            iRestNoPrimaryKey::class,
            iRestSinglePrimaryKey::class,
            iRestMultiplePrimaryKeys::class
        ];

        $imp = array_map('strtolower', array_keys(class_implements($className)));
        $opt = array_map('strtolower', array_keys(class_implements($options)));
        $intersect = array_intersect($imp, $opt);

        if (empty($intersect)) {
            $imp = implode('|', $options);
            throw new PublicAlert("Rest validation error. The class ($className) passed must extend ($imp).");
        }


        $noPrimary = in_array(strtolower(iRestfulReferences::class), $intersect, true)
            || in_array(strtolower(iRestNoPrimaryKey::class), $intersect, true);

        $query = [
            Rest::WHERE => [
                $columnName => $columnValue
            ],
            Rest::PAGINATION => [
                Rest::LIMIT => 1
            ]
        ];

        if (false === ($noPrimary ?
            $className::Get($return, $query) :
            $className::Get($return, null, $query))) {       // this will work for single or multiple keys
            throw new PublicAlert('Rest validation error. Get request failed in validation.');
        }

        if (!empty($return)) {
            throw new PublicAlert("Oh no! Looks like the value for '$columnName' already exists. Please use a different value and try again.");
        }
    }

    /**
     * @param array $request
     * @param string $column
     * @param string $value
     */
    public static function addToPostRequest(array &$request, string $column, string $value): void
    {
        $request[$column] = $value;
    }

    public static function addIDToPostRequest(array &$request, string $column): void
    {
        $request[$column] = Session::$user_id;
    }

}