<?php

use Carbon\Route;
use Carbon\View;

const CONTENT = SERVER_ROOT . 'Public' . DS;

$url = new class extends Route
{
    public function defaultRoute(): void
    {
        View::contents(CONTENT . 'home.php') and die;
    }
};


