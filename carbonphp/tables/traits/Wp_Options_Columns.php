<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Options_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Options $carbon_wp_options;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_options = new Wp_Options($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $option_id;

    public ?string $option_name;

    public ?string $option_value;

    public ?string $autoload;

}

