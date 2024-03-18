<?php

namespace CarbonPHP\Abstracts;

use CarbonPHP\Interfaces\iColorCode;
use CarbonPHP\Route;

abstract class GitHub
{

    public static function hooks(string $prefix = 'github') : bool {

        // @link https://gist.github.com/gka/4627519
        return Route::regexMatch('#' . preg_quote($prefix, '#') . '#i', static function () {

            $json = file_get_contents('php://input'); // Raw POST date from STDIN

            $hash = hash_hmac('sha1', $json, 'PQWL%7!?KLp-kc%C3!uaTqWy7b6TXb'); // Hash from raw POST data

            $server_hash = !empty($_SERVER['HTTP_X_HUB_SIGNATURE']) ? substr($_SERVER['HTTP_X_HUB_SIGNATURE'], 5) : null;

            if ($server_hash !== $hash) {

                ColorCode::colorCode($message = 'Github Update failed to run verify server hash', iColorCode::RED);

                print $message;

                die(0);

            }

            $json = json_decode($json, true, 512,JSON_THROW_ON_ERROR);

            if ('refs/heads/master' === $json['ref']) {

                print shell_exec('git fetch --all && git reset --hard origin/master');

            } else {

                print 'The branch ' . $json['ref'] . ' was parsed. Nothing to do.';

            }

        });

    }


}