<?php
/**
 * Created by IntelliJ IDEA.
 * User: rmiles
 * Date: 6/26/2018
 * Time: 3:21 PM
 */

declare(strict_types=1);

namespace Tests\Feature;

use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Tables\Users;


final class UserTest extends Config
{

    public array $user = [];

    /**
     * Ideally this is run with a fresh build. If not, the relation between create new users
     * must depend on can be deleted. This is cyclic and can not be annotated.
     * @throws PublicAlert
     */
    public function setUp(): void /* The :void return type declaration that should be here would cause a BC issue */
    {
        parent::setUp();

        $_SERVER['REQUEST_TIME'] = time();

        $this->user = [];

        Users::Get($this->user, null, [
            iRest::WHERE => [
                Users::USER_USERNAME => Config::ADMIN_USERNAME
            ]
        ]);
    }


    /**
     * @return string
     * @throws PublicAlert
     */
    public function testUserCanBeCreated(): string
    {
        if (!empty($this->user)) {
            $this->testUserCanBeDeleted();
        }

        $post = [
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
        ];

        self::assertIsString($id = Users::Post($post), 'No string ID was returned');

        return $id;
    }


    /**
     * @depends testUserCanBeCreated
     */
    public function testUserCanBeRetrieved(): void
    {
        $this->user = [];
        self::assertTrue(
            Users::Get($this->user, null, [
                    iRest::WHERE => [
                        Users::USER_USERNAME => Config::ADMIN_USERNAME
                    ],
                    iRest::PAGINATION => [
                        iRest::LIMIT => 1
                    ]
                ]
            ));

        self::assertIsArray($this->user);

        self::assertArrayHasKey(
            Users::COLUMNS[Users::USER_EMAIL],
            $this->user);

    }

    /**
     * @depends testUserCanBeRetrieved
     */
    public function testUserCanBeUpdated(): void
    {
        self::assertTrue(
            Users::Get($this->user, null, [
                    iRest::WHERE => [
                        Users::USER_USERNAME => Config::ADMIN_USERNAME
                    ]
                ]
            ));

        $this->user = $this->user[0];

        self::assertTrue(
            Users::Put($this->user,
                $this->user[Users::COLUMNS[Users::USER_ID]],
                [
                    Users::USER_FIRST_NAME => 'lil\'Rich'
                ]));

        $this->user = [];

        self::assertTrue(
            Users::Get($this->user, null, [
                    iRest::WHERE => [
                        Users::USER_USERNAME => Config::ADMIN_USERNAME
                    ],
                    iRest::PAGINATION => [
                        iRest::LIMIT => 1
                    ]
                ]
            ));

        self::assertEquals('lil\'Rich',
            $this->user[Users::COLUMNS[Users::USER_FIRST_NAME]]);
    }


    /**
     * @depends testUserCanBeRetrieved
     */
    public function testUserCanBeDeleted(): void
    {
        global $json;

        $this->user = [];

        Users::Get($this->user, null, [
            iRest::WHERE => [
                Users::USER_USERNAME => Config::ADMIN_USERNAME
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1
            ]
        ]);

        self::assertNotEmpty($this->user,
            'User (' . Config::ADMIN_USERNAME . ') does not appear to exist.');

        self::assertTrue(
            Users::Delete($this->user,
                $this->user[Users::COLUMNS[Users::USER_ID]], [])
        );

        self::assertEmpty($this->user);

        $this->user = [];

        Users::Get($this->user, null, [
            iRest::WHERE => [
                Users::USER_USERNAME => Config::ADMIN_USERNAME
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1
            ]
        ]);

        self::assertEmpty($this->user, 'Failed asserting delete removed the user :: ' . json_encode($json));

    }

}