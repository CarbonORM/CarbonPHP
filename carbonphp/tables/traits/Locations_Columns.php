<?php

namespace CarbonPHP\Tables\Traits;

trait Locations_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Locations $carbon_locations;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_locations = new Locations($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $entity_id;

    public ?string $latitude;

    public ?string $longitude;

    public ?string $street;

    public ?string $city;

    public ?string $state;

    public ?string $elevation;

    public ?int $zip;

}

