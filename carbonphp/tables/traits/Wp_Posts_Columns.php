<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Posts_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Posts $carbon_wp_posts;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_posts = new Wp_Posts($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $ID;

    public ?int $post_author;

    public ?string $post_date;

    public ?string $post_date_gmt;

    public ?string $post_content;

    public ?string $post_title;

    public ?string $post_excerpt;

    public ?string $post_status;

    public ?string $comment_status;

    public ?string $ping_status;

    public ?string $post_password;

    public ?string $post_name;

    public ?string $to_ping;

    public ?string $pinged;

    public ?string $post_modified;

    public ?string $post_modified_gmt;

    public ?string $post_content_filtered;

    public ?int $post_parent;

    public ?string $guid;

    public ?int $menu_order;

    public ?string $post_type;

    public ?string $post_mime_type;

    public ?int $comment_count;

}

