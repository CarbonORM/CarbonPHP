<?php
/**
 * Created by IntelliJ IDEA.
 * User: richardmiles
 * Date: 2/25/18
 * Time: 3:20 AM
 */

namespace CarbonPHP;


use CarbonPHP\Error\ThrowableHandler;
use CarbonPHP\Error\PublicAlert;
use Error;
use Mustache_Exception_InvalidArgumentException;
use Throwable;

abstract class Application extends Route
{

    /**
     * This functions should change the $matched property in the extended Route class to true.
     * public bool $matched = false;
     * This can happen by using the Routing methods to produce a match, or setting it explicitly.
     * If the start application method finishes with $matched = false; your defaultRoute() method will run.
     *
     *
     * @param string $uri
     * @return bool  - REQUIRED, it seems like we use it anywhere at first glance, but its apart of a
     *                  recursive ending condition for Application::ControllerModelView
     */
    abstract public function startApplication(string $uri): bool;

}
