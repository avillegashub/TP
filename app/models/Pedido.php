<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model{

    use SoftDeletes;
    
    protected $primaryKey = 'id';

    public $incrementing = true;
    public $timestamps = true;

    const DELETED_AT = 'fechaBaja';

    protected $fillable = ['id_cuenta', 'id_producto'];






}


?>





