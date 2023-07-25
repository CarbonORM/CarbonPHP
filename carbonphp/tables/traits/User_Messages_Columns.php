<?php

namespace CarbonPHP\Tables\Traits;

trait User_Messages_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public User_Messages $carbon_user_messages;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_user_messages = new User_Messages($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $message_id;

    public ?string $from_user_id;

    public ?string $to_user_id;

    public ?string $message;

    public ?int $message_read;

    public ?string $creation_date;

}

