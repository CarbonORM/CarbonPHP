<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Users_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Users $carbon_wp_users;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_users = new Wp_Users($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $ID;

    public ?string $user_login;

    public ?string $user_pass;

    public ?string $user_nicename;

    public ?string $user_email;

    public ?string $user_url;

    public ?string $user_registered;

    public ?string $user_activation_key;

    public ?int $user_status;

    public ?string $display_name;

}

