<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

    protected $fillable=[
        'title',
        'description',
        'startDate',
        'endDate',
        'startClock',
        'endClock',
        'image',
        'type',
        'address',
        'link',
        'status',
        'eventState',
        'approvalLevel',
        'user_id'
    ];

}
