<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 10/15/17
 * Time: 5:59 PM
 */

use Carbon\Route;
use Carbon\View;
use Carbon\Request;
use Carbon\Error\PublicAlert;


const CONTENT = SERVER_ROOT . 'Public' . DS;


$url = Route::setInstance(new class extends Route        // Start the route with the structure of the default route const
{
    public function defaultRoute($run = false): void
    {
        if (SOCKET) return;
        if ($run) View::contents(CONTENT . 'Carbon.php') and die;
    }
});

$url->defaultRoute();


$html = function (...$args) {
    // These args are not used in this example, but they can be if desired
    View::contents(CONTENT . 'home.php') and exit(1);
};

$url->match('Home/', 'CustomArgs', 'Variable')->closure($html);

$url->match('{variable}/*', function ($variable) use ($html){

    if ($variable = (new Request())->set($variable)->noHTML()->alnum())
        alert($variable);
    else
        PublicAlert::danger('Urls should be alphanumeric.');

    $html();
});


exit(1);