<?php
require_once './models/Pedidoscancelado.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Pedidoscancelado as pedidoscanceladoORM;

class PedidoCanceladoController{

  public static function GuardarCancelado()
  {

      $aux = json_decode(file_get_contents("php://input"));
      $guardar = new pedidoscanceladoORM();
      $guardar->id_producto = $aux->id_producto;
      $guardar->id_cuenta = $aux->id_cuenta;
      $guardar->save();


  }


}
