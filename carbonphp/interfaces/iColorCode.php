<?php


namespace CarbonPHP\Interfaces;


interface iColorCode
{
    public const BOLD = 'bold';
    public const DARK = 'dark';
    public const ITALIC = 'italic';
    public const UNDERLINE = 'underline';
    public const BLINK = 'blink';
    public const REVERSE = 'reverse';
    public const CONCEALED = 'concealed';
    public const BLACK = 'black';
    public const RED = 'red';
    public const GREEN = 'green';
    public const YELLOW = 'yellow';
    public const BLUE = 'blue';
    public const MAGENTA = 'magenta';
    public const CYAN = 'cyan';
    public const WHITE = 'white';
    public const BACKGROUND_BLACK = 'background_black';
    public const BACKGROUND_RED = 'background_red';
    public const BACKGROUND_GREEN = 'background_green';
    public const BACKGROUND_YELLOW = 'background_yellow';
    public const BACKGROUND_BLUE = 'background_blue';
    public const BACKGROUND_MAGENTA = 'background_magenta';
    public const BACKGROUND_CYAN = 'background_cyan';
    public const BACKGROUND_WHITE = 'background_white';


    public const PRINTF_ANSI_COLOR = [
        // styles
        // italic and blink may not work depending of your terminal
        iColorCode::BOLD => "\033[1m%s\033[0m",
        iColorCode::DARK => "\033[2m%s\033[0m",
        iColorCode::ITALIC => "\033[3m%s\033[0m",
        iColorCode::UNDERLINE => "\033[4m%s\033[0m",
        iColorCode::BLINK => "\033[5m%s\033[0m",
        iColorCode::REVERSE => "\033[7m%s\033[0m",
        iColorCode::CONCEALED => "\033[8m%s\033[0m",
        // foreground colors
        iColorCode::BLACK => "\033[30m%s\033[0m",
        iColorCode::RED => "\033[31m%s\033[0m",
        iColorCode::GREEN => "\033[32m%s\033[0m",
        iColorCode::YELLOW => "\033[33m%s\033[0m",
        iColorCode::BLUE => "\033[34m%s\033[0m",
        iColorCode::MAGENTA => "\033[35m%s\033[0m",
        iColorCode::CYAN => "\033[36m%s\033[0m",
        iColorCode::WHITE => "\033[37m%s\033[0m",
        // background colors
        iColorCode::BACKGROUND_BLACK => "\033[40m%s\033[0m",
        iColorCode::BACKGROUND_RED => "\033[41m%s\033[0m",
        iColorCode::BACKGROUND_GREEN => "\033[42m%s\033[0m",
        iColorCode::BACKGROUND_YELLOW => "\033[43m%s\033[0m",
        iColorCode::BACKGROUND_BLUE => "\033[44m%s\033[0m",
        iColorCode::BACKGROUND_MAGENTA => "\033[45m%s\033[0m",
        iColorCode::BACKGROUND_CYAN => "\033[46m%s\033[0m",
        iColorCode::BACKGROUND_WHITE => "\033[47m%s\033[0m",
    ];

    /**
     * @param string $message
     * @param string $color
     * @param bool $exit
     * @param int $priority
     */
    public static function colorCode(string $message, string $color = self::GREEN): void;
}