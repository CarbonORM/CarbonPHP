<?php


namespace Tests\Feature;


use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Rest;
use CarbonPHP\Tables\Location_References;
use CarbonPHP\Tables\Locations;
use CarbonPHP\Tables\Photos;
use CarbonPHP\Tables\Users;


class FullRestTest extends CarbonRestTest
{

    public function testGenerateCorrectDistinctCountAndThreeArgumentBooleanConditionsUsingIntAndStringSql(): void
    {

        $_GET = [
            iRest::SELECT => [
                [iRest::COUNT, Photos::PHOTO_ID, 'countCustomNamed'],
                [iRest::DISTINCT, Photos::PHOTO_PATH, 'distCustomNamed']
            ],
            iRest::JOIN => [
                iRest::INNER => [
                    Locations::TABLE_NAME => [
                        [
                            Locations::ENTITY_ID,
                            iRest::EQUAL,
                            Location_References::LOCATION_REFERENCE
                        ]
                    ],
                ]
            ],
            iRest::WHERE => [
                [Photos::PHOTO_ID, iRest::NOT_EQUAL, 1],
                [Photos::PHOTO_ID => Location_References::ENTITY_REFERENCE],
            ],
            iRest::GROUP_BY => [
                Photos::PHOTO_ID
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1000,
            ]
        ];

        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        $GLOBALS['json'] = [];

        self::assertTrue(Rest::ExternalRestfulRequestsAPI(Photos::CLASS_NAME, null, Users::CLASS_NAMESPACE));

        $out = trim(ob_get_clean());

        /** @noinspection PhpUnhandledExceptionInspection */
        $json_array = json_decode(trim($out), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('sql', $json_array);

        self::assertArrayHasKey('rest', $json_array);

        #sortDump($GLOBALS['json']['sql']);

        self::assertEquals(
            "SELECT DISTINCT(carbon_photos.photo_path) AS :injection1, COUNT(carbon_photos.photo_id) AS :injection0 FROM CarbonPHP.carbon_photos INNER JOIN CarbonPHP.carbon_locations ON ((carbon_locations.entity_id = UNHEX(:injection2))) WHERE ((carbon_photos.photo_id <> UNHEX(:injection3)) AND (carbon_photos.photo_id = UNHEX(:injection4))) GROUP BY carbon_photos.photo_id  LIMIT 1000",
            $GLOBALS['json']['sql'][2]['stmt']['sql']);


    }

    public function testRootLevelJoinConditionBooleanSwitch(): void
    {
        $_GET = [
            iRest::SELECT => [
                Users::USER_USERNAME,
                Locations::STATE,
            ],
            iRest::JOIN => [
                iRest::INNER => [
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
            iRest::WHERE => [
                [Users::USER_USERNAME, iRest::LIKE, '%rock%']
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 10,
                iRest::ORDER => [
                    Users::USER_USERNAME => iRest::ASC
                ] // todo - I think Users::USER_USERNAME . Users::ASC worked, or didnt throw an error..
            ]
        ];

        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        $GLOBALS['json'] = [];

        self::assertTrue(Rest::ExternalRestfulRequestsAPI(Users::CLASS_NAME, null, Users::CLASS_NAMESPACE));

        $out = trim(ob_get_clean());

        /** @noinspection JsonEncodingApiUsageInspection */
        $json_array = json_decode(trim($out), true);

        self::assertArrayHasKey('sql', $json_array);

        self::assertArrayHasKey('rest', $json_array);

        self::assertEquals(
            "SELECT carbon_users.user_username, carbon_locations.state FROM CarbonPHP.carbon_users INNER JOIN CarbonPHP.carbon_location_references ON (carbon_users.user_id = carbon_location_references.entity_reference AND carbon_users.user_email = :injection0 AND (carbon_users.user_id = carbon_location_references.entity_reference OR (carbon_users.user_email = :injection0)) AND carbon_users.user_about_me = carbon_location_references.entity_reference) INNER JOIN CarbonPHP.carbon_locations ON ((carbon_locations.entity_id = carbon_location_references.location_reference OR carbon_locations.longitude = carbon_users.user_about_me)) WHERE ((carbon_users.user_username LIKE :injection1)) ORDER BY carbon_users.user_username ASC  LIMIT 10",
            $GLOBALS['json']['sql'][0]['stmt']['sql']);


    }

    public function testMultipleJoinConditionsOnSingleTableNoLimit(): void
    {
        $_GET = [
            iRest::SELECT => [
                Users::USER_USERNAME,
                Locations::STATE,
            ],
            iRest::JOIN => [
                iRest::INNER => [
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
            iRest::WHERE => [
                [Users::USER_USERNAME, iRest::LIKE, '%admin%']
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => null,
                iRest::ORDER => [
                    Users::USER_USERNAME => iRest::ASC
                ] // todo - I think Users::USER_USERNAME . Users::ASC worked, or didnt throw an error..
            ]
        ];

        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        $GLOBALS['json'] = [];

        self::assertTrue(Rest::ExternalRestfulRequestsAPI(Users::TABLE_NAME, null, Users::CLASS_NAMESPACE));

        $out = trim(ob_get_clean());

        $json_array = json_decode(trim($out), true);

        self::assertArrayHasKey('sql', $json_array);

        self::assertArrayHasKey('rest', $json_array);

        self::assertEquals(
            "SELECT carbon_users.user_username, carbon_locations.state FROM CarbonPHP.carbon_users INNER JOIN CarbonPHP.carbon_location_references ON ((carbon_users.user_id = carbon_location_references.entity_reference)) INNER JOIN CarbonPHP.carbon_locations ON ((carbon_locations.entity_id = carbon_location_references.location_reference)) WHERE ((carbon_users.user_username LIKE :injection0)) ORDER BY carbon_users.user_username ASC",
            $GLOBALS['json']['sql'][0]['stmt']['sql']);

    }

    public function testCanUseIsAggregate(): void {

        $_GET = [
            iRest::SELECT => [
                Users::USER_USERNAME,
            ],
            iRest::WHERE => [
                Users::USER_MEMBERSHIP => [ iRest::LESS_THAN, 2],
                Users::USER_LOCATION => [iRest::IS, iRest::UNKNOWN ],
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1,
                iRest::ORDER => [
                    Users::USER_USERNAME => iRest::ASC
                ] // todo - I think Users::USER_USERNAME . Users::ASC worked, or didnt throw an error..
            ],
            iRest::GROUP_BY => [
                Users::USER_USERNAME
            ],
        ];

        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        self::assertTrue(Rest::ExternalRestfulRequestsAPI(Users::TABLE_NAME, null, Users::CLASS_NAMESPACE));

        $out = trim(ob_get_clean());

        $json_array = json_decode(trim($out), true);

        self::assertCount(1, $json_array['rest']);

        $_GET = [
            iRest::SELECT => [
                Users::USER_USERNAME,
                [ Users::USER_SESSION_ID, iRest::IS, iRest::NULL ],
                [ Users::USER_SESSION_ID, iRest::IS, iRest::UNKNOWN ],
                [ Users::USER_SESSION_ID, iRest::IS, iRest::TRUE ],
                [ Users::USER_SESSION_ID, iRest::IS, iRest::FALSE ]
            ],
            iRest::WHERE => [
                Users::USER_LOCATION => [iRest::IS, iRest::UNKNOWN ],
                [ Users::USER_SESSION_ID, iRest::IS, iRest::NULL ],
                [Users::USER_USERNAME, iRest::LIKE, '%admin%']
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1,
                iRest::ORDER => [
                    Users::USER_USERNAME => iRest::ASC
                ] // todo - I think Users::USER_USERNAME . Users::ASC worked, or didnt throw an error..
            ],
            iRest::GROUP_BY => [
                Users::USER_USERNAME
            ],
        ];

        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        self::assertTrue(Rest::ExternalRestfulRequestsAPI(Users::TABLE_NAME, null, Users::CLASS_NAMESPACE));

        $out = trim(ob_get_clean());

        $json_array = json_decode(trim($out), true);

        self::assertCount(5, $json_array['rest']);

    }



    public function testBooleanJoinToNestedAggregateHavingAndGroupBy(): void
    {
        $_GET = [
            iRest::SELECT => [
                Users::USER_USERNAME,
                Users::USER_ABOUT_ME,
                [iRest::COUNT, Location_References::ENTITY_REFERENCE],
                [iRest::DISTINCT, Location_References::ENTITY_REFERENCE],
            ],
            iRest::JOIN => [
                iRest::INNER => [
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
            iRest::WHERE => [
                [Users::USER_USERNAME, iRest::LIKE, '%rock%'],
            ],
            iRest::GROUP_BY => [
                Users::USER_USERNAME
            ],
            iRest::HAVING => [
                Users::USER_ABOUT_ME => [
                    iRest::NOT_EQUAL,
                    [iRest::COUNT, Location_References::ENTITY_REFERENCE]
                ]
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => null,
                iRest::ORDER => [
                    Users::USER_USERNAME => iRest::ASC
                ]
            ]
        ];

        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        $GLOBALS['json'] = [];

        self::assertTrue(Rest::ExternalRestfulRequestsAPI(Users::TABLE_NAME, null, Users::CLASS_NAMESPACE));

        $out = trim(ob_get_clean());

        $json_array = json_decode(trim($out), true);

        self::assertArrayHasKey('sql', $json_array);

        self::assertArrayHasKey('rest', $json_array);

        self::assertEquals(
            "SELECT DISTINCT HEX(carbon_location_references.entity_reference) AS entity_reference, carbon_users.user_username, carbon_users.user_about_me, COUNT(carbon_location_references.entity_reference) FROM CarbonPHP.carbon_users INNER JOIN CarbonPHP.carbon_location_references ON ((carbon_users.user_id = carbon_location_references.entity_reference)) INNER JOIN CarbonPHP.carbon_locations ON ((carbon_locations.entity_id = carbon_location_references.location_reference)) WHERE ((carbon_users.user_username LIKE :injection0)) GROUP BY carbon_users.user_username  HAVING (carbon_users.user_about_me <> COUNT(carbon_location_references.entity_reference)) ORDER BY carbon_users.user_username ASC",
            $GLOBALS['json']['sql'][0]['stmt']['sql']);

    }



}