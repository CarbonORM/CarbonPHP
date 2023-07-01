<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Terms_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Terms $carbon_wp_terms;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_terms = new Wp_Terms($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $term_id;

    public ?string $name;

    public ?string $slug;

    public ?int $term_group;

}

