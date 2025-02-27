<?php

declare(strict_types=1);

namespace CarbonPHP\Abstracts;

use Random\RandomException;

abstract class Generate
{
    /** http://php.net/manual/en/language.operators.bitwise.php
     * in actual system we will have to see what bit system we are using
     * 32 bit = 28
     * 64 bit = 60
     * I assume this means php uses 4 bits to denote type (z_val) ?
     * idk
     *
     * @param int $bitLength
     *
     * @return string
     *
     * @throws RandomException
     */
    public static function randomHex(int $bitLength = 40): string
    {
        $r = 1;
        for ($i = 0; $i <= $bitLength; ++$i) {
            $r = ($r << 1) | random_int(0, 1);
        }

        return dechex($r);
    }
}
