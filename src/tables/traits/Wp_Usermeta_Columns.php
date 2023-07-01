<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Usermeta_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Usermeta $carbon_wp_usermeta;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_usermeta = new Wp_Usermeta($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $umeta_id;

    public ?int $user_id;

    public ?string $meta_key;

    public ?string $meta_value;

}

