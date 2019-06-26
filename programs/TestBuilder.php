<?php


namespace CarbonPHP\Programs;

use CarbonPHP\interfaces\iCommand;

class TestBuilder implements iCommand
{

    public function usage()
    {
        print 'I dont know what this will be!';
    }

    public function run($argv)
    {
        // TODO: Implement run() method.
    }

    public function cleanUp($argv)
    {
        // TODO: Implement cleanUp() method.
    }
}