<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Term_Relationships_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Term_Relationships $carbon_wp_term_relationships;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_term_relationships = new Wp_Term_Relationships($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $object_id;

    public ?int $term_taxonomy_id;

    public ?int $term_order;

}

