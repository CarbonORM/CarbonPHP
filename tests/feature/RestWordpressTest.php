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
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Tables\Wp_Users;
use PDOException;
use Throwable;

/**
 * @runTestsInSeparateProcesses
 */
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

        $db = Database::database();

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
            // an auto incrementing int should be returned
            $primary = Wp_Users::Post($data ?? [
                    Wp_Users::USER_LOGIN => 'WookieeWorking',
                    Wp_Users::USER_PASS => 'carbon',
                    Wp_Users::USER_NICENAME => 'WookieeWorking1',
                    Wp_Users::USER_EMAIL => 'support@miles.systems',
                    Wp_Users::USER_REGISTERED => date('Y-m-d H:i:s'),
                    Wp_Users::USER_URL => '',
                ]);

            self::assertGreaterThan(1, $primary, 'Failed to create a new wordpress user.');

            return $primary;

        } catch (PDOException | PublicAlert | Throwable $e) {
            sortDump($e->getMessage());
            die(1);
        }
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public static function deleteUser(string $id) : bool {
        try {
            $ignore = [];
            return Wp_Users::Delete($ignore, $id);
        } catch (PDOException | PublicAlert | Throwable $e) {
            sortDump($e->getMessage());
            die(1);
        }
    }

}