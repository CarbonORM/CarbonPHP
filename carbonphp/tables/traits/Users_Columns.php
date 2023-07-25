<?php

namespace CarbonPHP\Tables\Traits;

trait Users_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Users $carbon_users;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_users = new Users($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $user_username;

    public ?string $user_password;

    public ?string $user_id;

    public ?string $user_type;

    public ?string $user_sport;

    public ?string $user_session_id;

    public ?string $user_facebook_id;

    public ?string $user_first_name;

    public ?string $user_last_name;

    public ?string $user_profile_pic;

    public ?string $user_profile_uri;

    public ?string $user_cover_photo;

    public ?string $user_birthday;

    public ?string $user_gender;

    public ?string $user_about_me;

    public ?int $user_rank;

    public ?string $user_email;

    public ?string $user_email_code;

    public ?int $user_email_confirmed;

    public ?string $user_generated_string;

    public ?int $user_membership;

    public ?int $user_deactivated;

    public ?string $user_last_login;

    public ?string $user_ip;

    public ?string $user_education_history;

    public ?string $user_location;

    public ?string $user_creation_date;

}

