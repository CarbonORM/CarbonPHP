<?php
/**
 * Created by IntelliJ IDEA.
 * User: rmiles
 * Date: 6/26/2018
 * Time: 3:21 PM
 */

declare(strict_types=1);

namespace Tests;

use CarbonPHP\CarbonPHP;
use CarbonPHP\Tables\Carbon_Location_References;
use CarbonPHP\Tables\Carbon_Locations;
use CarbonPHP\Tables\Carbon_Users as Users;
use CarbonPHP\Tables\Carbons;


/**
 * @runTestsInSeparateProcesses
 */
final class RestTest extends Config
{

    private function KeyExistsAndRemove(string $key): void
    {
        $store = [];

        Carbons::Get($store, $key, []);

        if (!empty($store)) {
            $this->assertTrue(
                Carbons::Delete($store, $key, []),
                'Rest api failed to remove the test key ' . $key
            );
        }
    }


    public function testRestApiCanPost(): void
    {
        $this->KeyExistsAndRemove('8544e3d581ba11e8942cd89ef3fc55fa'); //a
        $this->KeyExistsAndRemove('8544e3d581ba11e8942cd89ef3fc55fb'); //b

        $store = [];

        if (!empty($store)) {
            $this->assertTrue(
                Carbons::Delete($store, $store['entity_pk'], []),
                'Rest api failed to remove the test key.'
            );
        }


        // Should return a unique hex id
        $this->assertInternalType('string', $pool = Carbons::Post([
            Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fa',
            Carbons::ENTITY_TAG => self::class
        ]));
    }


    /**
     * @depends testRestApiCanPost
     */
    public function testRestApiCanGet(): void
    {
        $store = [];
        $this->assertTrue(Carbons::Get($store, '8544e3d581ba11e8942cd89ef3fc55fa', []));

        $this->assertInternalType('array', $store);

        if (!empty($store)) {
            $this->assertArrayHasKey('entity_fk', $store);
        }

        $this->assertTrue(Carbons::Get($store, null,
            [
                Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fa'
            ]));

        // This route redirects to home, thus ending in false
    }

    /**
     * @depends testRestApiCanGet
     */
    public function testRestApiCanPut(): void
    {
        $store = [];

        $this->assertTrue(Carbons::Get($store, '8544e3d581ba11e8942cd89ef3fc55fa', []));

        $this->assertArrayHasKey('entity_fk', $store);

        $this->assertTrue(
            Carbons::Put($store, $store['entity_pk'], [
                Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fb'
            ]), 'Failed Updating Records.');

        $this->assertEquals('8544e3d581ba11e8942cd89ef3fc55fb', $store['entity_pk']);

        $this->assertTrue(Carbons::Get($store, null, [
            Carbons::WHERE => [
                Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fb'
            ]
        ]));
    }

    /**
     *
     */
    public function testRestApiCanDelete(): void
    {
        $temp = [];

        $this->assertTrue(Carbons::Get($temp, null, [
            Carbons::WHERE => [
                Carbons::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fb'
            ],
            Carbons::PAGINATION => [
                Carbons::LIMIT => 1
            ]
        ]));

        $this->assertTrue(array_key_exists('entity_fk', $temp),
            'Failed asserting that ' . print_r($temp, true) . ' has the key \'entity_fk\'.');

        $this->assertTrue(
            Carbons::Delete($temp, $temp['entity_pk'], [])
        );

        $this->assertEmpty($temp);
    }

    /**
     *
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
        ]) && !empty($user)){
            $this->assertTrue(Users::Delete($user, $user[Users::COLUMNS[Users::USER_ID]], []));
        }


        $this->assertInternalType('string', $uid = Users::Post([
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

        $this->assertInternalType('string', $lid = Carbon_Locations::Post([
            Carbon_Locations::CITY => 'Grapevine',
            Carbon_Locations::STATE => 'Texas',
            Carbon_Locations::ZIP => 76051
        ]), 'Failed to create location entity.');

        $this->assertTrue(Carbon_Location_References::Post([
            Carbon_Location_References::ENTITY_REFERENCE => $uid,
            Carbon_Location_References::LOCATION_REFERENCE => $lid
        ]), 'Failed to create location references.');

        $this->commit();

        $user = [];

        Users::Get($user, $uid, [
            Users::SELECT => [
                Users::USER_USERNAME,
                Carbon_Locations::STATE
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
                Users::LIMIT => 1,
                Users::ORDER => Users::USER_USERNAME . Users::ASC
            ]
        ]);

        $this->assertArrayHasKey(Users::COLUMNS[Users::USER_USERNAME], $user);

        $this->assertEquals(Config::ADMIN_USERNAME, $user[Users::COLUMNS[Users::USER_USERNAME]]);

        $this->assertEquals('Texas', $user[Carbon_Locations::COLUMNS[Carbon_Locations::STATE]]);
    }

}