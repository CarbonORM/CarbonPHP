<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Comments_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Comments $carbon_wp_comments;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_comments = new Wp_Comments($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $comment_ID;

    public ?int $comment_post_ID;

    public ?string $comment_author;

    public ?string $comment_author_email;

    public ?string $comment_author_url;

    public ?string $comment_author_IP;

    public ?string $comment_date;

    public ?string $comment_date_gmt;

    public ?string $comment_content;

    public ?int $comment_karma;

    public ?string $comment_approved;

    public ?string $comment_agent;

    public ?string $comment_type;

    public ?int $comment_parent;

    public ?int $user_id;

}

