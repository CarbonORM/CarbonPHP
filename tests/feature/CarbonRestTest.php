<?php

declare(strict_types=1);

namespace Tests\Feature;

use CarbonPHP\Abstracts\Cryptography;
use CarbonPHP\Database;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Rest;
use CarbonPHP\Tables\Carbons;
use CarbonPHP\Tables\Group_References;
use CarbonPHP\Tables\History_Logs;
use CarbonPHP\Tables\Location_References;
use CarbonPHP\Tables\Locations;
use CarbonPHP\Tables\User_Sessions;
use CarbonPHP\Tables\User_Tasks;
use CarbonPHP\Tables\Users;
use CarbonPHP\Tables\Wp_Users;


class CarbonRestTest extends Config
{

    public static array $restChallenge = [];

    public static function createUser(array &$data = [], int $amount = 1): string
    {
        self::assertGreaterThan(0, $amount);

        if (empty($data)) {

            for ($i = 0; $i < $amount; $i++) {

                $data[] = [
                    Users::USER_TYPE => 'Athlete',
                    Users::USER_IP => '127.0.0.1',
                    Users::USER_SPORT => 'GOLF',
                    Users::USER_EMAIL_CONFIRMED => 1,
                    Users::USER_USERNAME => Config::ADMIN_USERNAME . Cryptography::genRandomHex(),
                    Users::USER_PASSWORD => Config::ADMIN_PASSWORD,
                    Users::USER_EMAIL => 'richard@miles.systems',
                    Users::USER_FIRST_NAME => 'Richard',
                    Users::USER_LAST_NAME => 'Miles',
                    Users::USER_GENDER => 'Male'
                ];

            }

        }

        self::assertIsString($uid = Users::Post($data), 'No string ID was returned');

        self::assertNotEmpty($data);

        return $uid;
    }


    public function testRestApiCanPostAndDelete(): void
    {

        $post = [Carbons::ENTITY_TAG => self::class];

        // Should return a unique hex id
        self::assertIsString($key = Carbons::Post($post));

        $ref = [];

        self::assertTrue(Carbons::Delete($ref, $key));

        self::assertEmpty($ref);

        self::assertTrue(Carbons::Get($ref, $key));

        self::assertEmpty($ref);

    }

    public function testRestApiCanGet(): void
    {
        $post = [
            Carbons::ENTITY_TAG => self::class
        ];

        $key = Carbons::Post($post);

        $return = [];

        self::assertTrue(Carbons::Get($return, $key));

        self::assertIsArray($return);

        self::assertNotEmpty($return);

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_FK], $return);

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_TAG], $return);

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $return);

        $return = [];

        self::assertTrue(Carbons::Get($return, null, [
            iRest::WHERE => [
                Carbons::ENTITY_TAG => self::class
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1
            ]
        ]));

        self::assertNotEmpty($return);

        self::assertTrue(Carbons::Delete($return, $key));

        self::assertEmpty($return);
    }


    public function testRestApiCanAggregate(): void
    {

        $post = [
            Carbons::ENTITY_TAG => self::class
        ];

        $key = Carbons::Post($post);

        $return = [];

        self::assertTrue(Carbons::Get($return, $key));

        $temp = [];

        self::assertTrue(Carbons::Get($temp, null, [
            iRest::WHERE => [
                Carbons::ENTITY_TAG => self::class
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1
            ]
        ]));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $temp);

        $temp = [];

        self::assertTrue(Carbons::Get($temp, null, [
            iRest::SELECT => [
                [iRest::COUNT, Carbons::ENTITY_PK, Carbons::COLUMNS[Carbons::ENTITY_PK]]
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1
            ]
        ]));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $temp, 'failed on PAGINATION:LIMIT');

        self::assertTrue(Carbons::Get($temp, null, [
            iRest::SELECT => [
                [iRest::COUNT, Carbons::ENTITY_PK, Carbons::COLUMNS[Carbons::ENTITY_PK]]
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 2   // check the limit
            ]
        ]));

        self::assertArrayHasKey(0, $temp);
        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $temp[0], 'failed on PAGINATION:LIMIT');

    }

    public function testRestApiCanPut(): void
    {
        $store = [];

        $post = [];

        self::assertNotEmpty($primary = Carbons::Post($post));

        self::assertTrue(Carbons::Get($store, $primary, []));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_FK], $store);

        self::assertTrue(
            Carbons::Put($store, $store[Carbons::COLUMNS[Carbons::ENTITY_PK]], [
                Carbons::ENTITY_TAG => $primary
            ]), 'Failed Updating Records.');

        self::assertTrue(
            Carbons::Put($store, $store[Carbons::COLUMNS[Carbons::ENTITY_TAG]], [
                Carbons::ENTITY_TAG => $goodStuff = 'GOOD STUFF'
            ]), 'Failed Updating Records With Identical Data. See https://stackoverflow.com/questions/10522520/pdo-were-rows-affected-during-execute-statement ');

        self::assertEquals($goodStuff, $store[Carbons::COLUMNS[Carbons::ENTITY_TAG]]);

        $store = [];

        self::assertTrue(Carbons::Get($store, $primary, []));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $store,
            'Failed to see updated record in database.');

    }


    public function testRestApiCanJoin(): void
    {
        $user = [];

        if (Users::Get($user, null, [
                iRest::SELECT => [
                    Users::USER_ID
                ],
                iRest::WHERE => [
                    Users::USER_USERNAME => Config::ADMIN_USERNAME
                ],
                iRest::PAGINATION => [
                    iRest::LIMIT => 1
                ]
            ]) && !empty($user)) {

            self::assertTrue(Users::Delete($user, $user[Users::COLUMNS[Users::USER_ID]], []),
                'Failed to delete user for join test.');

        }

        Rest::$commit = false;

        $uid = self::createUser();

        $post = [
            Locations::CITY => 'Grapevine',
            Locations::STATE => 'Texas',
            Locations::ZIP => 76051
        ];

        self::assertIsString($lid = Locations::Post($post), 'Failed to create location entity.');

        Rest::$commit = true; // the next post request will post

        $post = [
            Location_References::ENTITY_REFERENCE => $uid,
            Location_References::LOCATION_REFERENCE => $lid
        ];

        self::assertTrue(Location_References::Post($post), 'Failed to create location references.');

        $user = [];

        $db = Database::database(true);

        self::assertFalse($db->inTransaction(), 'Failed closing transaction');

        self::assertTrue(Users::Get($user, $uid));

        self::assertArrayHasKey(Users::COLUMNS[Users::USER_ABOUT_ME], $user);

        self::assertTrue(Users::Get($user, $uid, [
            iRest::SELECT => [
                Users::USER_USERNAME,
                Locations::STATE
            ],
            iRest::JOIN => [
                iRest::INNER => [
                    Location_References::TABLE_NAME => [
                        Users::USER_ID => Location_References::ENTITY_REFERENCE
                    ],
                    Locations::TABLE_NAME => [
                        Locations::ENTITY_ID => Location_References::LOCATION_REFERENCE
                    ]
                ]
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1,
                iRest::ORDER => [Users::USER_USERNAME => iRest::ASC]
            ]
        ]), 'Failed to run inner join.');

        self::assertArrayHasKey(Users::COLUMNS[Users::USER_USERNAME], $user);

        self::assertStringStartsWith(Config::ADMIN_USERNAME, $user[Users::COLUMNS[Users::USER_USERNAME]]);

        self::assertEquals('Texas', $user[Locations::COLUMNS[Locations::STATE]]);
    }


    public function testRestApiCanUseUserDefinedCallbacks(): void
    {
        $user = [];

        self::assertTrue(Users::Get($user, null, [
                iRest::WHERE => [
                    [Users::USER_USERNAME, iRest::LIKE, Config::ADMIN_USERNAME . '%'],
                    Users::USER_PASSWORD => Config::ADMIN_PASSWORD
                ],
                iRest::PAGINATION => [
                    iRest::LIMIT => 1
                ]
            ]
        ), 'The user could not be retrieved.');

        $uid = $user[Users::COLUMNS[Users::USER_ID]];

        self::assertNotEmpty($uid, 'The user id was empty.');

        self::assertEmpty(self::$restChallenge, 'Rest Challenges Should Start as Empty.');

        $post = [
            User_Tasks::USER_ID => $uid,
            User_Tasks::TASK_NAME => 'Hello World',
            User_Tasks::TASK_DESCRIPTION => 'Test',
            User_Tasks::PERCENT_COMPLETE => 70
        ];

        $id = User_Tasks::Post($post);

        self::assertNotEmpty($id);

        self::assertCount(11, self::$restChallenge, 'Not all rest challenges have run (' . json_encode(self::$restChallenge) . ').');

        self::assertArrayHasKey(User_Tasks::USER_ID, self::$restChallenge[0][0]);

        self::assertArrayHasKey(User_Tasks::TASK_NAME, self::$restChallenge[0][0]);

        self::assertArrayHasKey(User_Tasks::TASK_DESCRIPTION, self::$restChallenge[0][0]);

        self::assertArrayHasKey(User_Tasks::PERCENT_COMPLETE, self::$restChallenge[0][0]);

        self::assertArrayHasKey(1, self::$restChallenge[1]);

        self::assertEquals(iRest::POST, self::$restChallenge[1][2]); // start at 0 ;)

        self::assertEquals(iRest::PREPROCESS, self::$restChallenge[1][3]); // start at 0 ;)

        self::assertEquals(User_Tasks::PERCENT_COMPLETE, self::$restChallenge[7][2] ?? 'NOT SET', 'Failed to see the correct value (User_Tasks::PERCENT_COMPLETE) at [6][2] => ' . print_r(self::$restChallenge, true));

    }


    public function testRestApiCanSubQuery(): void
    {
        global $json;

        $user = [];
        self::assertTrue(Carbons::Get($user, null, [
            iRest::SELECT => [
                Carbons::ENTITY_PK
            ],
            iRest::WHERE => [
                Carbons::ENTITY_PK =>
                    Users::subSelect(null, [
                        iRest::SELECT => [
                            Users::USER_ID
                        ],
                        iRest::WHERE => [
                            [Users::USER_USERNAME, iRest::LIKE, Config::ADMIN_USERNAME . '%']
                        ],
                        iRest::PAGINATION => [
                            iRest::LIMIT => 1
                        ]
                    ])
            ],
            iRest::PAGINATION => [
                iRest::LIMIT =>
                    1
            ]
        ]));

        self::assertNotEmpty($user, 'Could not get user admin via sub query (function). ' . json_encode($json));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $user);

        // try again with ^10 syntax

        $user = [];

        Rest::$allowSubSelectQueries = true;

        self::assertTrue(Carbons::Get($user, null, [
            iRest::SELECT => [
                Carbons::ENTITY_PK
            ],
            iRest::WHERE => [
                Carbons::ENTITY_PK =>
                    [
                        iRest::SELECT,
                        Users::class,
                        null,
                        [
                            iRest::SELECT => [
                                Users::USER_ID
                            ],
                            iRest::WHERE => [
                                [Users::USER_USERNAME, iRest::LIKE, Config::ADMIN_USERNAME . '%']
                            ],
                            iRest::PAGINATION => [
                                iRest::LIMIT => 1
                            ]
                        ]
                    ]
            ],
            iRest::PAGINATION => [
                iRest::LIMIT =>
                    1
            ]
        ]));

        self::assertNotEmpty($user, 'Could not get user admin via sub query (aggregate). ' . json_encode($json));

        self::assertArrayHasKey(Carbons::COLUMNS[Carbons::ENTITY_PK], $user);

    }


    public function testCascadeDelete(): void
    {
        $user = [Users::USER_USERNAME => Config::ADMIN_USERNAME];

        self::assertTrue(Users::Delete($user, null, [
            Users::USER_USERNAME => Config::ADMIN_USERNAME
        ]));

        self::assertEmpty($user, 'Could not delete user admin in cascade delete function.');

        $id = self::createUser();

        self::assertTrue(Users::Delete($user, $id, []),
            'Test can delete by primary key.php');

        self::assertEmpty($user, 'Cascade delete failed.');
    }

    public function testRestApiCanUseNonCarbonPrimaryKeys(): void
    {
        $return = [];

        $postOne = [];

        $postTwo = [];

        $post = [
            User_Sessions::USER_ID => $USER_ID = Carbons::post($postOne),
            User_Sessions::USER_IP => '127.0.0.1',
            User_Sessions::SESSION_ID => $SESSION_ID = Carbons::post($postTwo),
            User_Sessions::SESSION_EXPIRES => date('Y-m-d H:i:s'), // @link https://stackoverflow.com/questions/2215354/php-date-format-when-inserting-into-datetime-in-mysql/17295570
            User_Sessions::SESSION_DATA => '',
            User_Sessions::USER_ONLINE_STATUS => 1
        ];

        self::assertNotFalse(User_Sessions::Post($post));

        self::assertTrue(User_Sessions::Put($return, $SESSION_ID, [
            User_Sessions::USER_IP => '127.0.0.2',
            User_Sessions::USER_ONLINE_STATUS => 0
        ]));

        // todo - check array merge
        self::assertTrue(User_Sessions::Get($return, $SESSION_ID, []));
        self::assertTrue(User_Sessions::Delete($return, $SESSION_ID));
        self::assertTrue(Carbons::Delete($return, $SESSION_ID));
        self::assertTrue(Carbons::Delete($return, $USER_ID));
    }


    public function testRestApiCanUpdateCarbonPrimaryKeysWithNoCollisions(): void
    {
        $post = [];

        self::assertNotFalse($USER_ID_ONE = Carbons::Post($post));

        $post = [Carbons::ENTITY_FK => $USER_ID_ONE];

        self::assertNotFalse($USER_ID_TWO = Carbons::Post($post));

        $post = [Carbons::ENTITY_FK => $USER_ID_TWO];

        self::assertNotFalse(Carbons::Post($post));

        $returnUpdated = [];

        $post = [];

        self::assertNotFalse(Carbons::Put($returnUpdated, $USER_ID_TWO, [Carbons::ENTITY_FK => Carbons::Post($post)]));

        $return = [];

        self::assertTrue(Carbons::Get($return, $USER_ID_ONE, []));

        self::assertEmpty($return[Carbons::COLUMNS[Carbons::ENTITY_FK]]);

    }

    public function testRestApiCanUpdateNonCarbonPrimaryKeysWithNoCollisions(): void
    {
        $return = [];

        $postOne = [];

        $postTwo = [];

        $post = [
            User_Sessions::USER_ID => $USER_ID_ONE = Carbons::Post($postOne),
            User_Sessions::USER_IP => '127.0.0.1',
            User_Sessions::SESSION_ID => $SESSION_ID_ONE = Carbons::Post($postTwo),
            User_Sessions::SESSION_EXPIRES => date('Y-m-d H:i:s'), // @link https://stackoverflow.com/questions/2215354/php-date-format-when-inserting-into-datetime-in-mysql/17295570
            User_Sessions::SESSION_DATA => '',
            User_Sessions::USER_ONLINE_STATUS => 1
        ];

        self::assertNotFalse(User_Sessions::Post($post));

        self::assertTrue(User_Sessions::Get($return, $SESSION_ID_ONE, []));

        self::assertNotEmpty($return, 'Could not query rest sessions table with id :: ' . $SESSION_ID_ONE);

        $postOne = [];

        $postTwo = [];

        $post = [
            User_Sessions::USER_ID => $USER_ID_TWO = Carbons::Post($postOne),
            User_Sessions::USER_IP => '127.0.0.1',
            User_Sessions::SESSION_ID => $SESSION_ID_TWO = Carbons::Post($postTwo),
            User_Sessions::SESSION_EXPIRES => date('Y-m-d H:i:s'), // @link https://stackoverflow.com/questions/2215354/php-date-format-when-inserting-into-datetime-in-mysql/17295570
            User_Sessions::SESSION_DATA => '',
            User_Sessions::USER_ONLINE_STATUS => 1
        ];

        self::assertNotFalse(User_Sessions::Post($post));

        self::assertTrue(User_Sessions::Put($return, $SESSION_ID_TWO, [
            User_Sessions::USER_IP => '127.0.0.2',
            User_Sessions::USER_ONLINE_STATUS => 0
        ]));

        $return = [];

        self::assertTrue(User_Sessions::Get($return, $SESSION_ID_ONE, []));

        self::assertNotEmpty($return);

        self::assertNotEquals('127.0.0.2', $return[User_Sessions::COLUMNS[User_Sessions::USER_IP]]);
        self::assertNotEquals('0', $return[User_Sessions::COLUMNS[User_Sessions::USER_ONLINE_STATUS]]);

        self::assertTrue(User_Sessions::Get($return, $SESSION_ID_TWO, []));
        self::assertNotEmpty($return);

        self::assertEquals('127.0.0.2', $return[User_Sessions::COLUMNS[User_Sessions::USER_IP]]);

        self::assertEquals('0', $return[User_Sessions::COLUMNS[User_Sessions::USER_ONLINE_STATUS]]);
        self::assertTrue(User_Sessions::Delete($return, $SESSION_ID_ONE));
        self::assertTrue(User_Sessions::Delete($return, $SESSION_ID_TWO));
        self::assertTrue(Carbons::Delete($return, $USER_ID_ONE));
        self::assertTrue(Carbons::Delete($return, $USER_ID_TWO));
    }

    public function testRestApiCanUseTablesWithNoPrimaryKey(): void
    {
        $ignore = [];

        $bin16 = Carbons::Post($ignore);

        $post = [
            Group_References::GROUP_ID => $bin16,
            Group_References::ALLOWED_TO_GRANT_GROUP_ID => $bin16,
        ];

        // Should return a unique hex id
        self::assertTrue(Group_References::Post($post));


        $return = [];

        $getGroup = static function () use ($bin16, &$return) {
            Group_References::Get($return, [
                iRest::SELECT => [
                    Group_References::GROUP_ID,
                    Group_References::ALLOWED_TO_GRANT_GROUP_ID
                ],
                iRest::WHERE => [
                    Group_References::GROUP_ID => $bin16,
                    Group_References::ALLOWED_TO_GRANT_GROUP_ID => $bin16
                ],
                iRest::PAGINATION => [
                    iRest::LIMIT => 1
                ]
            ]);
        };

        $getGroup();

        self::assertGreaterThan(0, count($return));

        $ignore = [];

        $bin16Update = Carbons::Post($ignore);

        self::assertTrue(Group_References::Put($ignore, [
            iRest::UPDATE => [
                Group_References::ALLOWED_TO_GRANT_GROUP_ID => $bin16Update
            ],
            iRest::WHERE => [
                Group_References::GROUP_ID => $bin16,
                Group_References::ALLOWED_TO_GRANT_GROUP_ID => $bin16
            ]]));

        $getGroup();

        self::assertEquals(0, count($return));

        self::assertTrue(Group_References::Delete($ignore, [
            iRest::WHERE => [
                Group_References::GROUP_ID => $bin16,
                Group_References::ALLOWED_TO_GRANT_GROUP_ID => $bin16Update
            ]]));


    }

    public function testRestApiCanUseJson(): void
    {
        $ignore = [];

        self::assertTrue(History_Logs::Delete($ignore, null, [
            History_Logs::HISTORY_TABLE => self::class
        ]));

        $post = [
            History_Logs::HISTORY_REQUEST => ['Json' => 'is cool'],
            History_Logs::HISTORY_TABLE => self::class,
            History_Logs::HISTORY_UUID => Carbons::Post($ignore)
        ];

        self::assertNotFalse($logId = History_Logs::post($post));

        // Should return a unique hex id
        self::assertTrue(History_Logs::Put($ignore, $logId, [
            iRest::UPDATE => [
                History_Logs::HISTORY_REQUEST => ['Json' => 'is fun'],
            ],
            iRest::WHERE => [
                History_Logs::HISTORY_TABLE => self::class,
            ]
        ]));

        $return = [];

        self::assertTrue(History_Logs::get($return, null, [
            iRest::WHERE => [
                History_Logs::HISTORY_TABLE => self::class
            ],
            iRest::PAGINATION => [
                iRest::LIMIT => 1,
                iRest::ORDER => [History_Logs::HISTORY_TIME => iRest::ASC]
            ]
        ]));

        self::assertTrue(History_Logs::Delete($ignore, null, [
            History_Logs::HISTORY_UUID => $logId
        ]));

        self::assertGreaterThan(1, $return);

    }

    public function testRestApiCanPostMultipleCarbons(): void
    {

        $post = [
            [],
            [],
            [],
            []
        ];

        self::assertIsString(Carbons::post($post));

    }

    public function testRestApiCanPostMultipleCarbonEnabled(): void
    {

        $data = [];

        //$first_id =
        self::createUser($data, 10);

        self::assertNotEmpty($data);


        self::assertCount(10, $data);

    }

    public function testRestApiCanPostMultipleNonCarbonPrimaryKey(): void
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                Wp_Users::USER_REGISTERED => $now,
            ],
            [
                Wp_Users::USER_REGISTERED => $now,
            ],
            [
                Wp_Users::USER_REGISTERED => $now,
            ],
            [
                Wp_Users::USER_REGISTERED => $now,
            ]
        ];

        define('WP_DEBUG_TEST', true);

        $id = Wp_Users::post($data);

        //$first_id =
        self::assertIsNumeric($id);

        self::assertNotEmpty($data);

        self::assertCount(4, $data);

    }

}


