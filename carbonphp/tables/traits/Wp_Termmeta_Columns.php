<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Termmeta_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Termmeta $carbon_wp_termmeta;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_termmeta = new Wp_Termmeta($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $meta_id;

    public ?int $term_id;

    public ?string $meta_key;

    public ?string $meta_value;

}

