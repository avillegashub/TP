<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';
require_once './controllers/EstadoPedidoController.php';
require_once './controllers/PedidoCanceladoController.php';

use \App\Models\Pedido as PedidoORM;


class PedidoController implements IApiUsable
{

    public function TraerTodos($request, $response, $args)
    {
        $lista = PedidoORM::all();
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

    public function TraerPedidoPorEstacionTrash($request, $response, $args)
    {

        $response->getBody()->write(json_encode());

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function CancelarEntregar($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
    
        if(isset($arr['cancelar']))
        {
          $payload = self::CancelarPedido();
          $response->getBody()->write("Orden Cancelada");
        }
        else{
          $payload = self::PedidoEntregado();
          $response->getBody()->write("Pedido Entregado");
        }
        return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function CancelarPedido()
    {
        $aux = json_decode(file_get_contents("php://input"));
        PedidoCanceladoController::GuardarCancelado();
        $pedido = PedidoORM::find($aux->id_pedido);
        $pedido->forceDelete();

        EstadoPedidoController::CancelarPedido($aux->id);
        
    }
    
    public function PedidoEntregado()
    {
        $aux = json_decode(file_get_contents("php://input"));
        $pedido = PedidoORM::find($aux->id);
        $pedido->delete();
    
        EstadoPedidoController::PedidoEntregado($aux->id);
        
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
        EstadoPedidoController::AltaPedido($pedido->all()->last());
        
        $payload = json_encode(array("mensaje" => "Alta Exitosa"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        // Creamos el Pedido
        $Pedido = new PedidoORM();
        $aux = new PedidoORM();
        $aux = json_decode(file_get_contents("php://input"));
        $Pedido = $Pedido->find($aux->id);
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
            $un_Pedido = $Pedido->find(json_decode(file_get_contents("php://input"))->id);
            $response->getBody()->write(json_encode($un_Pedido->delete()));
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

}
