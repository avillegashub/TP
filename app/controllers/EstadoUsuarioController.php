<?php
require_once './models/EstadoUsuario.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\EstadoUsuario as EstadoUsuarioORM;

class EstadoUsuarioController
{

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

    public static function AltaEstadoUsuario($usr)
    {
        $estadoUsuario = new EstadoUsuarioORM();
        $estadoUsuario->id_usuario = $usr->id;
     
            $estadoUsuario->estado = "LoggedIn";
        $estadoUsuario->save();
    }

    public static function BajaEstadoUsuario($usr)
    {
        $estadoUsuario = new EstadoUsuarioORM();
        $estadoUsuario = EstadoUsuarioORM::where('id_usuario', $usr->id)->first();
        $estadoUsuario->estado = "LoggedOut";
        $estadoUsuario->save();
        $estadoUsuario->delete();
    }

    public static function ModificarEstadoUsuario($usr, $estado)
    {
        $estadoUsuario = EstadoUsuarioORM::find($usr);
        $estadoUsuario->estado = $estado;
        $estadoUsuario->save();

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
