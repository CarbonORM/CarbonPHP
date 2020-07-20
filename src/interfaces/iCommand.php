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
    public function usage(): void;
    public function run(array $argv): void;
    public function cleanUp(array $argv): void;
}