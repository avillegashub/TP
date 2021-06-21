<?php
require_once './models/EstadoUsuario.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\EstadoUsuario as EstadoUsuarioORM;

class EstadoUsuarioController implements IApiUsable{

  public function TraerTodos($request, $response, $args)
    {
        $lista = EstadoUsuarioORM::all();
        $payload = json_encode(array("listaPedido" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
        }

  public function TraerUno($request, $response, $args)
    {
        try {
          $response->getBody()->write(json_encode(PedidoORM::findOrFail($args['id'])));
        } catch (Exception $ex) {
          $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
          ->withHeader('Content-Type', 'application/json');
        }
        
  public function TraerPedidoPorEstacion($request, $response, $args)
    {
        echo $args['estacion'];
        $listaPedidos = json_encode(EstadoUsuarioORM::all()->where('estacion', $args['estacion'])->where('estado', 'pendiente'));
          $response->getBody()->write($listaPedidos);
        
        return $response
          ->withHeader('Content-Type', 'application/json');
        }

  
  public static function ServirPedido()
  {
    echo ((new DateTime())->getTimestamp ( ));
    echo "<br>----------------------";
    $aux = json_decode(file_get_contents("php://input"));
    $estadoUsuario = EstadoUsuarioORM::find($aux->id);
    $estadoUsuario->estado = "Servir";
    $estadoUsuario->finalizada_preparacion = (new DateTime())->getTimestamp ( );
    $estadoUsuario->save();

  }
  public static function PrepararPedido($tiempoEstimado)
  {
    echo ((new DateTime())->getTimestamp ( ));
    echo "<br>----------------------";
    $aux = json_decode(file_get_contents("php://input"));
    $estadoUsuario = EstadoUsuarioORM::find($aux->id);
    $estadoUsuario->estado = "Preparando";
    $estadoUsuario->tiempo_estimado = $tiempoEstimado;
    $estadoUsuario->comienzo_preparacion = (new DateTime())->getTimestamp ( );
    $estadoUsuario->save();



  }

    public function CargarUno($request, $response, $args)
    {

    }

    public static function AltaEstadoUsuario($usr)
    {
      $estadoUsuario = new EstadoUsuarioORM();
      $estadoUsuario->id_usuario = $usr->id;
      if($usr->tipo == 'cliente')
      {
        $estadoUsuario->estado = "Activo";
      }
      else{
        $estadoUsuario->estado = "Disponible";
      }
      $estadoUsuario->save();
    }

    public static function ModificarEstadoUsuario($usr, $estado)
    {
      $estadoUsuario = EstadoUsuarioORM::find($usr);
      $estadoUsuario->estado = $estado;
      $estadoUsuario->save();

      
    }



    public static function AltaPedido($pedido)
    {
      // Creamos el Pedido
      $estadoUsuario = new EstadoUsuarioORM();
      $estadoUsuario->id_cuenta = $pedido->id_cuenta;
      $estadoUsuario->id_pedido = $pedido->id;
      $estadoUsuario->id_producto = $pedido->id_producto;
      $estadoUsuario->estado = "pendiente";
      $estadoUsuario->estacion = ProductoController::TraerPorId($estadoUsuario->id_producto);

      $estadoUsuario->save();
      //Creo El Estado de este Pedido()
      $payload = json_encode(array("mensaje" => "Alta Exitosa"));
/*
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
        */
    }

        public function ModificarUno($request, $response, $args)
        {
                // Creamos el Pedido
            $Pedido = new PedidoORM();
            $aux = new PedidoORM();
            $aux = json_decode(file_get_contents("php://input"));
            $Pedido =  $Pedido->find($aux->id);
            $Pedido->Pedido = $aux->Pedido;
            $Pedido->nombre = $aux->nombre;
            $Pedido->apellido = $aux->apellido;
            $Pedido->clave = md5($aux->clave);
            $Pedido->tipo = $aux->tipo;
            $Pedido->save();
            $payload = json_encode(array("mensaje" => "Modificación Exitosa"));
            $response->getBody()->write("$payload");
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        public function BorrarUno($request, $response, $args)
        {
          try {

            $Pedido = new PedidoORM();
            $un_Pedido =  $Pedido->find(json_decode(file_get_contents("php://input"))->id);
            $response->getBody()->write(json_encode($un_Pedido->delete()));
          } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
          }
          return $response
            ->withHeader('Content-Type', 'application/json');
        }

    
}
