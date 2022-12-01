<?php
/**
 * Created by IntelliJ IDEA.
 * User: rmiles
 * Date: 6/26/2018
 * Time: 3:21 PM
 */

declare(strict_types=1);

namespace Tests\Feature;

use CarbonPHP\Database;
use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Tables\Wp_Users;
use Throwable;


final class RestWordpressTest extends Config
{
    public function testRestInternalPostAndDelete(): void
    {
        self::assertGreaterThan(1, $newUserId = self::createUser());

        self::assertTrue(self::deleteUser($newUserId), 'Failed to delete user.');
    }

    public function testRestCanSimpleSelectWordpressUser(): void
    {
        $return = [];

        self::assertTrue(Wp_Users::Get($return, '1', []));

        self::assertArrayHasKey(Wp_Users::COLUMNS[Wp_Users::USER_PASS], $return);
    }


    public function testRestInternalSelectAndUpdate(): void
    {
        self::assertGreaterThan(1, $primary = self::createUser());

        $db = Database::database(false);

        self::assertFalse($db->inTransaction(), 'Transaction did not commit');

        $return = [];

        self::assertTrue(Wp_Users::Get($return, $primary));

        self::assertArrayHasKey(Wp_Users::COLUMNS[Wp_Users::USER_NICENAME], $return);

        self::assertArrayHasKey(Wp_Users::COLUMNS[Wp_Users::USER_LOGIN], $return);

        $returnUpdated = [];

        self::assertTrue(Wp_Users::Put($returnUpdated, $primary, [
            Wp_Users::USER_LOGIN => $actual = 'Wookiee'
        ]));

        self::assertArrayHasKey(Wp_Users::COLUMNS[Wp_Users::USER_LOGIN], $return);

        self::assertEquals($returnUpdated[Wp_Users::COLUMNS[Wp_Users::USER_LOGIN]], $actual);

        self::assertNotEquals($returnUpdated[Wp_Users::COLUMNS[Wp_Users::USER_LOGIN]], $return[Wp_Users::COLUMNS[Wp_Users::USER_LOGIN]]);

        self::assertTrue(self::deleteUser($primary), 'Failed to delete user.');
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public static function createUser(array $data = null) : string {
        try {
            $data ??= [
                Wp_Users::USER_LOGIN => 'WookieeWorking',
                Wp_Users::USER_PASS => 'carbon',
                Wp_Users::USER_NICENAME => 'WookieeWorking1',
                Wp_Users::USER_EMAIL => 'support@miles.systems',
                Wp_Users::USER_REGISTERED => date('Y-m-d H:i:s'),
                Wp_Users::USER_URL => '',
            ];

            // an auto incrementing int should be returned
            $primary = Wp_Users::Post($data);

            self::assertGreaterThan(1, $primary, 'Failed to create a new wordpress user.');

            return $primary;

        } catch (Throwable $e) {
            ThrowableHandler::generateLog($e);
            die(1);
        }
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public static function deleteUser(string $id) : bool {
        try {
            $ignore = [];

            return Wp_Users::Delete($ignore, $id);

        } catch (Throwable $e) {

            ThrowableHandler::generateLog($e);

            die(1);
        }
    }

}