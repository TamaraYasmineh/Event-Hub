<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Add extends Model
{
    protected $fillable=[
        'link',
        'NameOfTheOwnerCompany',
        'viewPlace',
        'active',
        'startDate',
        'endDate',
        'image',
        'hits',
    ];
}
