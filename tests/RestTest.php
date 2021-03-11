<?php /** @noinspection PhpUndefinedClassInspection */
/**
 * Created by IntelliJ IDEA.
 * User: rmiles
 * Date: 6/26/2018
 * Time: 3:21 PM
 */

declare(strict_types=1);

namespace Tests;

use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Rest;
use CarbonPHP\Tables\Carbon_Location_References;
use CarbonPHP\Tables\Carbon_Locations;
use CarbonPHP\Tables\Carbon_User_Tasks;
use CarbonPHP\Tables\Carbon_Users as Users;
use CarbonPHP\Tables\Carbons;
use CarbonPHP\Tables\History_Logs;
use CarbonPHP\Tables\Sessions;


/**
 * @runTestsInSeparateProcesses
 */
final class RestTest extends Config
{

    public static array $restChallenge = [];


    /**
     * @param string $key
     * @throws PublicAlert
     */
    private function KeyExistsAndRemove(string $key): void
    {
        $store = [];

        self::assertTrue(Carbons::Get($store, $key, []),
            'Failed to see if user exists, post is actually dependant on GET.');

        if (!empty($store)) {
            self::assertTrue(
                Carbons::Delete($store, $key, []),
                'Rest api failed to remove the test key ' . $key
            );
        }
    }


    /**
     * @throws PublicAlert
     */
    public function testRestApiCanPost(): void
    {
        $this->KeyExistsAndRemove('8544e3d581ba11e8942cd89ef3fc55fa');

        $this->KeyExistsAndRemove('8544e3d581ba11e8942cd89ef3fc55fb');

        $store = [];

        if (!empty($store)) {
            self::assertTrue(
                Carbons::Delete($store, $store['entity_pk'], []),
                'Rest api failed to remove the test key.'
            );
        }


        // Should return a unique hex id
        self::assertInternalType('string', $pool = Carbons::Post([
            Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fa',
            Carbons::ENTITY_TAG => self::class
        ]));
    }


    /**
     * @depends testRestApiCanPost
     * @throws PublicAlert
     */
    public function testRestApiCanGet(): void
    {
        $store = [];
        self::assertTrue(Carbons::Get($store, '8544e3d581ba11e8942cd89ef3fc55fa', []));

        self::assertInternalType('array', $store);

        if (!empty($store)) {
            self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_FK], $store);
        }

        self::assertTrue(Carbons::Get($store, null,
            [
                Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fa'
            ]));

        // This route redirects to home, thus ending in false
    }


    /**
     * @depends testRestApiCanPost
     * @throws PublicAlert
     */
    public function testRestApiCanAggregate(): void
    {
        $temp = [];

        self::assertTrue(Carbons::Get($temp, '8544e3d581ba11e8942cd89ef3fc55fa', []));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $temp);

        $temp = [];

        self::assertTrue(Carbons::Get($temp, null, [
            Carbons::WHERE => [
                Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fa'
            ],
            Carbons::PAGINATION => [
                Carbons::LIMIT => 1
            ]
        ]));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $temp, 'failed on PAGINATION:LIMIT');
    }

    /**
     * @depends testRestApiCanGet
     * @throws PublicAlert
     */
    public function testRestApiCanPut(): void
    {
        $store = [];

        self::assertTrue(Carbons::Get($store, '8544e3d581ba11e8942cd89ef3fc55fa', []));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_FK], $store);

        self::assertTrue(
            Carbons::Put($store, $store[Carbons::COLUMNS[Carbons::ENTITY_PK]], [
                Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fb'
            ]), 'Failed Updating Records.');

        self::assertTrue(
            Carbons::Put($store, $store[Carbons::COLUMNS[Carbons::ENTITY_PK]], [
                Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fb'
            ]), 'Failed Updating Records With Identical Data. See https://stackoverflow.com/questions/10522520/pdo-were-rows-affected-during-execute-statement ');

        self::assertEquals('8544e3d581ba11e8942cd89ef3fc55fb', $store[Carbons::COLUMNS[Carbons::ENTITY_PK]]);

        $store = [];

        self::assertTrue(Carbons::Get($store, '8544e3d581ba11e8942cd89ef3fc55fb', []));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $store,
            'Failed to see updated record in database.');

    }


    /**
     * @depends testRestApiCanPost
     * @throws PublicAlert
     */
    public function testRestApiCanDelete(): void
    {
        $temp = [];

        self::assertTrue(Carbons::Get($temp, '8544e3d581ba11e8942cd89ef3fc55fa', []));

        if (empty($temp)) {
            self::assertTrue(Carbons::Get($temp, '8544e3d581ba11e8942cd89ef3fc55fb', []));
        }

        self::assertArrayHasKey('entity_fk', $temp);

        self::assertTrue(
            Carbons::Delete($temp, $temp['entity_pk'], [])
        );

        self::assertEmpty($temp);
    }

    /**
     * @throws PublicAlert
     * @depends testRestApiCanPost
     */
    public function testRestApiCanJoin(): void
    {
        $user = [];


        if (Users::Get($user, null, [
                Users::SELECT => [
                    Users::USER_ID
                ],
                Users::WHERE => [
                    Users::USER_USERNAME => Config::ADMIN_USERNAME
                ],
                Users::PAGINATION => [
                    Users::LIMIT => 1
                ]
            ]) && !empty($user)) {
            self::assertTrue(Users::Delete($user, $user[Users::COLUMNS[Users::USER_ID]], []),
                'Failed to delete user for join test.');
        }

        Rest::$commit = false;

        self::assertInternalType('string', $uid = Users::Post([
            Users::USER_TYPE => 'Athlete',
            Users::USER_IP => '127.0.0.1',
            Users::USER_SPORT => 'GOLF',
            Users::USER_EMAIL_CONFIRMED => 1,
            Users::USER_USERNAME => Config::ADMIN_USERNAME,
            Users::USER_PASSWORD => Config::ADMIN_PASSWORD,
            Users::USER_EMAIL => 'richard@miles.systems',
            Users::USER_FIRST_NAME => 'Richard',
            Users::USER_LAST_NAME => 'Miles',
            Users::USER_GENDER => 'Male'
        ]), 'No string ID was returned');


        self::assertInternalType('string', $lid = Carbon_Locations::Post([
            Carbon_Locations::CITY => 'Grapevine',
            Carbon_Locations::STATE => 'Texas',
            Carbon_Locations::ZIP => 76051
        ]), 'Failed to create location entity.');

        self::assertTrue(Carbon_Location_References::Post([
            Carbon_Location_References::ENTITY_REFERENCE => $uid,
            Carbon_Location_References::LOCATION_REFERENCE => $lid
        ]), 'Failed to create location references.');

        $this->commit();

        $user = [];

        self::assertTrue(Users::Get($user, $uid, [
            Users::SELECT => [
                Users::USER_USERNAME,
                Carbon_Locations::STATE
            ],
            Users::JOIN => [
                Users::INNER => [
                    Carbon_Location_References::TABLE_NAME => [
                        Users::USER_ID => Carbon_Location_References::ENTITY_REFERENCE
                    ],
                    Carbon_Locations::TABLE_NAME => [
                        Carbon_Locations::ENTITY_ID => Carbon_Location_References::LOCATION_REFERENCE
                    ]
                ]
            ],
            Users::PAGINATION => [
                Users::LIMIT => 1,
                Users::ORDER => [Users::USER_USERNAME => Users::ASC]
            ]
        ]), 'Failed to run inner join.');

        self::assertArrayHasKey(Users::COLUMNS[Users::USER_USERNAME], $user);

        self::assertEquals(Config::ADMIN_USERNAME, $user[Users::COLUMNS[Users::USER_USERNAME]]);

        self::assertEquals('Texas', $user[Carbon_Locations::COLUMNS[Carbon_Locations::STATE]]);
    }


    /**
     * This test undoubtedly does a half ass job at verifying order of operations,
     * expected return values for custom functions, custom method and validation preservation.
     * This will end up breaking and causing me to add another 40 lines.
     * @throws PublicAlert
     * @depends testRestApiCanJoin
     */
    public function testRestApiCanUseUserDefinedCallbacks(): void
    {
        $user = [];

        self::assertTrue(Users::Get($user, null, [
                Rest::WHERE => [
                    Users::USER_USERNAME => Config::ADMIN_USERNAME,
                    Users::USER_PASSWORD => Config::ADMIN_PASSWORD
                ],
                Rest::PAGINATION => [
                    Rest::LIMIT => 1
                ]
            ]
        ), 'The user could not be retrieved.');

        $uid = $user[Users::COLUMNS[Users::USER_ID]];

        self::assertNotEmpty($uid, 'The user id was empty.');

        self::assertEmpty(self::$restChallenge, 'Rest Challenges Should Start as Empty.');

        $id = Carbon_User_Tasks::Post([
            Carbon_User_Tasks::USER_ID => $uid,
            Carbon_User_Tasks::TASK_NAME => 'Hello World',
            Carbon_User_Tasks::TASK_DESCRIPTION => 'Test',
            Carbon_User_Tasks::PERCENT_COMPLETE => 70
        ]);

        self::assertCount(7, self::$restChallenge, 'Not all rest challenges have run');

        self::assertArrayHasKey(0, self::$restChallenge);
        self::assertArrayHasKey(1, self::$restChallenge);
        self::assertArrayHasKey(2, self::$restChallenge);
        self::assertArrayHasKey(3, self::$restChallenge);
        self::assertArrayHasKey(4, self::$restChallenge);
        self::assertArrayHasKey(5, self::$restChallenge);
        self::assertArrayHasKey(Carbon_User_Tasks::USER_ID, self::$restChallenge[0][0]);
        self::assertArrayHasKey(Carbon_User_Tasks::TASK_NAME, self::$restChallenge[0][0]);
        self::assertArrayHasKey(Carbon_User_Tasks::TASK_DESCRIPTION, self::$restChallenge[0][0]);
        self::assertArrayHasKey(Carbon_User_Tasks::PERCENT_COMPLETE, self::$restChallenge[0][0]);
        self::assertArrayHasKey(1, self::$restChallenge[1]);
        self::assertEquals(Rest::POST, self::$restChallenge[1][1]); // start at 0 ;)
        self::assertEquals(Rest::PREPROCESS, self::$restChallenge[1][2]); // start at 0 ;)
        self::assertEquals(Carbon_User_Tasks::PERCENT_COMPLETE, self::$restChallenge[3][1]);
    }


    /**
     * @depends testRestApiCanJoin
     * @throws PublicAlert
     */
    public function testRestApiCanSubQuery(): void
    {

        $user = [];

        /*$subSelect = Users::subSelect(null, [
            Users::SELECT => [
                Users::USER_ID
            ],
            Users::WHERE => [
                Users::USER_USERNAME => Config::ADMIN_USERNAME
            ]
        ]);*/

        #self::assertTrue(is_callable($subSelect), 'Failed asserting subSelect returns a calable.');

        #$subSelect = $subSelect();

        #self::assertTrue(is_string($subSelect));

        #self::assertTrue(Users::$allowSubSelectQueries, 'The allowSubSelectQueries variable was incorrectly set to false.');

        #self::assertSame(strpos($subSelect, '(SELECT '), 0);

        define('$name', true);

        self::assertTrue(Carbons::Get($user, null, [
            Carbons::SELECT => [
                Carbons::ENTITY_PK
            ],
            Carbons::WHERE => [
                Carbons::ENTITY_PK =>
                    Users::subSelect(null, [
                        Users::SELECT => [
                            Users::USER_ID
                        ],
                        Users::WHERE => [
                            Users::USER_USERNAME => Config::ADMIN_USERNAME
                        ]
                    ])
            ],
            Carbons::PAGINATION => [
                Carbons::LIMIT =>
                    1
            ]
        ]));

        self::assertNotEmpty($user, 'Could not get user admin via sub query.');

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $user);

    }


    /**
     * @depends testRestApiCanPost
     */
    public function testExternalRequestValidationRoutines(): void
    {

        $_POST = [
            Users::SELECT => [
                Users::USER_USERNAME,
                Carbon_Locations::STATE,
            ],
            Users::JOIN => [
                Users::INNER => [
                    Carbon_Location_References::TABLE_NAME => [
                        Users::USER_ID,
                        Carbon_Location_References::ENTITY_REFERENCE
                    ],
                    Carbon_Locations::TABLE_NAME => [
                        Carbon_Locations::ENTITY_ID,
                        Carbon_Location_References::LOCATION_REFERENCE
                    ]
                ]
            ],
            Users::PAGINATION => [
                Users::LIMIT => 10,
                Users::ORDER => [Users::USER_USERNAME, Users::ASC] // todo - I think Users::USER_USERNAME . Users::ASC worked, or didnt throw an error..
            ]
        ];


        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        Rest::ExternalRestfulRequestsAPI(Users::TABLE_NAME, null, Users::CLASS_NAMESPACE);

        $out = trim(ob_get_clean());

        self::assertNotEmpty($GLOBALS['json']['rest']);

        self::assertStringEndsWith('}', $out, 'Did not detect json output. OUTPUT :: ' . $out);

    }


    /**
     * TODO - this could be better showcasing more things
     * @throws PublicAlert
     * @depends testRestApiCanPost
     */
    public function testCascadeDelete(): void
    {
        $user = [Users::USER_USERNAME => Config::ADMIN_USERNAME];

        self::assertTrue(Users::Delete($user, null, [
            Users::USER_USERNAME => Config::ADMIN_USERNAME
        ]));

        self::assertEmpty($user, 'Could not delete user admin in cascade delete function.');

        self::assertInternalType('array', $user, 'Delete functions did not clear provided array to 
        empty array.');


        self::assertTrue(Users::Get($user, null, [
            Users::WHERE => [
                Users::USER_USERNAME => Config::ADMIN_USERNAME
            ]
        ]));

        self::assertTrue(Users::Delete($user, '8544e3d581ba11e8942cd89ef3fc55fb', []),
            'Test can delete by primary key.php');

        self::assertEmpty($user, 'Cascade delete failed.');
    }

    public function testRestApiCanUseNonCarbonPrimaryKeys(): void
    {
        $return = [];

        $session_primary_key = (string)random_int(0, 1234512341234);

        self::assertNotFalse(Sessions::Post([
            Sessions::USER_ID => '8544e3d581ba11e8942cd89ef3fc55fa',
            Sessions::USER_IP => '127.0.0.1',
            Sessions::SESSION_ID => $session_primary_key,
            Sessions::SESSION_EXPIRES => date('Y-m-d H:i:s'), // @link https://stackoverflow.com/questions/2215354/php-date-format-when-inserting-into-datetime-in-mysql/17295570
            Sessions::SESSION_DATA => '',
            Sessions::USER_ONLINE_STATUS => 1
        ]));

        self::assertTrue(Sessions::Put($return, $session_primary_key, [
            Sessions::USER_ID => '8544e3d581ba11e8942cd89ef3fc55fa',
            Sessions::USER_IP => '127.0.0.1',
            Sessions::SESSION_ID => $session_primary_key,
            Sessions::SESSION_DATA => '',
            Sessions::USER_ONLINE_STATUS => 0
        ]));

        self::assertTrue(Sessions::Get($return, $session_primary_key, []));

        self::assertTrue(Sessions::Delete($return, $session_primary_key, []));
    }


    /**
     * It can be noted that history logs are not Carbon tables as the reff will be deleted before it
     * is added to this table.
     * @throws PublicAlert
     */
    public function testRestApiCanUseTablesWithNoPrimaryKey(): void
    {
        $ignore = [];
        $condition = 'ME';

        // Should return a unique hex id
        self::assertTrue(History_Logs::Post([
            History_Logs::RESOURCE_TYPE => $condition,
            History_Logs::RESOURCE_UUID => '8544e3d581ba11e8942cd89ef3fc55fa',
            History_Logs::UUID => '8544e3d581ba11e8942cd89ef3fc55fa',
            History_Logs::DATA => '{}'
        ]));

        // Should return a unique hex id
        self::assertTrue(History_Logs::Put($ignore, [
            Rest::UPDATE => [
                History_Logs::DATA => '',
            ],
            Rest::WHERE => [
                History_Logs::RESOURCE_UUID => '8544e3d581ba11e8942cd89ef3fc55fa',
            ]
        ]));

        $return = [];

        self::assertTrue(History_Logs::Get($return, [
            Rest::WHERE => [
                History_Logs::RESOURCE_TYPE => $condition
            ],
            Rest::PAGINATION => [
                Rest::LIMIT => 1,
                Rest::ORDER => [History_Logs::UUID => Rest::ASC]
            ]
        ]));

        self::assertCount(5, $return);
    }

    public function testRestApiCanUseJson(): void
    {
        $ignore = [];
        $condition = 'ME';

        // Should return a unique hex id
        self::assertTrue(History_Logs::Post([
            History_Logs::RESOURCE_TYPE => $condition,
            History_Logs::RESOURCE_UUID => '8544e3d581ba11e8942cd89ef3fc55fa',
            History_Logs::UUID => '8544e3d581ba11e8942cd89ef3fc55fa',
            History_Logs::DATA => [
                'Test' => 'Value'
            ]
        ]));


        // Should return a unique hex id
        self::assertTrue(History_Logs::Put($ignore, [
            Rest::UPDATE => [
                History_Logs::RESOURCE_UUID => '8544e3d581ba11e8942cd89ef3fc55fb',
                History_Logs::UUID => '8544e3d581ba11e8942cd89ef3fc55fb',
                History_Logs::DATA => [
                    'Test' => 'Value'
                ]
            ],
            Rest::WHERE => [
                History_Logs::RESOURCE_TYPE => $condition,
            ]
        ]));

        $return = [];

        self::assertTrue(History_Logs::Get($return, [
            Rest::WHERE => [
                History_Logs::RESOURCE_TYPE => $condition
            ],
            Rest::PAGINATION => [
                Rest::LIMIT => 1,
                Rest::ORDER => [History_Logs::UUID => Rest::ASC]
            ]
        ]));

        self::assertCount(5, $return);


    }

}