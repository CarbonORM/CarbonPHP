<?php


namespace Tests\Feature;


use CarbonPHP\Error\ErrorCatcher;
use CarbonPHP\Rest;
use CarbonPHP\Tables\Location_References;
use CarbonPHP\Tables\Locations;
use CarbonPHP\Tables\Photos;
use CarbonPHP\Tables\Users;
use Throwable;


/**
 * @runTestsInSeparateProcesses
 */
class ExternalRestTest extends Config
{
    public function testGenerateCorrectDistinctCountAndThreeArgumentBooleanConditionsUsingIntAndStringSql(): void
    {
        try {
            $_GET = [
                Rest::SELECT => [
                    [Rest::COUNT, Photos::PHOTO_PATH, 'countCustomNamed'],
                    [Rest::DISTINCT, Photos::PHOTO_PATH, 'countCustomNamed']
                ],
                Rest::JOIN => [
                    Rest::INNER => [
                        Locations::TABLE_NAME => [
                            [
                                Locations::ENTITY_ID,
                                Rest::EQUAL,
                                Location_References::LOCATION_REFERENCE
                            ]
                        ],
                    ]
                ],
                Rest::WHERE => [
                    [Photos::PHOTO_ID, Rest::NOT_EQUAL, 1],
                    [Photos::PHOTO_ID => Location_References::ENTITY_REFERENCE],
                ],
                Rest::PAGINATION => [
                    Rest::LIMIT => 1000,
                ]
            ];

            $_SERVER['REQUEST_METHOD'] = 'GET';

            ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

            self::assertTrue(Rest::ExternalRestfulRequestsAPI(Photos::TABLE_NAME, null, Users::CLASS_NAMESPACE));

            $out = trim(ob_get_clean());

            $json_array = json_decode(trim($out), true);

            self::assertArrayHasKey('sql', $json_array);

            self::assertArrayHasKey('rest', $json_array);

            self::assertEquals(
                "SELECT DISTINCT(carbon_photos.photo_path) AS :injection2, COUNT(carbon_photos.photo_path) AS :injection1 FROM CarbonPHP.carbon_photos INNER JOIN CarbonPHP.carbon_locations ON ((carbon_locations.entity_id = UNHEX(:injection0))) WHERE ((carbon_photos.photo_id <> UNHEX(:injection3)) AND (carbon_photos.photo_id = UNHEX(:injection4))) ORDER BY parent_id DESC LIMIT 1000",
                $GLOBALS['json']['sql'][0][1] ?? $GLOBALS['json']);

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

        }

    }

    public function testRootLevelJoinConditionBooleanSwitch(): void
    {
        try {
            $_GET = [
                Rest::SELECT => [
                    Users::USER_USERNAME,
                    Locations::STATE,
                ],
                Rest::JOIN => [
                    Rest::INNER => [
                        Location_References::TABLE_NAME => [
                            Users::USER_ID => Location_References::ENTITY_REFERENCE,
                            Users::USER_EMAIL => 'example@example.com', // this very much does not matter
                            [
                                Users::USER_ID => Location_References::ENTITY_REFERENCE,
                                [
                                    Users::USER_EMAIL => 'example@example.com'
                                ]
                            ],
                            Users::USER_ABOUT_ME => Location_References::ENTITY_REFERENCE
                        ],
                        Locations::TABLE_NAME => [
                            [
                                Locations::ENTITY_ID => Location_References::LOCATION_REFERENCE,
                                Locations::LONGITUDE => Users::USER_ABOUT_ME    // this doesnt matter we are testing structure
                            ]
                        ]
                    ]
                ],
                Rest::WHERE => [
                    [Users::USER_USERNAME, Rest::LIKE, '%rock%']
                ],
                Rest::PAGINATION => [
                    Rest::LIMIT => 10,
                    Rest::ORDER => [
                        Users::USER_USERNAME => Rest::ASC
                    ] // todo - I think Users::USER_USERNAME . Users::ASC worked, or didnt throw an error..
                ]
            ];

            $_SERVER['REQUEST_METHOD'] = 'GET';

            ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

            self::assertTrue(Rest::ExternalRestfulRequestsAPI(Users::TABLE_NAME, null, Users::CLASS_NAMESPACE));

            $out = trim(ob_get_clean());

            $json_array = json_decode(trim($out), true);

            self::assertArrayHasKey('sql', $json_array);

            self::assertArrayHasKey('rest', $json_array);

            self::assertEquals(
                "SELECT carbon_users.user_username, carbon_locations.state FROM CarbonPHP.carbon_users INNER JOIN CarbonPHP.carbon_location_references ON (carbon_users.user_id = carbon_location_references.entity_reference AND carbon_users.user_email = :injection0 AND (carbon_users.user_id = carbon_location_references.entity_reference OR (carbon_users.user_email = :injection1)) AND carbon_users.user_about_me = carbon_location_references.entity_reference)INNER JOIN CarbonPHP.carbon_locations ON ((carbon_locations.entity_id = carbon_location_references.location_reference OR carbon_locations.longitude = carbon_users.user_about_me)) WHERE ((carbon_users.user_username LIKE :injection2)) ORDER BY carbon_users.user_username ASC LIMIT 10",
                $GLOBALS['json']['sql'][0][1] ?? $GLOBALS['json']);

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

        }

    }

    public function testMultipleJoinConditionsOnSingleTableNoLimit(): void
    {
        try {
            $_GET = [
                Rest::SELECT => [
                    Users::USER_USERNAME,
                    Locations::STATE,
                ],
                Rest::JOIN => [
                    Rest::INNER => [
                        Location_References::TABLE_NAME => [
                            [Users::USER_ID =>
                                Location_References::ENTITY_REFERENCE]
                        ],
                        Locations::TABLE_NAME => [
                            [Locations::ENTITY_ID =>
                                Location_References::LOCATION_REFERENCE]
                        ]
                    ]
                ],
                Rest::WHERE => [
                    [Users::USER_USERNAME, Rest::LIKE, '%rock%']
                ],
                Rest::PAGINATION => [
                    Rest::LIMIT => null,
                    Rest::ORDER => [
                        Users::USER_USERNAME => Rest::ASC
                    ] // todo - I think Users::USER_USERNAME . Users::ASC worked, or didnt throw an error..
                ]
            ];

            $_SERVER['REQUEST_METHOD'] = 'GET';

            ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

            self::assertTrue(Rest::ExternalRestfulRequestsAPI(Users::TABLE_NAME, null, Users::CLASS_NAMESPACE));

            $out = trim(ob_get_clean());

            $json_array = json_decode(trim($out), true);

            self::assertArrayHasKey('sql', $json_array);

            self::assertArrayHasKey('rest', $json_array);

            self::assertEquals(
                "SELECT carbon_users.user_username, carbon_locations.state FROM CarbonPHP.carbon_users INNER JOIN CarbonPHP.carbon_location_references ON ((carbon_users.user_id = carbon_location_references.entity_reference))INNER JOIN CarbonPHP.carbon_locations ON ((carbon_locations.entity_id = carbon_location_references.location_reference)) WHERE ((carbon_users.user_username LIKE :injection0)) ORDER BY carbon_users.user_username ASC ",
                $GLOBALS['json']['sql'][0][1] ?? $GLOBALS['json']);

        } catch (Throwable $e) {

            ErrorCatcher::generateLog($e);

        }
    }

}