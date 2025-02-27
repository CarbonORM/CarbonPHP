<?php

declare(strict_types=1);

namespace CarbonPHP\Interfaces;

interface iColorCode
{
    public const string BOLD = 'bold';

    public const string DARK = 'dark';

    public const string ITALIC = 'italic';

    public const string UNDERLINE = 'underline';

    public const string BLINK = 'blink';

    public const string REVERSE = 'reverse';

    public const string CONCEALED = 'concealed';

    public const string BLACK = 'black';

    public const string RED = 'red';

    public const string GREEN = 'green';

    public const string YELLOW = 'yellow';

    public const string BLUE = 'blue';

    public const string MAGENTA = 'magenta';

    public const string CYAN = 'cyan';

    public const string WHITE = 'white';

    public const string BACKGROUND_BLACK = 'background_black';

    public const string BACKGROUND_RED = 'background_red';

    public const string BACKGROUND_GREEN = 'background_green';

    public const string BACKGROUND_YELLOW = 'background_yellow';

    public const string BACKGROUND_BLUE = 'background_blue';

    public const string BACKGROUND_MAGENTA = 'background_magenta';

    public const string BACKGROUND_CYAN = 'background_cyan';

    public const string BACKGROUND_WHITE = 'background_white';

    public const array PRINTF_ANSI_COLOR = [
        // styles
        // italic and blink may not work depending of your terminal
        self::BOLD      => "\033[1m%s\033[0m",
        self::DARK      => "\033[2m%s\033[0m",
        self::ITALIC    => "\033[3m%s\033[0m",
        self::UNDERLINE => "\033[4m%s\033[0m",
        self::BLINK     => "\033[5m%s\033[0m",
        self::REVERSE   => "\033[7m%s\033[0m",
        self::CONCEALED => "\033[8m%s\033[0m",
        // foreground colors
        self::BLACK   => "\033[30m%s\033[0m",
        self::RED     => "\033[31m%s\033[0m",
        self::GREEN   => "\033[32m%s\033[0m",
        self::YELLOW  => "\033[33m%s\033[0m",
        self::BLUE    => "\033[34m%s\033[0m",
        self::MAGENTA => "\033[35m%s\033[0m",
        self::CYAN    => "\033[36m%s\033[0m",
        self::WHITE   => "\033[37m%s\033[0m",
        // background colors
        self::BACKGROUND_BLACK   => "\033[40m%s\033[0m",
        self::BACKGROUND_RED     => "\033[41m%s\033[0m",
        self::BACKGROUND_GREEN   => "\033[42m%s\033[0m",
        self::BACKGROUND_YELLOW  => "\033[43m%s\033[0m",
        self::BACKGROUND_BLUE    => "\033[44m%s\033[0m",
        self::BACKGROUND_MAGENTA => "\033[45m%s\033[0m",
        self::BACKGROUND_CYAN    => "\033[46m%s\033[0m",
        self::BACKGROUND_WHITE   => "\033[47m%s\033[0m",
    ];

    /**
     * @param string $message
     * @param string $color
     * @param bool $exit
     * @param int $priority
     */
    public static function colorCode(string $message, string $color = self::GREEN): void;
}
