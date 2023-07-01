<?php

namespace CarbonPHP\Tables\Traits;

trait User_Tasks_Columns
{
    
    /**
     * COLUMNS
     * Interfacing with the restful return can be done using objects which allow your editor to smartly type fields.
     * The referenced return &$return from any Rest::Get method can be directly passed back into its calling classes 
     *  constructor. One might use these fields below with the following ::
     *
     *    public User_Tasks $carbon_user_tasks;
     *
     * The definition above can be defined with the following ::
     *
     *    $carbon_user_tasks = new User_Tasks($return);
     *
     * @note this method is unnecessary and should be avoided if not needed for clarity of clean code. 
    **/
    public ?string $task_id;

    public ?string $user_id;

    public ?string $from_id;

    public ?string $task_name;

    public ?string $task_description;

    public ?int $percent_complete;

    public ?string $start_date;

    public ?string $end_date;

}

