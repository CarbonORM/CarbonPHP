<?php

namespace CarbonPHP\Tables\Traits;

trait Reports_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public Reports $carbon_reports;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_reports = new Reports($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $log_level;

    public ?string $report;

    public ?string $date;

    public ?string $call_trace;

}

