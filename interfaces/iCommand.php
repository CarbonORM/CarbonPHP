<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/3/19
 * Time: 11:20 PM
 */

namespace CarbonPHP\interfaces;


interface iCommand
{
    public function usage();
    public function run($argv);
    public function cleanUp($argv);
}