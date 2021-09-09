<?php
/**
 * Created by IntelliJ IDEA.
 * User: rmiles
 * Date: 6/26/2018
 * Time: 3:21 PM
 */

declare(strict_types=1);

namespace Tests\Feature;


final class WordpressTest extends Config
{
    private const ERROR_MESSAGE = 'Wordpress internal login failed in testing. This could mean your not running your test class using the @runTestsInSeparateProcesses annotation, output was sent to stdout, or your build is upto date with stage. See the PHPDOC block on WordpressTest for more information on debugging this.';

    public function testWordpressCanQueryUsers(): void
    {
        wp_set_current_user(1);

        $id = get_current_user_id();

        self::assertEquals(1, $id, self::ERROR_MESSAGE);
    }

    public function testWordpressCanQueryUsersAcrossMultipleTests(): void
    {
        wp_set_current_user(1);

        $id = get_current_user_id();

        self::assertEquals(1, $id, self::ERROR_MESSAGE);
    }
}