<?php


namespace Config;


use CarbonPHP\Error\PublicAlert;
use CarbonPHP\interfaces\iRest;
use CarbonPHP\interfaces\iRestfulReferences;
use CarbonPHP\Rest;

class Validate
{

    /**
     * @param string $columnValue
     * @param string $className
     * @param string $columnName
     * @return bool
     * @throws PublicAlert
     */
    public static function validateUnique(string $columnValue, string $className, string $columnName): bool
    {
        if (class_exists($className)) {

            $imp = array_map('strtolower', array_keys(class_implements($className)));

            $return = [];

            $hasPrimary = in_array(strtolower(iRest::class), $imp, true);

            if (!$hasPrimary &&
                !in_array(strtolower(iRestfulReferences::class), $imp, true)) {
                throw new PublicAlert('Class given to validate unique was not Rest generated.');
            }

            $query = [
                Rest::WHERE => [
                    $columnName => $columnValue
                ],
                Rest::PAGINATION => [
                    Rest::LIMIT => 1
                ]
            ];

            /** @noinspection PhpUndefinedMethodInspection */
            if ($hasPrimary ?
                $className::Get($return, null, $query) :
                $className::Get($return, $query)) {
                if (empty($return)) {
                    return true;
                }                                              // todo - regex this
                throw new PublicAlert("Oh no! Looks like '$columnValue' already exists. Please use a different value and try again.");
            }
            throw new PublicAlert('Rest validation error. Get request failed in validation.');
        }
        throw new PublicAlert('Rest validation error. Parameters given to validate unique incorrect.');
    }

    /**
     * @param array $request
     * @param string $column
     * @param string $value
     */
    public static function addToPostRequest(array &$request, string $column, string $value) : void {
        $request[$column] = $value;
    }

}