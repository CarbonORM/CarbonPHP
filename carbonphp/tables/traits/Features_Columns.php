<?php

namespace CarbonPHP\Tables\Traits;

trait Features_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Features $carbon_features;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_features = new Features($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $feature_entity_id;

    public ?string $feature_code;

    public ?string $feature_creation_date;

}

