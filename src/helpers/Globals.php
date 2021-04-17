<?php

namespace CarbonPHP\Helpers;

use CarbonPHP\Singleton;

/**
 * Class Globals
 * @package Carbon\Helpers
 *
 * I only use this in the fetch_to_global()
 * method in the Carbon/Entities Class.
 * Singleton uses the __set() magic method on the global
 * scope and allows for easy closure binding. Its almost
 * guaranteed to be loaded if this file is run, so lets
 * just use it.
 *
 * Hints: intellij users can double-click the shift key
 * to find files buried deep in the file layout.
 * Command clicking functions, methods, and classes
 * wil aslo take you to there implementation.
 */
class Globals { use Singleton; }