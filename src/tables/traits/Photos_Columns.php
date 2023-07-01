<?php

namespace CarbonPHP\Tables\Traits;

trait Photos_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Photos $carbon_photos;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_photos = new Photos($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $parent_id;

    public ?string $photo_id;

    public ?string $user_id;

    public ?string $photo_path;

    public ?string $photo_description;

}

