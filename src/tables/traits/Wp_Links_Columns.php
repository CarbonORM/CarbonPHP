<?php

namespace CarbonPHP\Tables\Traits;

trait Wp_Links_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Wp_Links $carbon_wp_links;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_wp_links = new Wp_Links($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?int $link_id;

    public ?string $link_url;

    public ?string $link_name;

    public ?string $link_image;

    public ?string $link_target;

    public ?string $link_description;

    public ?string $link_visible;

    public ?int $link_owner;

    public ?int $link_rating;

    public ?string $link_updated;

    public ?string $link_rel;

    public ?string $link_notes;

    public ?string $link_rss;

}

