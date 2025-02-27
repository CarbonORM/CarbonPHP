<?php

namespace CarbonPHP\Interfaces;

use CarbonPHP\Classes\Configurations\DatabaseHost;

interface iDatabasePool
{
    public function searchConfigurations(bool $readerSearch): DatabaseHost;
    public function searchMostAvailableHost(bool $readerSearch): DatabaseHost;
}