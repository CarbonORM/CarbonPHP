<?php

namespace CarbonPHP\Tables;

use CarbonPHP\Database;
use CarbonPHP\Interfaces\iRest;


class carbon_users extends Database implements iRest
{
    public const PRIMARY = [
    'user_id',
    ];

    public const COLUMNS = [
        'user_id' => [ 'binary', '2', '16' ],'user_type' => [ 'varchar', '2', '20' ],'user_sport' => [ 'varchar', '2', '20' ],'user_session_id' => [ 'varchar', '2', '225' ],'user_facebook_id' => [ 'varchar', '2', '225' ],'user_username' => [ 'varchar', '2', '25' ],'user_first_name' => [ 'varchar', '2', '25' ],'user_last_name' => [ 'varchar', '2', '25' ],'user_profile_pic' => [ 'varchar', '2', '225' ],'user_profile_uri' => [ 'varchar', '2', '225' ],'user_cover_photo' => [ 'varchar', '2', '225' ],'user_birthday' => [ 'varchar', '2', '9' ],'user_gender' => [ 'varchar', '2', '25' ],'user_about_me' => [ 'varchar', '2', '225' ],'user_rank' => [ 'int', '2', '8' ],'user_password' => [ 'varchar', '2', '225' ],'user_email' => [ 'varchar', '2', '50' ],'user_email_code' => [ 'varchar', '2', '225' ],'user_email_confirmed' => [ 'varchar', '2', '20' ],'user_generated_string' => [ 'varchar', '2', '200' ],'user_membership' => [ 'int', '2', '10' ],'user_deactivated' => [ 'tinyint', '0', '1' ],'user_last_login' => [ 'datetime', '2', '' ],'user_ip' => [ 'varchar', '2', '20' ],'user_education_history' => [ 'varchar', '2', '200' ],'user_location' => [ 'varchar', '2', '20' ],'user_creation_date' => [ 'datetime', '2', '' ],
    ];

    public const VALIDATION = [];


    public static $injection = [];



    public static function buildWhere(array $set, \PDO $pdo, $join = 'AND') : string
    {
        $sql = '(';
        foreach ($set as $column => $value) {
            if (\is_array($value)) {
                $sql .= self::buildWhere($value, $pdo, $join === 'AND' ? 'OR' : 'AND');
            } else if (array_key_exists($column, self::COLUMNS)) {
                if (self::COLUMNS[$column][0] === 'binary') {
                    $sql .= "($column = UNHEX(:" . $column . ")) $join ";
                } else {
                    $sql .= "($column = :" . $column . ") $join ";
                }
            } else {
                $sql .= "($column = " . self::addInjection($value, $pdo) . ") $join ";
            }
        }
        return rtrim($sql, " $join") . ')';
    }

    public static function addInjection($value, \PDO $pdo, $quote = false) : string
    {
        $inject = ':injection' . \count(self::$injection) . 'buildWhere';
        self::$injection[$inject] = $quote ? $pdo->quote($value) : $value;
        return $inject;
    }

    public static function bind(\PDOStatement $stmt, array $argv) {
        if (array_key_exists('user_id', $argv)) {
            $user_id = $argv['user_id'];
            $stmt->bindParam(':user_id',$user_id, 2, 16);
        }
        if (array_key_exists('user_type', $argv)) {
            $user_type = $argv['user_type'];
            $stmt->bindParam(':user_type',$user_type, 2, 20);
        }
        if (array_key_exists('user_sport', $argv)) {
            $user_sport = $argv['user_sport'];
            $stmt->bindParam(':user_sport',$user_sport, 2, 20);
        }
        if (array_key_exists('user_session_id', $argv)) {
            $user_session_id = $argv['user_session_id'];
            $stmt->bindParam(':user_session_id',$user_session_id, 2, 225);
        }
        if (array_key_exists('user_facebook_id', $argv)) {
            $user_facebook_id = $argv['user_facebook_id'];
            $stmt->bindParam(':user_facebook_id',$user_facebook_id, 2, 225);
        }
        if (array_key_exists('user_username', $argv)) {
            $user_username = $argv['user_username'];
            $stmt->bindParam(':user_username',$user_username, 2, 25);
        }
        if (array_key_exists('user_first_name', $argv)) {
            $user_first_name = $argv['user_first_name'];
            $stmt->bindParam(':user_first_name',$user_first_name, 2, 25);
        }
        if (array_key_exists('user_last_name', $argv)) {
            $user_last_name = $argv['user_last_name'];
            $stmt->bindParam(':user_last_name',$user_last_name, 2, 25);
        }
        if (array_key_exists('user_profile_pic', $argv)) {
            $user_profile_pic = $argv['user_profile_pic'];
            $stmt->bindParam(':user_profile_pic',$user_profile_pic, 2, 225);
        }
        if (array_key_exists('user_profile_uri', $argv)) {
            $user_profile_uri = $argv['user_profile_uri'];
            $stmt->bindParam(':user_profile_uri',$user_profile_uri, 2, 225);
        }
        if (array_key_exists('user_cover_photo', $argv)) {
            $user_cover_photo = $argv['user_cover_photo'];
            $stmt->bindParam(':user_cover_photo',$user_cover_photo, 2, 225);
        }
        if (array_key_exists('user_birthday', $argv)) {
            $user_birthday = $argv['user_birthday'];
            $stmt->bindParam(':user_birthday',$user_birthday, 2, 9);
        }
        if (array_key_exists('user_gender', $argv)) {
            $user_gender = $argv['user_gender'];
            $stmt->bindParam(':user_gender',$user_gender, 2, 25);
        }
        if (array_key_exists('user_about_me', $argv)) {
            $user_about_me = $argv['user_about_me'];
            $stmt->bindParam(':user_about_me',$user_about_me, 2, 225);
        }
        if (array_key_exists('user_rank', $argv)) {
            $user_rank = $argv['user_rank'];
            $stmt->bindParam(':user_rank',$user_rank, 2, 8);
        }
        if (array_key_exists('user_password', $argv)) {
            $user_password = $argv['user_password'];
            $stmt->bindParam(':user_password',$user_password, 2, 225);
        }
        if (array_key_exists('user_email', $argv)) {
            $user_email = $argv['user_email'];
            $stmt->bindParam(':user_email',$user_email, 2, 50);
        }
        if (array_key_exists('user_email_code', $argv)) {
            $user_email_code = $argv['user_email_code'];
            $stmt->bindParam(':user_email_code',$user_email_code, 2, 225);
        }
        if (array_key_exists('user_email_confirmed', $argv)) {
            $user_email_confirmed = $argv['user_email_confirmed'];
            $stmt->bindParam(':user_email_confirmed',$user_email_confirmed, 2, 20);
        }
        if (array_key_exists('user_generated_string', $argv)) {
            $user_generated_string = $argv['user_generated_string'];
            $stmt->bindParam(':user_generated_string',$user_generated_string, 2, 200);
        }
        if (array_key_exists('user_membership', $argv)) {
            $user_membership = $argv['user_membership'];
            $stmt->bindParam(':user_membership',$user_membership, 2, 10);
        }
        if (array_key_exists('user_deactivated', $argv)) {
            $user_deactivated = $argv['user_deactivated'];
            $stmt->bindParam(':user_deactivated',$user_deactivated, 0, 1);
        }
        if (array_key_exists('user_last_login', $argv)) {
            $stmt->bindValue(':user_last_login',$argv['user_last_login'], 2);
        }
        if (array_key_exists('user_ip', $argv)) {
            $user_ip = $argv['user_ip'];
            $stmt->bindParam(':user_ip',$user_ip, 2, 20);
        }
        if (array_key_exists('user_education_history', $argv)) {
            $user_education_history = $argv['user_education_history'];
            $stmt->bindParam(':user_education_history',$user_education_history, 2, 200);
        }
        if (array_key_exists('user_location', $argv)) {
            $user_location = $argv['user_location'];
            $stmt->bindParam(':user_location',$user_location, 2, 20);
        }
        if (array_key_exists('user_creation_date', $argv)) {
            $stmt->bindValue(':user_creation_date',$argv['user_creation_date'], 2);
        }

        foreach (self::$injection as $key => $value) {
            $stmt->bindValue($key,$value);
        }

        return $stmt->execute();
    }


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
    *              'order' => '*column name* [ASC|DESC]',  // i.e.  'username ASC' or 'username, email DESC'
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
    * @return bool
    * @throws \Exception
    */
    public static function Get(array &$return, string $primary = null, array $argv) : bool
    {
        self::$injection = [];
        $aggregate = false;
        $group = $sql = '';
        $pdo = self::database();

        $get = $argv['select'] ?? array_keys(self::COLUMNS);
        $where = $argv['where'] ?? [];

        if (array_key_exists('pagination',$argv)) {
            if (!empty($argv['pagination']) && !\is_array($argv['pagination'])) {
                $argv['pagination'] = json_decode($argv['pagination'], true);
            }
            if (array_key_exists('limit',$argv['pagination']) && $argv['pagination']['limit'] !== null) {
                $limit = ' LIMIT ' . $argv['pagination']['limit'];
            } else {
                $limit = '';
            }

            $order = '';
            if (!empty($limit)) {

                $order = ' ORDER BY ';

                if (array_key_exists('order',$argv['pagination']) && $argv['pagination']['order'] !== null) {
                    if (\is_array($argv['pagination']['order'])) {
                        foreach ($argv['pagination']['order'] as $item => $sort) {
                            $order .= "$item $sort";
                        }
                    } else {
                        $order .= $argv['pagination']['order'];
                    }
                } else {
                    $order .= 'user_id ASC';
                }
            }
            $limit = "$order $limit";
        } else {
            $limit = ' ORDER BY user_id ASC LIMIT 100';
        }

        foreach($get as $key => $column){
            if (!empty($sql)) {
                $sql .= ', ';
                if (!empty($group)) {
                    $group .= ', ';
                }
            }
            $columnExists = array_key_exists($column, self::COLUMNS);
            if ($columnExists && self::COLUMNS[$column][0] === 'binary') {
                $sql .= "HEX($column) as $column";
                $group .= $column;
            } elseif ($columnExists) {
                $sql .= $column;
                $group .= $column;
            } else {
                if (!preg_match('#(((((hex|argv|count|sum|min|max) *\(+ *)+)|(distinct|\*|\+|\-|\/| |user_id|user_type|user_sport|user_session_id|user_facebook_id|user_username|user_first_name|user_last_name|user_profile_pic|user_profile_uri|user_cover_photo|user_birthday|user_gender|user_about_me|user_rank|user_password|user_email|user_email_code|user_email_confirmed|user_generated_string|user_membership|user_deactivated|user_last_login|user_ip|user_education_history|user_location|user_creation_date))+\)*)+ *(as [a-z]+)?#i', $column)) {
                    return false;
                }
                $sql .= $column;
                $aggregate = true;
            }
        }

        $sql = 'SELECT ' .  $sql . ' FROM carbon_users';

        if (null === $primary) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if (!empty($where)) {
                $sql .= ' WHERE ' . self::buildWhere($where, $pdo);
            }
        } else {
        $sql .= ' WHERE  user_id=UNHEX('.self::addInjection($primary, $pdo).')';
        }

        if ($aggregate  && !empty($group)) {
            $sql .= ' GROUP BY ' . $group . ' ';
        }

        $sql .= $limit;

        

        $stmt = $pdo->prepare($sql);

        if (!self::bind($stmt, $argv['where'] ?? [])) {
            return false;
        }

        $return = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        /**
        *   The next part is so every response from the rest api
        *   formats to a set of rows. Even if only one row is returned.
        *   You must set the third parameter to true, otherwise '0' is
        *   apparently in the self::COLUMNS
        */

        
        if ($primary !== null || (isset($argv['pagination']['limit']) && $argv['pagination']['limit'] === 1 && \count($return) === 1)) {
            $return = isset($return[0]) && \is_array($return[0]) ? $return[0] : $return;
            // promise this is needed and will still return the desired array except for a single record will not be an array
        
        }

        return true;
    }

    /**
    * @param array $argv
    * @return bool|mixed
    */
    public static function Post(array $argv)
    {
        self::$injection = [];
        /** @noinspection SqlResolve */
        $sql = 'INSERT INTO carbon_users (user_id, user_type, user_sport, user_session_id, user_facebook_id, user_username, user_first_name, user_last_name, user_profile_pic, user_profile_uri, user_cover_photo, user_birthday, user_gender, user_about_me, user_rank, user_password, user_email, user_email_code, user_email_confirmed, user_generated_string, user_membership, user_deactivated, user_ip, user_education_history, user_location) VALUES ( UNHEX(:user_id), :user_type, :user_sport, :user_session_id, :user_facebook_id, :user_username, :user_first_name, :user_last_name, :user_profile_pic, :user_profile_uri, :user_cover_photo, :user_birthday, :user_gender, :user_about_me, :user_rank, :user_password, :user_email, :user_email_code, :user_email_confirmed, :user_generated_string, :user_membership, :user_deactivated, :user_ip, :user_education_history, :user_location)';

        

        $stmt = self::database()->prepare($sql);

                $user_id = $id = $argv['user_id'] ?? self::beginTransaction('carbon_users');
                $stmt->bindParam(':user_id',$user_id, 2, 16);
                
                    $user_type =  $argv['user_type'] ?? 'Athlete';
                    $stmt->bindParam(':user_type',$user_type, 2, 20);
                        
                    $user_sport =  $argv['user_sport'] ?? 'GOLF';
                    $stmt->bindParam(':user_sport',$user_sport, 2, 20);
                        
                    $user_session_id =  $argv['user_session_id'] ?? null;
                    $stmt->bindParam(':user_session_id',$user_session_id, 2, 225);
                        
                    $user_facebook_id =  $argv['user_facebook_id'] ?? null;
                    $stmt->bindParam(':user_facebook_id',$user_facebook_id, 2, 225);
                        
                    $user_username = $argv['user_username'];
                    $stmt->bindParam(':user_username',$user_username, 2, 25);
                        
                    $user_first_name = $argv['user_first_name'];
                    $stmt->bindParam(':user_first_name',$user_first_name, 2, 25);
                        
                    $user_last_name = $argv['user_last_name'];
                    $stmt->bindParam(':user_last_name',$user_last_name, 2, 25);
                        
                    $user_profile_pic =  $argv['user_profile_pic'] ?? null;
                    $stmt->bindParam(':user_profile_pic',$user_profile_pic, 2, 225);
                        
                    $user_profile_uri =  $argv['user_profile_uri'] ?? null;
                    $stmt->bindParam(':user_profile_uri',$user_profile_uri, 2, 225);
                        
                    $user_cover_photo =  $argv['user_cover_photo'] ?? null;
                    $stmt->bindParam(':user_cover_photo',$user_cover_photo, 2, 225);
                        
                    $user_birthday =  $argv['user_birthday'] ?? null;
                    $stmt->bindParam(':user_birthday',$user_birthday, 2, 9);
                        
                    $user_gender = $argv['user_gender'];
                    $stmt->bindParam(':user_gender',$user_gender, 2, 25);
                        
                    $user_about_me =  $argv['user_about_me'] ?? null;
                    $stmt->bindParam(':user_about_me',$user_about_me, 2, 225);
                        
                    $user_rank =  $argv['user_rank'] ?? '0';
                    $stmt->bindParam(':user_rank',$user_rank, 2, 8);
                        
                    $user_password = $argv['user_password'];
                    $stmt->bindParam(':user_password',$user_password, 2, 225);
                        
                    $user_email = $argv['user_email'];
                    $stmt->bindParam(':user_email',$user_email, 2, 50);
                        
                    $user_email_code =  $argv['user_email_code'] ?? null;
                    $stmt->bindParam(':user_email_code',$user_email_code, 2, 225);
                        
                    $user_email_confirmed =  $argv['user_email_confirmed'] ?? '0';
                    $stmt->bindParam(':user_email_confirmed',$user_email_confirmed, 2, 20);
                        
                    $user_generated_string =  $argv['user_generated_string'] ?? null;
                    $stmt->bindParam(':user_generated_string',$user_generated_string, 2, 200);
                        
                    $user_membership =  $argv['user_membership'] ?? '0';
                    $stmt->bindParam(':user_membership',$user_membership, 2, 10);
                        
                    $user_deactivated =  $argv['user_deactivated'] ?? '0';
                    $stmt->bindParam(':user_deactivated',$user_deactivated, 0, 1);
                                
                    $user_ip = $argv['user_ip'];
                    $stmt->bindParam(':user_ip',$user_ip, 2, 20);
                        
                    $user_education_history =  $argv['user_education_history'] ?? null;
                    $stmt->bindParam(':user_education_history',$user_education_history, 2, 200);
                        
                    $user_location =  $argv['user_location'] ?? null;
                    $stmt->bindParam(':user_location',$user_location, 2, 20);
                


        return $stmt->execute() ? $id : false;

    }

    /**
    * @param array $return
    * @param string $primary
    * @param array $argv
    * @return bool
    */
    public static function Put(array &$return, string $primary, array $argv) : bool
    {
        self::$injection = [];
        if (empty($primary)) {
            return false;
        }

        foreach ($argv as $key => $value) {
            if (!\array_key_exists($key, self::COLUMNS)){
                return false;
            }
        }

        $sql = 'UPDATE carbon_users ';

        $sql .= ' SET ';        // my editor yells at me if I don't separate this from the above stmt

        $set = '';

            if (array_key_exists('user_id', $argv)) {
                $set .= 'user_id=UNHEX(:user_id),';
            }
            if (array_key_exists('user_type', $argv)) {
                $set .= 'user_type=:user_type,';
            }
            if (array_key_exists('user_sport', $argv)) {
                $set .= 'user_sport=:user_sport,';
            }
            if (array_key_exists('user_session_id', $argv)) {
                $set .= 'user_session_id=:user_session_id,';
            }
            if (array_key_exists('user_facebook_id', $argv)) {
                $set .= 'user_facebook_id=:user_facebook_id,';
            }
            if (array_key_exists('user_username', $argv)) {
                $set .= 'user_username=:user_username,';
            }
            if (array_key_exists('user_first_name', $argv)) {
                $set .= 'user_first_name=:user_first_name,';
            }
            if (array_key_exists('user_last_name', $argv)) {
                $set .= 'user_last_name=:user_last_name,';
            }
            if (array_key_exists('user_profile_pic', $argv)) {
                $set .= 'user_profile_pic=:user_profile_pic,';
            }
            if (array_key_exists('user_profile_uri', $argv)) {
                $set .= 'user_profile_uri=:user_profile_uri,';
            }
            if (array_key_exists('user_cover_photo', $argv)) {
                $set .= 'user_cover_photo=:user_cover_photo,';
            }
            if (array_key_exists('user_birthday', $argv)) {
                $set .= 'user_birthday=:user_birthday,';
            }
            if (array_key_exists('user_gender', $argv)) {
                $set .= 'user_gender=:user_gender,';
            }
            if (array_key_exists('user_about_me', $argv)) {
                $set .= 'user_about_me=:user_about_me,';
            }
            if (array_key_exists('user_rank', $argv)) {
                $set .= 'user_rank=:user_rank,';
            }
            if (array_key_exists('user_password', $argv)) {
                $set .= 'user_password=:user_password,';
            }
            if (array_key_exists('user_email', $argv)) {
                $set .= 'user_email=:user_email,';
            }
            if (array_key_exists('user_email_code', $argv)) {
                $set .= 'user_email_code=:user_email_code,';
            }
            if (array_key_exists('user_email_confirmed', $argv)) {
                $set .= 'user_email_confirmed=:user_email_confirmed,';
            }
            if (array_key_exists('user_generated_string', $argv)) {
                $set .= 'user_generated_string=:user_generated_string,';
            }
            if (array_key_exists('user_membership', $argv)) {
                $set .= 'user_membership=:user_membership,';
            }
            if (array_key_exists('user_deactivated', $argv)) {
                $set .= 'user_deactivated=:user_deactivated,';
            }
            if (array_key_exists('user_last_login', $argv)) {
                $set .= 'user_last_login=:user_last_login,';
            }
            if (array_key_exists('user_ip', $argv)) {
                $set .= 'user_ip=:user_ip,';
            }
            if (array_key_exists('user_education_history', $argv)) {
                $set .= 'user_education_history=:user_education_history,';
            }
            if (array_key_exists('user_location', $argv)) {
                $set .= 'user_location=:user_location,';
            }
            if (array_key_exists('user_creation_date', $argv)) {
                $set .= 'user_creation_date=:user_creation_date,';
            }

        if (empty($set)){
            return false;
        }

        $sql .= substr($set, 0, -1);

        $pdo = self::database();

        $sql .= ' WHERE  user_id=UNHEX('.self::addInjection($primary, $pdo).')';

        

        $stmt = $pdo->prepare($sql);

        if (!self::bind($stmt, $argv)){
            return false;
        }

        $return = array_merge($return, $argv);

        return true;

    }

    /**
    * @param array $remove
    * @param string|null $primary
    * @param array $argv
    * @return bool
    */
    public static function Delete(array &$remove, string $primary = null, array $argv) : bool
    {
        return carbons::Delete($remove, $primary, $argv);
    }
}
