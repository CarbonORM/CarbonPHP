<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use CarbonPHP\Database;
use CarbonPHP\Error\PublicAlert;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Rest;
use PDO;
use PDOException;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

// Custom User Imports
use CarbonPHP\Programs\ColorCode;
use CarbonPHP\Helpers\RestfulValidation;

/**
 * 
 * Class Carbon_Users
 * @package CarbonPHP\Tables
 * 
 */
class Carbon_Users extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbon_Users';
    public const CLASS_NAMESPACE = 'CarbonPHP\Tables\\';
    public const TABLE_NAME = 'carbon_users';
    public const TABLE_PREFIX = '';
    
    public const USER_USERNAME = 'carbon_users.user_username'; 
    public const USER_PASSWORD = 'carbon_users.user_password'; 
    public const USER_ID = 'carbon_users.user_id'; 
    public const USER_TYPE = 'carbon_users.user_type'; 
    public const USER_SPORT = 'carbon_users.user_sport'; 
    public const USER_SESSION_ID = 'carbon_users.user_session_id'; 
    public const USER_FACEBOOK_ID = 'carbon_users.user_facebook_id'; 
    public const USER_FIRST_NAME = 'carbon_users.user_first_name'; 
    public const USER_LAST_NAME = 'carbon_users.user_last_name'; 
    public const USER_PROFILE_PIC = 'carbon_users.user_profile_pic'; 
    public const USER_PROFILE_URI = 'carbon_users.user_profile_uri'; 
    public const USER_COVER_PHOTO = 'carbon_users.user_cover_photo'; 
    public const USER_BIRTHDAY = 'carbon_users.user_birthday'; 
    public const USER_GENDER = 'carbon_users.user_gender'; 
    public const USER_ABOUT_ME = 'carbon_users.user_about_me'; 
    public const USER_RANK = 'carbon_users.user_rank'; 
    public const USER_EMAIL = 'carbon_users.user_email'; 
    public const USER_EMAIL_CODE = 'carbon_users.user_email_code'; 
    public const USER_EMAIL_CONFIRMED = 'carbon_users.user_email_confirmed'; 
    public const USER_GENERATED_STRING = 'carbon_users.user_generated_string'; 
    public const USER_MEMBERSHIP = 'carbon_users.user_membership'; 
    public const USER_DEACTIVATED = 'carbon_users.user_deactivated'; 
    public const USER_LAST_LOGIN = 'carbon_users.user_last_login'; 
    public const USER_IP = 'carbon_users.user_ip'; 
    public const USER_EDUCATION_HISTORY = 'carbon_users.user_education_history'; 
    public const USER_LOCATION = 'carbon_users.user_location'; 
    public const USER_CREATION_DATE = 'carbon_users.user_creation_date'; 

    public const PRIMARY = [
        'carbon_users.user_id',
    ];

    public const COLUMNS = [
        'carbon_users.user_username' => 'user_username','carbon_users.user_password' => 'user_password','carbon_users.user_id' => 'user_id','carbon_users.user_type' => 'user_type','carbon_users.user_sport' => 'user_sport','carbon_users.user_session_id' => 'user_session_id','carbon_users.user_facebook_id' => 'user_facebook_id','carbon_users.user_first_name' => 'user_first_name','carbon_users.user_last_name' => 'user_last_name','carbon_users.user_profile_pic' => 'user_profile_pic','carbon_users.user_profile_uri' => 'user_profile_uri','carbon_users.user_cover_photo' => 'user_cover_photo','carbon_users.user_birthday' => 'user_birthday','carbon_users.user_gender' => 'user_gender','carbon_users.user_about_me' => 'user_about_me','carbon_users.user_rank' => 'user_rank','carbon_users.user_email' => 'user_email','carbon_users.user_email_code' => 'user_email_code','carbon_users.user_email_confirmed' => 'user_email_confirmed','carbon_users.user_generated_string' => 'user_generated_string','carbon_users.user_membership' => 'user_membership','carbon_users.user_deactivated' => 'user_deactivated','carbon_users.user_last_login' => 'user_last_login','carbon_users.user_ip' => 'user_ip','carbon_users.user_education_history' => 'user_education_history','carbon_users.user_location' => 'user_location','carbon_users.user_creation_date' => 'user_creation_date',
    ];

    public const PDO_VALIDATION = [
        'carbon_users.user_username' => ['varchar', 'PDO::PARAM_STR', '100'],'carbon_users.user_password' => ['varchar', 'PDO::PARAM_STR', '225'],'carbon_users.user_id' => ['binary', 'PDO::PARAM_STR', '16'],'carbon_users.user_type' => ['varchar', 'PDO::PARAM_STR', '20'],'carbon_users.user_sport' => ['varchar', 'PDO::PARAM_STR', '20'],'carbon_users.user_session_id' => ['varchar', 'PDO::PARAM_STR', '225'],'carbon_users.user_facebook_id' => ['varchar', 'PDO::PARAM_STR', '225'],'carbon_users.user_first_name' => ['varchar', 'PDO::PARAM_STR', '25'],'carbon_users.user_last_name' => ['varchar', 'PDO::PARAM_STR', '25'],'carbon_users.user_profile_pic' => ['varchar', 'PDO::PARAM_STR', '225'],'carbon_users.user_profile_uri' => ['varchar', 'PDO::PARAM_STR', '225'],'carbon_users.user_cover_photo' => ['varchar', 'PDO::PARAM_STR', '225'],'carbon_users.user_birthday' => ['varchar', 'PDO::PARAM_STR', '9'],'carbon_users.user_gender' => ['varchar', 'PDO::PARAM_STR', '25'],'carbon_users.user_about_me' => ['varchar', 'PDO::PARAM_STR', '225'],'carbon_users.user_rank' => ['int', 'PDO::PARAM_INT', ''],'carbon_users.user_email' => ['varchar', 'PDO::PARAM_STR', '50'],'carbon_users.user_email_code' => ['varchar', 'PDO::PARAM_STR', '225'],'carbon_users.user_email_confirmed' => ['tinyint', 'PDO::PARAM_INT', '1'],'carbon_users.user_generated_string' => ['varchar', 'PDO::PARAM_STR', '200'],'carbon_users.user_membership' => ['int', 'PDO::PARAM_INT', ''],'carbon_users.user_deactivated' => ['tinyint', 'PDO::PARAM_INT', '1'],'carbon_users.user_last_login' => ['datetime', 'PDO::PARAM_STR', ''],'carbon_users.user_ip' => ['varchar', 'PDO::PARAM_STR', '20'],'carbon_users.user_education_history' => ['varchar', 'PDO::PARAM_STR', '200'],'carbon_users.user_location' => ['varchar', 'PDO::PARAM_STR', '20'],'carbon_users.user_creation_date' => ['datetime', 'PDO::PARAM_STR', ''],
    ];
     
    /**
     * PHP validations works as follows:
     *  The first index '0' of PHP_VALIDATIONS will run after REGEX_VALIDATION's but
     *  before every other validation method described here below.
     *  The other index positions are respective to the request method calling the ORM
     *  or column which maybe present in the request.
     *  Column names using the 1 to 1 constants in the class maybe used for global
     *  specific methods when under PHP_VALIDATION, or method specific operations when under
     *  its respective request method, which only run when the column is requested or acted on.
     *  Global functions and method specific functions will receive the full request which
     *  maybe acted on by reference. All column specific validation methods will only receive
     *  the associated value given in the request which may also be received by reference.
     *  All methods MUST be declared as static.
     */
 
    public const PHP_VALIDATION = []; 
 
    public const REGEX_VALIDATION = [
        // 1 to 1 regular expressions to match on every post request.
        self::USER_ID => self::VALIDATE_C6_ENTITY_ID_REGEX,
        self::USER_USERNAME => "#^[A-Za-z0-9_-]{4,16}#",
    ]; 
 
    public const REFRESH_SCHEMA = [
        [self::class => 'tableExistsOrExecuteSQL', self::TABLE_NAME, self::CREATE_TABLE_SQL]
    ]; 
   
    public const CREATE_TABLE_SQL = /** @lang MySQL */ <<<MYSQL
    CREATE TABLE `carbon_users` (
  `user_username` varchar(100) NOT NULL,
  `user_password` varchar(225) NOT NULL,
  `user_id` binary(16) NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'Athlete',
  `user_sport` varchar(20) DEFAULT 'GOLF',
  `user_session_id` varchar(225) DEFAULT NULL,
  `user_facebook_id` varchar(225) DEFAULT NULL,
  `user_first_name` varchar(25) NOT NULL,
  `user_last_name` varchar(25) NOT NULL,
  `user_profile_pic` varchar(225) DEFAULT NULL,
  `user_profile_uri` varchar(225) DEFAULT NULL,
  `user_cover_photo` varchar(225) DEFAULT NULL,
  `user_birthday` varchar(9) DEFAULT NULL,
  `user_gender` varchar(25) DEFAULT NULL,
  `user_about_me` varchar(225) DEFAULT NULL,
  `user_rank` int DEFAULT '0',
  `user_email` varchar(50) NOT NULL,
  `user_email_code` varchar(225) DEFAULT NULL,
  `user_email_confirmed` tinyint(1) DEFAULT '0' COMMENT 'need to change to enums, but no support in rest yet\n',
  `user_generated_string` varchar(200) DEFAULT NULL,
  `user_membership` int DEFAULT '0',
  `user_deactivated` tinyint(1) DEFAULT '0',
  `user_last_login` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_ip` varchar(20) NOT NULL,
  `user_education_history` varchar(200) DEFAULT NULL,
  `user_location` varchar(20) DEFAULT NULL,
  `user_creation_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `carbon_users_user_username_uindex` (`user_username`),
  UNIQUE KEY `user_user_profile_uri_uindex` (`user_profile_uri`),
  UNIQUE KEY `carbon_users_user_facebook_id_uindex` (`user_facebook_id`),
  CONSTRAINT `user_entity_entity_pk_fk` FOREIGN KEY (`user_id`) REFERENCES `carbons` (`entity_pk`) ON DELETE CASCADE ON UPDATE CASCADE
)  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
MYSQL;
   
   
    /**
     * @param array $request
     * @param string|null $column
     * @param string $value
     * @throws PublicAlert
     */
    public static function addToEveryUserRequest(array &$request, string $column = null, string $value = 'World')
    {
        ColorCode::colorCode(PHP_EOL . 'A request to the users database was made. ' . ($column ? "Column $column was requested." : 'Hello ' . $value));
    }

    public static function addToRequestExample(array &$request, string $column, string $value = 'world'): void
    {
        $request[$column] = $value;
    }

    public static function failRequest(): bool
    {
        return false;
    }
    
    /**
     * @deprecated Use the class constant CREATE_TABLE_SQL directly
     * @return string
     */
    public static function createTableSQL() : string {
        return self::CREATE_TABLE_SQL;
    }
    
    /**
    * Currently nested aggregation is not supported. It is recommended to avoid using 'AS' where possible. Sub-selects are 
    * allowed and do support 'as' aggregation. Refer to the static subSelect method parameters in the parent `Rest` class.
    * All supported aggregation is listed in the example below. Note while the WHERE and JOIN members are syntactically 
    * similar, and are moreover compiled through the same method, our aggregation is not. Please refer to this example 
    * when building your queries. By design, queries using subSelect are only allowed internally. Public Sub-Selects may 
    * be given an optional argument with future releases but will never default to on. Thus, you external API validation
    * need only validate for possible table joins. In many cases sub-selects can be replaces using simple joins, this is
    * highly recommended.
    *
    *   $argv = [
    *       Rest::SELECT => [
    *              'table_name.column_name',
    *              self::EXAMPLE_COLUMN_ONE,
    *              [self::EXAMPLE_COLUMN_TWO, self::AS, 'customName'],
    *              [self::GROUP_CONCAT, self::EXAMPLE_COLUMN_THREE], 
    *              [self::MAX, self::EXAMPLE_COLUMN_FOUR], 
    *              [self::MIN, self::EXAMPLE_COLUMN_FIVE], 
    *              [self::SUM, self::EXAMPLE_COLUMN_SIX], 
    *              [self::DISTINCT, self::EXAMPLE_COLUMN_SEVEN], 
    *              ANOTHER_EXAMPLE_TABLE::subSelect($primary, $argv, $as, $pdo, $database)
    *       ],
    *       Rest::WHERE => [
    *              
    *              self::EXAMPLE_COLUMN_NINE => 'Value To Constrain',                       // self::EXAMPLE_COLUMN_NINE AND           
    *              'Defaults to boolean AND grouping' => 'Nesting array switches to OR',    // ''='' AND 
    *              [
    *                  'Column Name' => 'Value To Constrain',                                  // ''='' OR
    *                  'This array is OR'ed together' => 'Another sud array would `AND`'       // ''=''
    *                  [ etc... ]
    *              ],
    *              'last' => 'whereExample'                                                  // AND '' = ''
    *        ],
    *        Rest::JOIN => [
    *            Rest::INNER => [
    *                Carbon_Users::CLASS_NAME => [
    *                    'Column Name' => 'Value To Constrain',
    *                    'Defaults to AND' => 'Nesting array switches to OR',
    *                    [
    *                       'Column Name' => 'Value To Constrain',
    *                       'This array is OR'ed together' => 'value'
    *                       [ 'Another sud array would `AND`ed... ]
    *                    ],
    *                    [ 'Column Name', Rest::LESS_THAN, 'Another Column Name']           // NOTE the Rest::LESS_THAN
    *                ]
    *            ],
    *            Rest::LEFT_OUTER => [
    *                Example_Table::CLASS_NAME => [
    *                    Location::USER_ID => Users::ID,
    *                    Location_References::ENTITY_KEY => $custom_var,
    *                   
    *                ],
    *                Example_Table_Two::CLASS_NAME => [
    *                    Example_Table_Two::ID => Example_Table_Two::subSelect($primary, $argv, $as, $pdo, $database)
    *                    ect... 
    *                ]
    *            ]
    *        ],
    *        Rest::PAGINATION => [
    *              Rest::LIMIT => (int) 90, // The maximum number of rows to return,
    *                       setting the limit explicitly to 1 will return a key pair array of only the
    *                       singular result. SETTING THE LIMIT TO NULL WILL ALLOW INFINITE RESULTS (NO LIMIT).
    *                       The limit defaults to 100 by design.
    *
    *               Rest::ORDER => [self::EXAMPLE_COLUMN_TEN => Rest::ASC ],  // i.e.  'username' => Rest::DESC
    *         ],
    *
    *   ];
    *
    *
    * @param array $return
    * @param string|null $primary
    * @param array $argv
    * @throws PublicAlert|PDOException
    * @return bool
    */
    public static function Get(array &$return, string $primary = null, array $argv = []): bool
    {
        self::startRest(self::GET, $argv);

        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
        self::postpreprocessRestRequest($sql);
        
        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Failed to execute the query on Carbon_Users.', 'danger');
        }

        $return = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::PDO_VALIDATION
        */

        
        if ($primary !== null || (isset($argv[self::PAGINATION][self::LIMIT]) && $argv[self::PAGINATION][self::LIMIT] === 1 && count($return) === 1)) {
            $return = isset($return[0]) && is_array($return[0]) ? $return[0] : $return;
            // promise this is needed and will still return the desired array except for a single record will not be an array
        }
        
        

        self::postprocessRestRequest($return);
        self::completeRest();
        return true;
    }

    /**
     * @param array $argv
     * @param string|null $dependantEntityId - a C6 Hex entity key 
     * @return bool|string|mixed
     * @throws PublicAlert|PDOException
     */
    public static function Post(array $argv, string $dependantEntityId = null)
    {   
        self::startRest(self::POST, $argv);
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)) {
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_users (user_username, user_password, user_id, user_type, user_sport, user_session_id, user_facebook_id, user_first_name, user_last_name, user_profile_pic, user_profile_uri, user_cover_photo, user_birthday, user_gender, user_about_me, user_rank, user_email, user_email_code, user_email_confirmed, user_generated_string, user_membership, user_deactivated, user_ip, user_education_history, user_location) VALUES ( :user_username, :user_password, UNHEX(:user_id), :user_type, :user_sport, :user_session_id, :user_facebook_id, :user_first_name, :user_last_name, :user_profile_pic, :user_profile_uri, :user_cover_photo, :user_birthday, :user_gender, :user_about_me, :user_rank, :user_email, :user_email_code, :user_email_confirmed, :user_generated_string, :user_membership, :user_deactivated, :user_ip, :user_education_history, :user_location)';


        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = self::database()->prepare($sql);
        
        
        
        
        if (!array_key_exists('carbon_users.user_username', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_username" is missing from the request.', 'danger');
        }
        $user_username = $argv['carbon_users.user_username'];
        $ref='carbon_users.user_username';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_username)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_username\'.');
        }
        $stmt->bindParam(':user_username',$user_username, PDO::PARAM_STR, 100);
        
        
        
        
        
        if (!array_key_exists('carbon_users.user_password', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_password" is missing from the request.', 'danger');
        }
        $user_password = $argv['carbon_users.user_password'];
        $ref='carbon_users.user_password';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_password)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_password\'.');
        }
        $stmt->bindParam(':user_password',$user_password, PDO::PARAM_STR, 225);
                
        $user_id = $id = $argv['carbon_users.user_id'] ?? false;
        if ($id === false) {
             $user_id = $id = self::beginTransaction(self::class, $dependantEntityId);
        } else {
           $ref='carbon_users.user_id';
           $op = self::EQUAL;
           if (!self::validateInternalColumn(self::POST, $ref, $op, $user_id)) {
             throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_id\'.');
           }            
        }
        $stmt->bindParam(':user_id',$user_id, PDO::PARAM_STR, 16);
        
        
        
        
        
        
        $user_type = $argv['carbon_users.user_type'] ?? 'Athlete';
        $ref='carbon_users.user_type';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_type, $user_type === 'Athlete')) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_type\'.');
        }
        $stmt->bindParam(':user_type',$user_type, PDO::PARAM_STR, 20);
        
        
        
        
        $user_sport = $argv['carbon_users.user_sport'] ?? 'GOLF';
        $ref='carbon_users.user_sport';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_sport, $user_sport === 'GOLF')) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_sport\'.');
        }
        $stmt->bindParam(':user_sport',$user_sport, PDO::PARAM_STR, 20);
        
        
        
        
        $user_session_id = $argv['carbon_users.user_session_id'] ?? null;
        $ref='carbon_users.user_session_id';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_session_id, $user_session_id === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_session_id\'.');
        }
        $stmt->bindParam(':user_session_id',$user_session_id, PDO::PARAM_STR, 225);
        
        
        
        
        $user_facebook_id = $argv['carbon_users.user_facebook_id'] ?? null;
        $ref='carbon_users.user_facebook_id';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_facebook_id, $user_facebook_id === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_facebook_id\'.');
        }
        $stmt->bindParam(':user_facebook_id',$user_facebook_id, PDO::PARAM_STR, 225);
        
        
        
        
        
        if (!array_key_exists('carbon_users.user_first_name', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_first_name" is missing from the request.', 'danger');
        }
        $user_first_name = $argv['carbon_users.user_first_name'];
        $ref='carbon_users.user_first_name';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_first_name)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_first_name\'.');
        }
        $stmt->bindParam(':user_first_name',$user_first_name, PDO::PARAM_STR, 25);
        
        
        
        
        
        if (!array_key_exists('carbon_users.user_last_name', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_last_name" is missing from the request.', 'danger');
        }
        $user_last_name = $argv['carbon_users.user_last_name'];
        $ref='carbon_users.user_last_name';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_last_name)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_last_name\'.');
        }
        $stmt->bindParam(':user_last_name',$user_last_name, PDO::PARAM_STR, 25);
        
        
        
        
        $user_profile_pic = $argv['carbon_users.user_profile_pic'] ?? null;
        $ref='carbon_users.user_profile_pic';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_profile_pic, $user_profile_pic === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_profile_pic\'.');
        }
        $stmt->bindParam(':user_profile_pic',$user_profile_pic, PDO::PARAM_STR, 225);
        
        
        
        
        $user_profile_uri = $argv['carbon_users.user_profile_uri'] ?? null;
        $ref='carbon_users.user_profile_uri';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_profile_uri, $user_profile_uri === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_profile_uri\'.');
        }
        $stmt->bindParam(':user_profile_uri',$user_profile_uri, PDO::PARAM_STR, 225);
        
        
        
        
        $user_cover_photo = $argv['carbon_users.user_cover_photo'] ?? null;
        $ref='carbon_users.user_cover_photo';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_cover_photo, $user_cover_photo === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_cover_photo\'.');
        }
        $stmt->bindParam(':user_cover_photo',$user_cover_photo, PDO::PARAM_STR, 225);
        
        
        
        
        $user_birthday = $argv['carbon_users.user_birthday'] ?? null;
        $ref='carbon_users.user_birthday';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_birthday, $user_birthday === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_birthday\'.');
        }
        $stmt->bindParam(':user_birthday',$user_birthday, PDO::PARAM_STR, 9);
        
        
        
        
        $user_gender = $argv['carbon_users.user_gender'] ?? null;
        $ref='carbon_users.user_gender';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_gender, $user_gender === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_gender\'.');
        }
        $stmt->bindParam(':user_gender',$user_gender, PDO::PARAM_STR, 25);
        
        
        
        
        $user_about_me = $argv['carbon_users.user_about_me'] ?? null;
        $ref='carbon_users.user_about_me';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_about_me, $user_about_me === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_about_me\'.');
        }
        $stmt->bindParam(':user_about_me',$user_about_me, PDO::PARAM_STR, 225);
        
        
                        
        $user_rank = $argv['carbon_users.user_rank'] ?? '0';
        $ref='carbon_users.user_rank';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_rank, $user_rank === '0')) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_rank\'.');
        }
        $stmt->bindValue(':user_rank', $user_rank, PDO::PARAM_INT);
        
        
        
        
        
        
        if (!array_key_exists('carbon_users.user_email', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_email" is missing from the request.', 'danger');
        }
        $user_email = $argv['carbon_users.user_email'];
        $ref='carbon_users.user_email';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_email)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_email\'.');
        }
        $stmt->bindParam(':user_email',$user_email, PDO::PARAM_STR, 50);
        
        
        
        
        $user_email_code = $argv['carbon_users.user_email_code'] ?? null;
        $ref='carbon_users.user_email_code';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_email_code, $user_email_code === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_email_code\'.');
        }
        $stmt->bindParam(':user_email_code',$user_email_code, PDO::PARAM_STR, 225);
        
        
        
        
        $user_email_confirmed = $argv['carbon_users.user_email_confirmed'] ?? '0';
        $ref='carbon_users.user_email_confirmed';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_email_confirmed, $user_email_confirmed === '0')) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_email_confirmed\'.');
        }
        $stmt->bindParam(':user_email_confirmed',$user_email_confirmed, PDO::PARAM_INT, 1);
        
        
        
        
        $user_generated_string = $argv['carbon_users.user_generated_string'] ?? null;
        $ref='carbon_users.user_generated_string';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_generated_string, $user_generated_string === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_generated_string\'.');
        }
        $stmt->bindParam(':user_generated_string',$user_generated_string, PDO::PARAM_STR, 200);
        
        
                        
        $user_membership = $argv['carbon_users.user_membership'] ?? '0';
        $ref='carbon_users.user_membership';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_membership, $user_membership === '0')) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_membership\'.');
        }
        $stmt->bindValue(':user_membership', $user_membership, PDO::PARAM_INT);
        
        
        
        
        
        $user_deactivated = $argv['carbon_users.user_deactivated'] ?? '0';
        $ref='carbon_users.user_deactivated';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_deactivated, $user_deactivated === '0')) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_deactivated\'.');
        }
        $stmt->bindParam(':user_deactivated',$user_deactivated, PDO::PARAM_INT, 1);
        
        
        
        
        
        
        
        if (!array_key_exists('carbon_users.user_ip', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_ip" is missing from the request.', 'danger');
        }
        $user_ip = $argv['carbon_users.user_ip'];
        $ref='carbon_users.user_ip';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_ip)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_ip\'.');
        }
        $stmt->bindParam(':user_ip',$user_ip, PDO::PARAM_STR, 20);
        
        
        
        
        $user_education_history = $argv['carbon_users.user_education_history'] ?? null;
        $ref='carbon_users.user_education_history';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_education_history, $user_education_history === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_education_history\'.');
        }
        $stmt->bindParam(':user_education_history',$user_education_history, PDO::PARAM_STR, 200);
        
        
        
        
        $user_location = $argv['carbon_users.user_location'] ?? null;
        $ref='carbon_users.user_location';
        $op = self::EQUAL;
        if (!self::validateInternalColumn(self::POST, $ref, $op, $user_location, $user_location === null)) {
            throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.user_location\'.');
        }
        $stmt->bindParam(':user_location',$user_location, PDO::PARAM_STR, 20);
        
        
        

        if ($stmt->execute()) {
            self::prepostprocessRestRequest($id);
             
            if (self::$commit && !Database::commit()) {
               throw new PublicAlert('Failed to store commit transaction on table carbon_users');
            } 
             
            self::postprocessRestRequest($id); 
             
            self::completeRest(); 
            
            return $id; 
        } 
       
        self::completeRest();
        return false;
    }
    
    /**
    * @param array $return
    * @param string $primary
    * @param array $argv
    * @throws PublicAlert|PDOException
    * @return bool
    */
    public static function Put(array &$return, string $primary, array $argv) : bool
    {
        self::startRest(self::PUT, $argv);
        
        if ('' === $primary) {
            throw new PublicAlert('Restful tables which have a primary key must be updated by its primary key.', 'danger');
        }
        
        if (array_key_exists(self::UPDATE, $argv)) {
            $argv = $argv[self::UPDATE];
        }
        
        foreach ($argv as $key => &$value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.', 'danger');
            }
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $key, $op, $value)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_users.\'.');
            }
        }
        unset($value);

        $sql = /** @lang MySQLFragment */ 'UPDATE carbon_users SET '; // intellij cant handle this otherwise

        $set = '';

        if (array_key_exists('carbon_users.user_username', $argv)) {
            $set .= 'user_username=:user_username,';
        }
        if (array_key_exists('carbon_users.user_password', $argv)) {
            $set .= 'user_password=:user_password,';
        }
        if (array_key_exists('carbon_users.user_id', $argv)) {
            $set .= 'user_id=UNHEX(:user_id),';
        }
        if (array_key_exists('carbon_users.user_type', $argv)) {
            $set .= 'user_type=:user_type,';
        }
        if (array_key_exists('carbon_users.user_sport', $argv)) {
            $set .= 'user_sport=:user_sport,';
        }
        if (array_key_exists('carbon_users.user_session_id', $argv)) {
            $set .= 'user_session_id=:user_session_id,';
        }
        if (array_key_exists('carbon_users.user_facebook_id', $argv)) {
            $set .= 'user_facebook_id=:user_facebook_id,';
        }
        if (array_key_exists('carbon_users.user_first_name', $argv)) {
            $set .= 'user_first_name=:user_first_name,';
        }
        if (array_key_exists('carbon_users.user_last_name', $argv)) {
            $set .= 'user_last_name=:user_last_name,';
        }
        if (array_key_exists('carbon_users.user_profile_pic', $argv)) {
            $set .= 'user_profile_pic=:user_profile_pic,';
        }
        if (array_key_exists('carbon_users.user_profile_uri', $argv)) {
            $set .= 'user_profile_uri=:user_profile_uri,';
        }
        if (array_key_exists('carbon_users.user_cover_photo', $argv)) {
            $set .= 'user_cover_photo=:user_cover_photo,';
        }
        if (array_key_exists('carbon_users.user_birthday', $argv)) {
            $set .= 'user_birthday=:user_birthday,';
        }
        if (array_key_exists('carbon_users.user_gender', $argv)) {
            $set .= 'user_gender=:user_gender,';
        }
        if (array_key_exists('carbon_users.user_about_me', $argv)) {
            $set .= 'user_about_me=:user_about_me,';
        }
        if (array_key_exists('carbon_users.user_rank', $argv)) {
            $set .= 'user_rank=:user_rank,';
        }
        if (array_key_exists('carbon_users.user_email', $argv)) {
            $set .= 'user_email=:user_email,';
        }
        if (array_key_exists('carbon_users.user_email_code', $argv)) {
            $set .= 'user_email_code=:user_email_code,';
        }
        if (array_key_exists('carbon_users.user_email_confirmed', $argv)) {
            $set .= 'user_email_confirmed=:user_email_confirmed,';
        }
        if (array_key_exists('carbon_users.user_generated_string', $argv)) {
            $set .= 'user_generated_string=:user_generated_string,';
        }
        if (array_key_exists('carbon_users.user_membership', $argv)) {
            $set .= 'user_membership=:user_membership,';
        }
        if (array_key_exists('carbon_users.user_deactivated', $argv)) {
            $set .= 'user_deactivated=:user_deactivated,';
        }
        if (array_key_exists('carbon_users.user_last_login', $argv)) {
            $set .= 'user_last_login=:user_last_login,';
        }
        if (array_key_exists('carbon_users.user_ip', $argv)) {
            $set .= 'user_ip=:user_ip,';
        }
        if (array_key_exists('carbon_users.user_education_history', $argv)) {
            $set .= 'user_education_history=:user_education_history,';
        }
        if (array_key_exists('carbon_users.user_location', $argv)) {
            $set .= 'user_location=:user_location,';
        }
        if (array_key_exists('carbon_users.user_creation_date', $argv)) {
            $set .= 'user_creation_date=:user_creation_date,';
        }
        
        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  user_id=UNHEX('.self::addInjection($primary, $pdo).')';
        

        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_users.user_username', $argv)) {
            $user_username = $argv['carbon_users.user_username'];
            $ref = 'carbon_users.user_username';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_username)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_username',$user_username, PDO::PARAM_STR, 100);
        }
        if (array_key_exists('carbon_users.user_password', $argv)) {
            $user_password = $argv['carbon_users.user_password'];
            $ref = 'carbon_users.user_password';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_password)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_password',$user_password, PDO::PARAM_STR, 225);
        }
        if (array_key_exists('carbon_users.user_id', $argv)) {
            $user_id = $argv['carbon_users.user_id'];
            $ref = 'carbon_users.user_id';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_id',$user_id, PDO::PARAM_STR, 16);
        }
        if (array_key_exists('carbon_users.user_type', $argv)) {
            $user_type = $argv['carbon_users.user_type'];
            $ref = 'carbon_users.user_type';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_type)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_type',$user_type, PDO::PARAM_STR, 20);
        }
        if (array_key_exists('carbon_users.user_sport', $argv)) {
            $user_sport = $argv['carbon_users.user_sport'];
            $ref = 'carbon_users.user_sport';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_sport)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_sport',$user_sport, PDO::PARAM_STR, 20);
        }
        if (array_key_exists('carbon_users.user_session_id', $argv)) {
            $user_session_id = $argv['carbon_users.user_session_id'];
            $ref = 'carbon_users.user_session_id';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_session_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_session_id',$user_session_id, PDO::PARAM_STR, 225);
        }
        if (array_key_exists('carbon_users.user_facebook_id', $argv)) {
            $user_facebook_id = $argv['carbon_users.user_facebook_id'];
            $ref = 'carbon_users.user_facebook_id';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_facebook_id)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_facebook_id',$user_facebook_id, PDO::PARAM_STR, 225);
        }
        if (array_key_exists('carbon_users.user_first_name', $argv)) {
            $user_first_name = $argv['carbon_users.user_first_name'];
            $ref = 'carbon_users.user_first_name';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_first_name)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_first_name',$user_first_name, PDO::PARAM_STR, 25);
        }
        if (array_key_exists('carbon_users.user_last_name', $argv)) {
            $user_last_name = $argv['carbon_users.user_last_name'];
            $ref = 'carbon_users.user_last_name';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_last_name)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_last_name',$user_last_name, PDO::PARAM_STR, 25);
        }
        if (array_key_exists('carbon_users.user_profile_pic', $argv)) {
            $user_profile_pic = $argv['carbon_users.user_profile_pic'];
            $ref = 'carbon_users.user_profile_pic';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_profile_pic)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_profile_pic',$user_profile_pic, PDO::PARAM_STR, 225);
        }
        if (array_key_exists('carbon_users.user_profile_uri', $argv)) {
            $user_profile_uri = $argv['carbon_users.user_profile_uri'];
            $ref = 'carbon_users.user_profile_uri';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_profile_uri)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_profile_uri',$user_profile_uri, PDO::PARAM_STR, 225);
        }
        if (array_key_exists('carbon_users.user_cover_photo', $argv)) {
            $user_cover_photo = $argv['carbon_users.user_cover_photo'];
            $ref = 'carbon_users.user_cover_photo';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_cover_photo)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_cover_photo',$user_cover_photo, PDO::PARAM_STR, 225);
        }
        if (array_key_exists('carbon_users.user_birthday', $argv)) {
            $user_birthday = $argv['carbon_users.user_birthday'];
            $ref = 'carbon_users.user_birthday';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_birthday)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_birthday',$user_birthday, PDO::PARAM_STR, 9);
        }
        if (array_key_exists('carbon_users.user_gender', $argv)) {
            $user_gender = $argv['carbon_users.user_gender'];
            $ref = 'carbon_users.user_gender';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_gender)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_gender',$user_gender, PDO::PARAM_STR, 25);
        }
        if (array_key_exists('carbon_users.user_about_me', $argv)) {
            $user_about_me = $argv['carbon_users.user_about_me'];
            $ref = 'carbon_users.user_about_me';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_about_me)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_about_me',$user_about_me, PDO::PARAM_STR, 225);
        }
        if (array_key_exists('carbon_users.user_rank', $argv)) {
            $stmt->bindValue(':user_rank',$argv['carbon_users.user_rank'], PDO::PARAM_INT);
        }
        if (array_key_exists('carbon_users.user_email', $argv)) {
            $user_email = $argv['carbon_users.user_email'];
            $ref = 'carbon_users.user_email';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_email)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_email',$user_email, PDO::PARAM_STR, 50);
        }
        if (array_key_exists('carbon_users.user_email_code', $argv)) {
            $user_email_code = $argv['carbon_users.user_email_code'];
            $ref = 'carbon_users.user_email_code';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_email_code)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_email_code',$user_email_code, PDO::PARAM_STR, 225);
        }
        if (array_key_exists('carbon_users.user_email_confirmed', $argv)) {
            $user_email_confirmed = $argv['carbon_users.user_email_confirmed'];
            $ref = 'carbon_users.user_email_confirmed';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_email_confirmed)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_email_confirmed',$user_email_confirmed, PDO::PARAM_INT, 1);
        }
        if (array_key_exists('carbon_users.user_generated_string', $argv)) {
            $user_generated_string = $argv['carbon_users.user_generated_string'];
            $ref = 'carbon_users.user_generated_string';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_generated_string)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_generated_string',$user_generated_string, PDO::PARAM_STR, 200);
        }
        if (array_key_exists('carbon_users.user_membership', $argv)) {
            $stmt->bindValue(':user_membership',$argv['carbon_users.user_membership'], PDO::PARAM_INT);
        }
        if (array_key_exists('carbon_users.user_deactivated', $argv)) {
            $user_deactivated = $argv['carbon_users.user_deactivated'];
            $ref = 'carbon_users.user_deactivated';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_deactivated)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_deactivated',$user_deactivated, PDO::PARAM_INT, 1);
        }
        if (array_key_exists('carbon_users.user_last_login', $argv)) {
            $stmt->bindValue(':user_last_login',$argv['carbon_users.user_last_login'], PDO::PARAM_STR);
        }
        if (array_key_exists('carbon_users.user_ip', $argv)) {
            $user_ip = $argv['carbon_users.user_ip'];
            $ref = 'carbon_users.user_ip';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_ip)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_ip',$user_ip, PDO::PARAM_STR, 20);
        }
        if (array_key_exists('carbon_users.user_education_history', $argv)) {
            $user_education_history = $argv['carbon_users.user_education_history'];
            $ref = 'carbon_users.user_education_history';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_education_history)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_education_history',$user_education_history, PDO::PARAM_STR, 200);
        }
        if (array_key_exists('carbon_users.user_location', $argv)) {
            $user_location = $argv['carbon_users.user_location'];
            $ref = 'carbon_users.user_location';
            $op = self::EQUAL;
            if (!self::validateInternalColumn(self::PUT, $ref, $op, $user_location)) {
                throw new PublicAlert('Your custom restful api validations caused the request to fail on column \'carbon_user_tasks.end_date\'.');
            }
            $stmt->bindParam(':user_location',$user_location, PDO::PARAM_STR, 20);
        }
        if (array_key_exists('carbon_users.user_creation_date', $argv)) {
            $stmt->bindValue(':user_creation_date',$argv['carbon_users.user_creation_date'], PDO::PARAM_STR);
        }

        self::bind($stmt);

        if (!$stmt->execute()) {
            throw new PublicAlert('Restful table Carbon_Users failed to execute the update query.', 'danger');
        }
        
        if (!$stmt->rowCount()) {
            throw new PublicAlert('Failed to update the target row.', 'danger');
        }
        
        $argv = array_combine(
            array_map(
                static function($k) { return str_replace('carbon_users.', '', $k); },
                array_keys($argv)
            ),
            array_values($argv)
        );

        $return = array_merge($return, $argv);

        self::prepostprocessRestRequest($return);
        
        self::postprocessRestRequest($return);
        
        self::completeRest();
        
        return true;
    }

    /**
    * @param array $remove
    * @param string|null $primary
    * @param array $argv
    * @throws PublicAlert|PDOException
    * @return bool
    */
    public static function Delete(array &$remove, string $primary = null, array $argv = []) : bool
    {
        self::startRest(self::DELETE, $argv);
        
        if (null !== $primary) {
            return Carbons::Delete($remove, $primary, $argv);
        }

        /**
         *   While useful, we've decided to disallow full
         *   table deletions through the rest api. For the
         *   n00bs and future self, "I got chu."
         */
        if (empty($argv)) {
            throw new PublicAlert('When deleting from restful tables a primary key or where query must be provided.', 'danger');
        }
        
        $sql = 'DELETE c FROM carbons c 
                JOIN carbon_users on c.entity_pk = carbon_users.user_id';

        $pdo = self::database();

        $sql .= ' WHERE ' . self::buildBooleanJoinConditions(self::DELETE, $argv, $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        self::postpreprocessRestRequest($sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        if ($r) {
            $remove = [];
        }
        
        self::prepostprocessRestRequest($remove);
        
        self::postprocessRestRequest($remove);
        
        self::completeRest();
        
        return $r;
    }
     

    
}
