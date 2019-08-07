<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
   protected $fillable = [
    'first_name', 'last_name', 'preferred_room', 'added_by', 'updated_by'
   ];
}
