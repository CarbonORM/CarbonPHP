<?php
/**
 * Created by IntelliJ IDEA.
 * User: rmiles
 * Date: 6/26/2018
 * Time: 3:21 PM
 */

declare(strict_types=1);

namespace Tests;

use  PHPUnit\Framework\TestCase;
use CarbonPHP\Tables\Carbons as Rest;


/**
 * @runTestsInSeparateProcesses
 */
final class RestTest extends Config
{
    public $store;
    public $user_id;

    private function KeyExistsAndRemove(string $key) : void {
        $store = [];

        Rest::Get($store, $key, []);

        if (!empty($store)) {
            $this->assertTrue(
                Rest::Delete($store, $key, []),
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
                Rest::Delete($store, $store['entity_pk'], []),
                'Rest api failed to remove the test key.'
            );
        }


        // Should return a unique hex id
        $this->assertInternalType('string', $pool = Rest::Post([
            Rest::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fa'
        ]));
    }


    /**
     * @depends testRestApiCanPost
     */
    public function testRestApiCanGet(): void
    {
        $store = [];
        $this->assertTrue(Rest::Get($store, '8544e3d581ba11e8942cd89ef3fc55fa', []));

        $this->assertInternalType('array', $store);

        if (!empty($store)) {
            $this->assertArrayHasKey('entity_fk', $store);
        }

        $this->assertTrue(Rest::Get($store, null, [Rest::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fa']));


        // This route redirects to home, thus ending in false
    }

    /**
     * @depends testRestApiCanGet
     */
    public function testRestApiCanPut(): void
    {
        $this->store = [];

        $this->assertTrue(Rest::Get($this->store, '8544e3d581ba11e8942cd89ef3fc55fa', []));

        $this->assertArrayHasKey('entity_fk', $this->store);

        $this->assertTrue(
            Rest::Put($this->store, $this->store['entity_pk'], [
                REST::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fb'
            ]), 'Failed Updating Records.');

        $this->assertEquals('8544e3d581ba11e8942cd89ef3fc55fb', $this->store['entity_pk']);

        $this->assertTrue(Rest::Get($this->store, null, [
            'where' => [
                REST::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fb'
            ]
        ]));
    }

    /**
     * @depends testRestApiCanPut
     */
    public function testRestApiCanDelete(): void
    {
        $temp = [];

        $this->assertTrue(Rest::Get($temp, null, [
            'where' => [
                Rest::ENTITY_PK => '8544e3d581ba11e8942cd89ef3fc55fb'
            ],
            'pagination' => [ 'limit' => 1 ]
        ]));

        $this->assertTrue(array_key_exists('entity_fk', $temp),
            'Failed asserting that ' . print_r($temp, true) . ' has the key \'entity_fk\'.');

        $this->assertTrue(
            Rest::Delete($temp, $temp['entity_pk'], [])
        );

        $this->assertEmpty($temp);
    }
}