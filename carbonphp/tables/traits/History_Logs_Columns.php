<?php

namespace CarbonPHP\Tables\Traits;

trait History_Logs_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public History_Logs $carbon_history_logs;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_history_logs = new History_Logs($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $history_uuid;

    public ?string $history_uri;

    public ?string $history_table;

    public ?string $history_type;

    public ?array $history_request;

    public ?array $history_response;

    public ?string $history_query;

    public ?string $history_time;

    public ?string $history_updated;

}

