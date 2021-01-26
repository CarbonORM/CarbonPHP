<?php 

namespace CarbonPHP\Tables;

// Restful defaults
use PDO;
use CarbonPHP\Rest;
use CarbonPHP\Interfaces\iRest;
use CarbonPHP\Error\PublicAlert;
use function array_key_exists;
use function count;
use function func_get_args;
use function is_array;

// Custom User Imports
use CarbonPHP\Programs\ColorCode;
use CarbonPHP\Helpers\RestfulValidation;

class Carbon_Users extends Rest implements iRest
{
    
    public const CLASS_NAME = 'Carbon_Users';
    public const TABLE_NAME = 'carbon_users';
    public const TABLE_NAMESPACE = 'CarbonPHP\Tables';
    
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
        'carbon_users.user_username' => ['varchar', '2', '100'],'carbon_users.user_password' => ['varchar', '2', '225'],'carbon_users.user_id' => ['binary', '2', '16'],'carbon_users.user_type' => ['varchar', '2', '20'],'carbon_users.user_sport' => ['varchar', '2', '20'],'carbon_users.user_session_id' => ['varchar', '2', '225'],'carbon_users.user_facebook_id' => ['varchar', '2', '225'],'carbon_users.user_first_name' => ['varchar', '2', '25'],'carbon_users.user_last_name' => ['varchar', '2', '25'],'carbon_users.user_profile_pic' => ['varchar', '2', '225'],'carbon_users.user_profile_uri' => ['varchar', '2', '225'],'carbon_users.user_cover_photo' => ['varchar', '2', '225'],'carbon_users.user_birthday' => ['varchar', '2', '9'],'carbon_users.user_gender' => ['varchar', '2', '25'],'carbon_users.user_about_me' => ['varchar', '2', '225'],'carbon_users.user_rank' => ['int', '2', ''],'carbon_users.user_email' => ['varchar', '2', '50'],'carbon_users.user_email_code' => ['varchar', '2', '225'],'carbon_users.user_email_confirmed' => ['tinyint', '0', '1'],'carbon_users.user_generated_string' => ['varchar', '2', '200'],'carbon_users.user_membership' => ['int', '2', ''],'carbon_users.user_deactivated' => ['tinyint', '0', '1'],'carbon_users.user_last_login' => ['datetime', '2', ''],'carbon_users.user_ip' => ['varchar', '2', '20'],'carbon_users.user_education_history' => ['varchar', '2', '200'],'carbon_users.user_location' => ['varchar', '2', '20'],'carbon_users.user_creation_date' => ['datetime', '2', ''],
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
     *  All methods MUST be declaired as static.
     */
 
    public const PHP_VALIDATION = []; 
 
    public const REGEX_VALIDATION = [
        // 1 to 1 regular expressions to match on every post request.
        self::USER_ID => self::VALIDATE_C6_ENTITY_ID_REGEX,
        self::USER_USERNAME => "#^[A-Za-z0-9_-]{4,16}#",
    ]; 
    
    /**
    *
    *   $argv = [
    *       'select' => [
    *                          '*column name array*', 'etc..'
    *        ],
    *
    *       'where' => [
    *              'Column Name' => 'Value To Constrain',
    *              'Defaults to AND' => 'Nesting array switches to OR',
    *              [
    *                  'Column Name' => 'Value To Constrain',
    *                  'This array is OR'ed togeather' => 'Another sud array would `AND`'
    *                  [ etc... ]
    *              ]
    *        ],
    *
    *        'pagination' => [
    *              'limit' => (int) 90, // The maximum number of rows to return,
    *                       setting the limit explicitly to 1 will return a key pair array of only the
    *                       singular result. SETTING THE LIMIT TO NULL WILL ALLOW INFINITE RESULTS (NO LIMIT).
    *                       The limit defaults to 100 by design.
    *
    *              'order' => ['*column name*'=> '(ASC|DESC)'],  // i.e.  'username' => 'ASC'
    *
    *
    *         ],
    *
    *   ];
    *
    *
    * @param array $return
    * @param string|null $primary
    * @param array $argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Get(array &$return, string $primary = null, array $argv = []): bool
    {
        self::$tableNamespace = self::TABLE_NAMESPACE;
   
        $pdo = self::database();

        $sql = self::buildSelectQuery($primary, $argv, '', $pdo);
        
        self::jsonSQLReporting(func_get_args(), $sql);
        
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

        return true;
    }

    /**
     * @param array $argv
     * @param string|null $dependantEntityId - a C6 Hex entity key 
     * @return bool|string
     * @throws PublicAlert
     */
    public static function Post(array $argv, string $dependantEntityId = null)
    {   
        self::$tableNamespace = self::TABLE_NAMESPACE;
    
        foreach ($argv as $columnName => $postValue) {
            if (!array_key_exists($columnName, self::PDO_VALIDATION)){
                throw new PublicAlert("Restful table could not post column $columnName, because it does not appear to exist.", 'danger');
            }
        } 
        
        $sql = 'INSERT INTO carbon_users (user_username, user_password, user_id, user_type, user_sport, user_session_id, user_facebook_id, user_first_name, user_last_name, user_profile_pic, user_profile_uri, user_cover_photo, user_birthday, user_gender, user_about_me, user_rank, user_email, user_email_code, user_email_confirmed, user_generated_string, user_membership, user_deactivated, user_ip, user_education_history, user_location) VALUES ( :user_username, :user_password, UNHEX(:user_id), :user_type, :user_sport, :user_session_id, :user_facebook_id, :user_first_name, :user_last_name, :user_profile_pic, :user_profile_uri, :user_cover_photo, :user_birthday, :user_gender, :user_about_me, :user_rank, :user_email, :user_email_code, :user_email_confirmed, :user_generated_string, :user_membership, :user_deactivated, :user_ip, :user_education_history, :user_location)';

        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = self::database()->prepare($sql);

    
    
        if (!array_key_exists('carbon_users.user_username', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_username" is missing from the request.', 'danger');
        }
        $user_username = $argv['carbon_users.user_username'];
        $stmt->bindParam(':user_username',$user_username, 2, 100);
    
    
        if (!array_key_exists('carbon_users.user_password', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_password" is missing from the request.', 'danger');
        }
        $user_password = $argv['carbon_users.user_password'];
        $stmt->bindParam(':user_password',$user_password, 2, 225);
    
        $user_id = $id = $argv['carbon_users.user_id'] ?? self::beginTransaction(self::class, $dependantEntityId);
        $stmt->bindParam(':user_id',$user_id, 2, 16);
    
    
        $user_type =  $argv['carbon_users.user_type'] ?? 'Athlete';
        $stmt->bindParam(':user_type',$user_type, 2, 20);
    
    
        $user_sport =  $argv['carbon_users.user_sport'] ?? 'GOLF';
        $stmt->bindParam(':user_sport',$user_sport, 2, 20);
    
    
        $user_session_id =  $argv['carbon_users.user_session_id'] ?? null;
        $stmt->bindParam(':user_session_id',$user_session_id, 2, 225);
    
    
        $user_facebook_id =  $argv['carbon_users.user_facebook_id'] ?? null;
        $stmt->bindParam(':user_facebook_id',$user_facebook_id, 2, 225);
    
    
        if (!array_key_exists('carbon_users.user_first_name', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_first_name" is missing from the request.', 'danger');
        }
        $user_first_name = $argv['carbon_users.user_first_name'];
        $stmt->bindParam(':user_first_name',$user_first_name, 2, 25);
    
    
        if (!array_key_exists('carbon_users.user_last_name', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_last_name" is missing from the request.', 'danger');
        }
        $user_last_name = $argv['carbon_users.user_last_name'];
        $stmt->bindParam(':user_last_name',$user_last_name, 2, 25);
    
    
        $user_profile_pic =  $argv['carbon_users.user_profile_pic'] ?? null;
        $stmt->bindParam(':user_profile_pic',$user_profile_pic, 2, 225);
    
    
        $user_profile_uri =  $argv['carbon_users.user_profile_uri'] ?? null;
        $stmt->bindParam(':user_profile_uri',$user_profile_uri, 2, 225);
    
    
        $user_cover_photo =  $argv['carbon_users.user_cover_photo'] ?? null;
        $stmt->bindParam(':user_cover_photo',$user_cover_photo, 2, 225);
    
    
        $user_birthday =  $argv['carbon_users.user_birthday'] ?? null;
        $stmt->bindParam(':user_birthday',$user_birthday, 2, 9);
    
    
        $user_gender =  $argv['carbon_users.user_gender'] ?? null;
        $stmt->bindParam(':user_gender',$user_gender, 2, 25);
    
    
        $user_about_me =  $argv['carbon_users.user_about_me'] ?? null;
        $stmt->bindParam(':user_about_me',$user_about_me, 2, 225);
    
        $stmt->bindValue(':user_rank',array_key_exists('carbon_users.user_rank',$argv) ? $argv['carbon_users.user_rank'] : '0', 2);
    
    
        if (!array_key_exists('carbon_users.user_email', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_email" is missing from the request.', 'danger');
        }
        $user_email = $argv['carbon_users.user_email'];
        $stmt->bindParam(':user_email',$user_email, 2, 50);
    
    
        $user_email_code =  $argv['carbon_users.user_email_code'] ?? null;
        $stmt->bindParam(':user_email_code',$user_email_code, 2, 225);
    
    
        $user_email_confirmed =  $argv['carbon_users.user_email_confirmed'] ?? '0';
        $stmt->bindParam(':user_email_confirmed',$user_email_confirmed, 0, 1);
    
    
        $user_generated_string =  $argv['carbon_users.user_generated_string'] ?? null;
        $stmt->bindParam(':user_generated_string',$user_generated_string, 2, 200);
    
        $stmt->bindValue(':user_membership',array_key_exists('carbon_users.user_membership',$argv) ? $argv['carbon_users.user_membership'] : '0', 2);
    
    
        $user_deactivated =  $argv['carbon_users.user_deactivated'] ?? '0';
        $stmt->bindParam(':user_deactivated',$user_deactivated, 0, 1);
    
    
        if (!array_key_exists('carbon_users.user_ip', $argv)) {
            throw new PublicAlert('Required argument "carbon_users.user_ip" is missing from the request.', 'danger');
        }
        $user_ip = $argv['carbon_users.user_ip'];
        $stmt->bindParam(':user_ip',$user_ip, 2, 20);
    
    
        $user_education_history =  $argv['carbon_users.user_education_history'] ?? null;
        $stmt->bindParam(':user_education_history',$user_education_history, 2, 200);
    
    
        $user_location =  $argv['carbon_users.user_location'] ?? null;
        $stmt->bindParam(':user_location',$user_location, 2, 20);
    


        return $stmt->execute() ? $id : false;
    
    }
    
    /**
    * @param array $return
    * @param string $primary
    * @param array $argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Put(array &$return, string $primary, array $argv) : bool
    {
        self::$tableNamespace = self::TABLE_NAMESPACE;
        
        if (empty($primary)) {
            throw new PublicAlert('Restful tables which have a primary key must be updated by its primary key.', 'danger');
        }
        
        if (array_key_exists(self::UPDATE, $argv)) {
            $argv = $argv[self::UPDATE];
        }
        
        foreach ($argv as $key => $value) {
            if (!array_key_exists($key, self::PDO_VALIDATION)){
                throw new PublicAlert('Restful table could not update column $key, because it does not appear to exist.', 'danger');
            }
        }

        $sql = 'UPDATE carbon_users ' . ' SET '; // intellij cant handle this otherwise

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

        $stmt = $pdo->prepare($sql);

        if (array_key_exists('carbon_users.user_username', $argv)) {
            $user_username = $argv['carbon_users.user_username'];
            $stmt->bindParam(':user_username',$user_username, 2, 100);
        }
        if (array_key_exists('carbon_users.user_password', $argv)) {
            $user_password = $argv['carbon_users.user_password'];
            $stmt->bindParam(':user_password',$user_password, 2, 225);
        }
        if (array_key_exists('carbon_users.user_id', $argv)) {
            $user_id = $argv['carbon_users.user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
        if (array_key_exists('carbon_users.user_type', $argv)) {
            $user_type = $argv['carbon_users.user_type'];
            $stmt->bindParam(':user_type',$user_type, 2, 20);
        }
        if (array_key_exists('carbon_users.user_sport', $argv)) {
            $user_sport = $argv['carbon_users.user_sport'];
            $stmt->bindParam(':user_sport',$user_sport, 2, 20);
        }
        if (array_key_exists('carbon_users.user_session_id', $argv)) {
            $user_session_id = $argv['carbon_users.user_session_id'];
            $stmt->bindParam(':user_session_id',$user_session_id, 2, 225);
        }
        if (array_key_exists('carbon_users.user_facebook_id', $argv)) {
            $user_facebook_id = $argv['carbon_users.user_facebook_id'];
            $stmt->bindParam(':user_facebook_id',$user_facebook_id, 2, 225);
        }
        if (array_key_exists('carbon_users.user_first_name', $argv)) {
            $user_first_name = $argv['carbon_users.user_first_name'];
            $stmt->bindParam(':user_first_name',$user_first_name, 2, 25);
        }
        if (array_key_exists('carbon_users.user_last_name', $argv)) {
            $user_last_name = $argv['carbon_users.user_last_name'];
            $stmt->bindParam(':user_last_name',$user_last_name, 2, 25);
        }
        if (array_key_exists('carbon_users.user_profile_pic', $argv)) {
            $user_profile_pic = $argv['carbon_users.user_profile_pic'];
            $stmt->bindParam(':user_profile_pic',$user_profile_pic, 2, 225);
        }
        if (array_key_exists('carbon_users.user_profile_uri', $argv)) {
            $user_profile_uri = $argv['carbon_users.user_profile_uri'];
            $stmt->bindParam(':user_profile_uri',$user_profile_uri, 2, 225);
        }
        if (array_key_exists('carbon_users.user_cover_photo', $argv)) {
            $user_cover_photo = $argv['carbon_users.user_cover_photo'];
            $stmt->bindParam(':user_cover_photo',$user_cover_photo, 2, 225);
        }
        if (array_key_exists('carbon_users.user_birthday', $argv)) {
            $user_birthday = $argv['carbon_users.user_birthday'];
            $stmt->bindParam(':user_birthday',$user_birthday, 2, 9);
        }
        if (array_key_exists('carbon_users.user_gender', $argv)) {
            $user_gender = $argv['carbon_users.user_gender'];
            $stmt->bindParam(':user_gender',$user_gender, 2, 25);
        }
        if (array_key_exists('carbon_users.user_about_me', $argv)) {
            $user_about_me = $argv['carbon_users.user_about_me'];
            $stmt->bindParam(':user_about_me',$user_about_me, 2, 225);
        }
        if (array_key_exists('carbon_users.user_rank', $argv)) {
            $stmt->bindValue(':user_rank',$argv['carbon_users.user_rank'], 2);
        }
        if (array_key_exists('carbon_users.user_email', $argv)) {
            $user_email = $argv['carbon_users.user_email'];
            $stmt->bindParam(':user_email',$user_email, 2, 50);
        }
        if (array_key_exists('carbon_users.user_email_code', $argv)) {
            $user_email_code = $argv['carbon_users.user_email_code'];
            $stmt->bindParam(':user_email_code',$user_email_code, 2, 225);
        }
        if (array_key_exists('carbon_users.user_email_confirmed', $argv)) {
            $user_email_confirmed = $argv['carbon_users.user_email_confirmed'];
            $stmt->bindParam(':user_email_confirmed',$user_email_confirmed, 0, 1);
        }
        if (array_key_exists('carbon_users.user_generated_string', $argv)) {
            $user_generated_string = $argv['carbon_users.user_generated_string'];
            $stmt->bindParam(':user_generated_string',$user_generated_string, 2, 200);
        }
        if (array_key_exists('carbon_users.user_membership', $argv)) {
            $stmt->bindValue(':user_membership',$argv['carbon_users.user_membership'], 2);
        }
        if (array_key_exists('carbon_users.user_deactivated', $argv)) {
            $user_deactivated = $argv['carbon_users.user_deactivated'];
            $stmt->bindParam(':user_deactivated',$user_deactivated, 0, 1);
        }
        if (array_key_exists('carbon_users.user_last_login', $argv)) {
            $stmt->bindValue(':user_last_login',$argv['carbon_users.user_last_login'], 2);
        }
        if (array_key_exists('carbon_users.user_ip', $argv)) {
            $user_ip = $argv['carbon_users.user_ip'];
            $stmt->bindParam(':user_ip',$user_ip, 2, 20);
        }
        if (array_key_exists('carbon_users.user_education_history', $argv)) {
            $user_education_history = $argv['carbon_users.user_education_history'];
            $stmt->bindParam(':user_education_history',$user_education_history, 2, 200);
        }
        if (array_key_exists('carbon_users.user_location', $argv)) {
            $user_location = $argv['carbon_users.user_location'];
            $stmt->bindParam(':user_location',$user_location, 2, 20);
        }
        if (array_key_exists('carbon_users.user_creation_date', $argv)) {
            $stmt->bindValue(':user_creation_date',$argv['carbon_users.user_creation_date'], 2);
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

        return true;

    }

    /**
    * @param array $remove
    * @param string|null $primary
    * @param array $argv
    * @throws PublicAlert
    * @return bool
    */
    public static function Delete(array &$remove, string $primary = null, array $argv = []) : bool
    {
        self::$tableNamespace = self::TABLE_NAMESPACE;
        
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

        $sql .= ' WHERE ' . self::buildWhere($argv, $pdo, 'carbon_users', self::PDO_VALIDATION);
        
        self::jsonSQLReporting(func_get_args(), $sql);

        $stmt = $pdo->prepare($sql);

        self::bind($stmt);

        $r = $stmt->execute();

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $r and $remove = [];

        return $r;
    }
     
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
    
}
