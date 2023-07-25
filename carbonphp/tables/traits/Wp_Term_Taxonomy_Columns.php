<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Term_Taxonomy_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Term_Taxonomy $carbon_wp_term_taxonomy;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_term_taxonomy = new Wp_Term_Taxonomy($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $term_taxonomy_id;

    public ?int $term_id;

    public ?string $taxonomy;

    public ?string $description;

    public ?int $parent;

    public ?int $count;

}

