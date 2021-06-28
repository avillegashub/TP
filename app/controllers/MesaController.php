<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Mesa as mesaORM;

class MesaController implements IApiUsable
{

    public function TraerTodos($request, $response, $args)
    {
        $lista = mesaORM::all();
        $payload = json_encode(array("listaMesa" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerDisponibles($request, $response, $args)
    {
        $lista = mesaORM::all()->where('estado',1)->where('usuario', null);
        $payload = json_encode(array("listaMesa" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function CambiarEstado($mesa, $estado, $usuario)
    {
      mesaORM::where('id', $mesa)
      ->update(['usuario' => $usuario]);
      mesaORM::where('id', $mesa)
      ->update(['estado' => $estado]);
    }

    public function TraerUno($request, $response, $args)
    {
        try {
            $response->getBody()->write(json_encode(mesaORM::findOrFail($args['id'])));
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args)
    {
        // Creamos el usuario
        $parametros = $request->getParsedBody();
        $mesa = new MesaORM();
        $mesa->nombre = $parametros['nombre'];
        $mesa->save();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        // Creamos el usuario
        $mesa = new MesaORM();
        $aux = new MesaORM();
        $aux = json_decode(file_get_contents("php://input"));
        $mesa = $mesa->find($aux->id);
        $mesa->nombre = $aux->nombre;
        $mesa->save();
        $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
        $response->getBody()->write("$payload");
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        try {

            $mesa = new MesaORM();
            $una_mesa = $mesa->find(json_decode(file_get_contents("php://input"))->id);
            $response->getBody()->write(json_encode($una_mesa->delete()));
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
