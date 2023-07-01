<?php

namespace CarbonPHP\Tables\Traits;

trait Carbons_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Carbons $carbon_carbons;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_carbons = new Carbons($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $entity_pk;

    public ?string $entity_fk;

    public ?string $entity_tag;

}

