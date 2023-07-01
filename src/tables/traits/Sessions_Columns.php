<?php

namespace CarbonPHP\Tables\Traits;

trait Sessions_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Sessions $carbon_sessions;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_sessions = new Sessions($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $user_id;

    public ?string $user_ip;

    public ?string $session_id;

    public ?string $session_expires;

    public ?string $session_data;

    public ?int $user_online_status;

}

