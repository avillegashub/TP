<?php
require_once './models/Cuenta.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Cuenta as cuentaORM;

class CuentaController implements IApiUsable
{

    public function TraerTodos($request, $response, $args)
    {
        $lista = productoORM::all();
        $payload = json_encode(array("listaProducto" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        try {
            $response->getBody()->write(json_encode(cuentaORM::findOrFail($args['id'])));
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args)
    {
      //Verificar con middleWare que todos existan y esten disponibles.
      //MIddleware Guardar Foto
        $parametros = $request->getParsedBody();
        $cuenta = new CuentaORM();
        $cuenta->id_usuario = $parametros['id_usuario'];
        $cuenta->id_mozo = $parametros['id_mozo'];
        $cuenta->id_mesa = $parametros['id_mesa'];
        $cuenta->save();
        $cuenta->all()->last();
        $payload = json_encode($cuenta);
        //Comienzo EstadoDeCuenta()
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        // Creamos el usuario
        $cuenta = new CuentaORM();
        $aux = new CuentaORM();
        $aux = json_decode(file_get_contents("php://input"));
        $cuenta = $cuenta->find($aux->id);
        $cuenta->nombre = $aux->nombre;
        $cuenta->precio = $aux->precio;
        $cuenta->estacion = $aux->estacion;
        $cuenta->save();
        $payload = json_encode(array("mensaje" => "Cuenta modificado con exito"));
        $response->getBody()->write("$payload");
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        try {

            $cuenta = new CuentaORM();
            $un_producto = $cuenta->find(json_decode(file_get_contents("php://input"))->id);
            $response->getBody()->write(json_encode($un_producto->delete()));
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
