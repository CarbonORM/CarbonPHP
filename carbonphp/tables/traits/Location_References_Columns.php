<?php

namespace CarbonPHP\Tables\Traits;

trait Location_References_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Location_References $carbon_location_references;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_location_references = new Location_References($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $entity_reference;

    public ?string $location_reference;

    public ?string $location_time;

}

