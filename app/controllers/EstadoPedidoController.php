<?php
require_once './models/EstadoPedido.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\EstadoPedido as EstadoPedidoORM;

class EstadoPedidoController implements IApiUsable{

  public function TraerTodos($request, $response, $args)
    {
        $lista = EstadoPedidoORM::all();
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
        $listaPedidos = json_encode(EstadoPedidoORM::all()->where('estacion', $args['estacion'])->where('estado', 'pendiente'));
          $response->getBody()->write($listaPedidos);
        
        return $response
          ->withHeader('Content-Type', 'application/json');
        }

  public function TraerPedidosPorCuenta($request, $response, $args)
    {
      parse_str($_SERVER['QUERY_STRING'], $arr);
        $listaPedidos = json_encode(EstadoPedidoORM::all()->where('id_cuenta', $arr['cuenta']));
        $response->getBody()->write($listaPedidos);
        
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
        
  public function ModificarEstadoPedido($request, $response, $args)
  {   

    parse_str($_SERVER['QUERY_STRING'], $arr);
    
    if(isset($arr['tiempo_estimado']))
    {
      $payload = self::PrepararPedido($arr);
      $response->getBody()->write("En preparación");
    }
    else{
      $payload = self::ServirPedido();
      $response->getBody()->write("Preparado Para servir");
    }

    
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
  
  public static function ServirPedido()
  {

    $aux = json_decode(file_get_contents("php://input"));
    $estadoPedido = EstadoPedidoORM::find($aux->id);
    $estadoPedido->estado = "Servir";
    $estadoPedido->finalizada_preparacion = (new DateTime())->getTimestamp ( );
    $estadoPedido->tiempo_real = ($estadoPedido->comienzo_preparacion+$estadoPedido->tiempo_estimado) - (new DateTime())->getTimestamp ( );
    $estadoPedido->save();

  }
  public static function PrepararPedido($arr)
  {
  
    $aux = json_decode(file_get_contents("php://input"));
    $estadoPedido = EstadoPedidoORM::find($aux->id);
    $estadoPedido->estado = "Preparando";
    $estadoPedido->id_empleado = $arr['id'];
    $estadoPedido->tiempo_estimado = $arr['tiempo_estimado'];
    $estadoPedido->comienzo_preparacion = (new DateTime())->getTimestamp ( );
    $estadoPedido->save();



  }

  public static function CancelarPedido($id)
  {
  
    $estadoPedido = EstadoPedidoORM::whereid_pedido($id);
    $estadoPedido->forceDelete();
  }

  public static function PedidoEntregado($id)
  {
  
    $estadoPedido = EstadoPedidoORM::whereid_pedido($id);
    $estadoPedido->delete();
  }

    public function CargarUno($request, $response, $args)
    {
      // Creamos el Pedido
      $parametros = $request->getParsedBody();
      $pedido = new PedidoORM();
      $pedido->id_cuenta = $parametros['id_cuenta'];
      $pedido->id_producto = $parametros['id_producto'];
      $pedido->save();
      //Creo El Estado de este Pedido()
      $payload = json_encode(array("mensaje" => "Alta Exitosa"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }



    public static function AltaPedido($pedido)
    {
      // Creamos el Pedido
      $estadoPedido = new EstadoPedidoORM();
      $estadoPedido->id_cuenta = $pedido->id_cuenta;
      $estadoPedido->id_pedido = $pedido->id;
      $estadoPedido->id_producto = $pedido->id_producto;
      $estadoPedido->estado = "pendiente";
      $estadoPedido->estacion = ProductoController::TraerPorId($estadoPedido->id_producto);

      $estadoPedido->save();
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
