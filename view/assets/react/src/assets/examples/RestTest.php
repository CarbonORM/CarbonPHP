<?php
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
use CarbonPHP\Tables\Location_References;
use CarbonPHP\Tables\Locations;
use CarbonPHP\Tables\Users as Users;
use CarbonPHP\Tables\Carbons;


/**
 * @runTestsInSeparateProcesses
 */
final class RestTest extends Config
{

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
    public function testRestAdiCanAggregate(): void {
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
            self::assertTrue(Users::Delete($user, $user[Users::COLUMNS[Users::USER_ID]], []));
        }

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

        self::assertInternalType('string', $lid = Locations::Post([
            Locations::CITY => 'Grapevine',
            Locations::STATE => 'Texas',
            Locations::ZIP => 76051
        ]), 'Failed to create location entity.');

        self::assertTrue(Location_References::Post([
            Location_References::ENTITY_REFERENCE => $uid,
            Location_References::LOCATION_REFERENCE => $lid
        ]), 'Failed to create location references.');


        $user = [];

        self::assertTrue(Users::Get($user, $uid, [
            Users::SELECT => [
                Users::USER_USERNAME,
                Locations::STATE
            ],
            Users::JOIN => [
                Users::INNER => [
                    Location_References::TABLE_NAME => [
                        Users::USER_ID,
                        Location_References::ENTITY_REFERENCE
                    ],
                    Locations::TABLE_NAME => [
                        Locations::ENTITY_ID,
                        Location_References::LOCATION_REFERENCE
                    ]
                ]
            ],
            Users::PAGINATION => [
                Users::LIMIT => 1,
                Users::ORDER => Users::USER_USERNAME . Users::ASC
            ]
        ]));

        self::assertArrayHasKey(Users::COLUMNS[Users::USER_USERNAME], $user);

        self::assertEquals(Config::ADMIN_USERNAME, $user[Users::COLUMNS[Users::USER_USERNAME]]);

        self::assertEquals('Texas', $user[Locations::COLUMNS[Locations::STATE]]);
    }


    /**
     * @depends testRestApiCanPost
     * @throws PublicAlert
     */
    public function testRestApiCanSubQuery(): void
    {
        $user = [];

        define('testlazy', true);

        $subSelect = Users::subSelect(null, [
            Users::SELECT => [
                Users::USER_ID
            ],
            Users::WHERE => [
                Users::USER_USERNAME => Config::ADMIN_USERNAME
            ]
        ]);


        self::assertTrue(Users::$allowSubSelectQueries);

        self::assertSame(strpos($subSelect, '(SELECT '), 0);

        self::assertTrue(Carbons::Get($user, null, [
            Carbons::SELECT => [
                Carbons::ENTITY_PK
            ],
            Carbons::WHERE => [
                Carbons::ENTITY_PK =>
                    $subSelect
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

        $_POST = [Users::SELECT => [
            Users::USER_USERNAME,
            Locations::STATE
        ],
            Users::JOIN => [
                Users::INNER => [
                    Location_References::TABLE_NAME => [
                        Users::USER_ID,
                        Location_References::ENTITY_REFERENCE
                    ],
                    Locations::TABLE_NAME => [
                        Locations::ENTITY_ID,
                        Location_References::LOCATION_REFERENCE
                    ]
                ]
            ],
            Users::PAGINATION => [
                Users::LIMIT => 10,
                Users::ORDER => Users::USER_USERNAME . Users::ASC
            ]];


        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start(null, 0, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

        Rest::RestfulRequests(Users::TABLE_NAME, null);

        $out = trim(ob_get_clean());

        self::assertNotEmpty($GLOBALS['json']['rest']);

        self::assertStringEndsWith('}', $out, 'Did not detect json output.');

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


}