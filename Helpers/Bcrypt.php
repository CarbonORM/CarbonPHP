<?php
/* Create a new class called Bcrypt */
/*
 *
*/

namespace CarbonPHP\Helpers;

class Bcrypt
{
    private static $rounds = 10;


    // http://php.net/manual/en/language.operators.bitwise.php
    // in actual system we will have to see what bit system we are using
    // 32 bit = 28
    // 64 biit = 60
    // I assume this means php uses 4 bits to denote type (vzal) ?
    // idk
    // godaddy has me on a 64 bit computer
    public static function genRandomHex($bitLength = 40)
    {
        $sudoRandom = 1;
        for ($i=0;$i<=$bitLength;$i++) $sudoRandom = ($sudoRandom<<1)|rand(0,1);
        return dechex($sudoRandom);
    }
    
    private static function genSalt()
    {
        /* GenSalt */
        $string = str_shuffle( mt_rand() );
        return uniqid( $string, true );
    }

    /* Gen Hash */
    public static function genHash($password)
    {
        if (CRYPT_BLOWFISH != 1)
            throw new \Exception( "Bcrypt is not supported on this server, please see the following to learn more: http://php.net/crypt" );

        /* Explain '$2y$' . $this->rounds . '$' */
        /* 2y selects bcrypt algorithm */
        /* $this->rounds is the workload factor */
        /* GenHash */
        $hash = crypt( $password, '$2y$' . self::$rounds . '$' . self::genSalt() );
        /* Return */
        return $hash;
    }

    /* Verify Password */
    public static function verify($password, $existingHash)
    {
        /* Hash new password with old hash */

        $hash = crypt( $password, $existingHash );

        /* Do Hashs match? */
        if ($hash === $existingHash) {
            return true;
        } else {
            return false;
        }
    }
}