<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encuesta extends Model{

    use SoftDeletes;
    

    public $incrementing = true;
    public $timestamps = true;

    const DELETED_AT = 'fechaBaja';

    protected $fillable = ['id','mesa','restaurante','mozo', 'cocina','comentarios'];
    
}