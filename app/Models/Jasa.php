<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jasa extends Model
{
    protected $table = 'jasa';
    protected $primaryKey = 'ServCode';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['ServCode', 'NameOfServ', 'ServPrice'];
}
