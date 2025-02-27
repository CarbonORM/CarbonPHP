<?php

declare(strict_types=1);

/* Create a new class called Bcrypt */

namespace CarbonPHP\Abstracts;


use CarbonPHP\Classes\ThrowableHandler;
use CarbonPHP\Throwables\PrivateAlert;
use CarbonPHP\Throwables\PublicAlert;

abstract class Cryptography extends Generate
{
    private static $rounds = 10;

    // http://php.net/manual/en/language.operators.bitwise.php
    // in actual system we will have to see what bit system we are using
    // 32 bit = 28
    // 64 biit = 60
    // I assume this means php uses 4 bits to denote type (vzal) ?
    // idk
    // godaddy has me on a 64 bit computer
    public static function genRandomHex($bitLength = 40): string
    {
        try {
            // Generate secure random bytes
            $bytes = random_bytes($bitLength);

            // Convert the bytes to a hexadecimal string
            return bin2hex($bytes);
        } catch (\Exception $e) {
            ThrowableHandler::generateLogAndExit($e);
        }
    }

    private static function genSalt()
    {
        /* GenSalt */
        $string = str_shuffle((string)mt_rand());

        return uniqid($string, true);
    }

    /**
     * @param $password
     *
     * @return string|null
     *
     * @throws PublicAlert
     */
    public static function genHash(string $password, int $rounds = 10): ?string
    {
        if (CRYPT_BLOWFISH !== 1) {
            throw new PrivateAlert('Bcrypt is not supported on this server, please see the following to learn more: http://php.net/crypt');
        }

        /* Explain '$2y$' . $this->rounds . '$' */
        /* 2y selects bcrypt algorithm */
        /* $this->rounds is the workload factor */
        /* GenHash */
        /* Return - The output includes the salt in the one way encode */
        return crypt($password, '$2y$'.self::$rounds.'$'.self::genSalt());
    }

    /* Verify Password */
    /**
     * @param $password
     * @param $existingHash
     *
     * @return bool
     */
    public static function verify($password, $existingHash): bool
    {
        /* Hash new password with old hash */
        /* The existing hash is in the output, so this essentially just runs the original crypt function and compares again */
        $hash = crypt($password, $existingHash);

        /* Do Hashes match? */
        return $hash === $existingHash;
    }
}
