<?php

namespace CarbonPHP\Tables\Traits;

trait Comments_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Comments $carbon_comments;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_comments = new Comments($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $parent_id;

    public ?string $comment_id;

    public ?string $user_id;

    public ?string $comment;

}

