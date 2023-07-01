<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Commentmeta_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Commentmeta $carbon_wp_commentmeta;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_commentmeta = new Wp_Commentmeta($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $meta_id;

    public ?int $comment_id;

    public ?string $meta_key;

    public ?string $meta_value;

}

