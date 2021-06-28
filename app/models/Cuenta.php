<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cuenta extends Model{

    use SoftDeletes;
    
    protected $primaryKey = 'id';

    public $incrementing = true;
    public $timestamps = true;

    const DELETED_AT = 'fechaBaja';

    protected $fillable = ['id_cliente','id_mesa','id_mozo', 'monto'];
    
}