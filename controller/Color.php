<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 3/1/19
 * Time: 12:28 AM
 */

namespace CarbonPHP\Controller;

class Color
{
    public function color() : ?bool {

        global $json;

        $json['colorCode'] = print_r($json, true);

        $_POST and sortDump($headers = apache_request_headers());

        return null;
    }
}