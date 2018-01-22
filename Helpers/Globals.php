<?php namespace Carbon\Helpers;

use Carbon\Singleton;

/**
 * Class Globals
 * @package Carbon\Helpers
 *
 * I only use this in the fetch_to_global()
 * method in the Carbon/Entities Class.
 * Singleton uses a __set() magic
 *
 * Hints: intellij users can double-click the shift key
 * to find files buried deep in the file layout.
 * Command clicking functions, methods, and classes
 * wil aslo take you to there implementation.
 */
class Globals { use Singleton; }