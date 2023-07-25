<?php

namespace CarbonPHP\Tables\Traits;

trait Group_References_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Group_References $carbon_group_references;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_group_references = new Group_References($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $group_id;

    public ?string $allowed_to_grant_group_id;

}

