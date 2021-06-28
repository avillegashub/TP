<?php
require_once './models/Cuenta.php';
require_once './controllers/MesaController.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Cuenta as cuentaORM;

class CuentaController implements IApiUsable
{

    public function TraerTodos($request, $response, $args)
    {
        $lista = cuentaORM::all();
        $payload = json_encode(array("listaProducto" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
    
    public static function TraerPorIdMozo($id)
    {
        $lista = cuentaORM::all()->where('id_mozo', $id);
        $arrayId = array();
        foreach ($lista as $key ) {
            array_push($arrayId, $key->id);
        }
        return $arrayId;
    }

    public function TraerUno($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        try {
            $response->getBody()->write(json_encode(cuentaORM::findOrFail($arr['id'])));
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args)
    {

        $jwt = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $jwt)[1]);
        $payload = array('data'=>AutentificadorJWT::ObtenerData($token));
        $parametros = $request->getParsedBody();
        $cuenta = new CuentaORM();
        $cuenta->id_cliente = $parametros['id_cliente'];
        $cuenta->id_mozo = $payload['data']->id;
        $cuenta->id_mesa = $parametros['id_mesa'];
        $cuenta->save();
        MesaController::CambiarEstado($cuenta->id_mesa, 0, $cuenta->id_mozo);
        $cuenta->all()->last();
        self::SavePhoto($cuenta->id);
        ob_end_clean();
        $response->getBody()->write("Numero de Cuenta: {$cuenta->id}");  
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function SavePhoto($id)
    {
        if(isset($_FILES["foto"]))
        {
            $aux = $_FILES["foto"]['name'];
            $fileExt = pathinfo($aux, PATHINFO_EXTENSION);
            $path = "./files/fotos/" . $id . "." . $fileExt;
            move_uploaded_file($_FILES["foto"]["tmp_name"],  $path);
        }
    }

    public static function GetCuenta($id)
    {
        $item = new CuentaORM();
        return $item->find($id);
    }

    public static function CambiarEstado($id, $estado)
    {
      CuentaORM::where('id', $id)
      ->update(['estado' => $estado]);
    }
    
    public function CerrarCuenta($request, $response, $args)
    {
        $aux = json_decode(file_get_contents("php://input"));
       
        $cuenta = self::GetCuenta($aux->id);
        self::CambiarEstado($aux->id, "Cerrada");
        self::SumarMonto($aux->id);
        MesaController::CambiarEstado($cuenta->id_mesa, 1, NULL);
        CuentaORM::where('id', $aux->id)->delete();
        ob_end_clean();
        $response->getBody()->write("Cuenta Cerrada");
        return $response
            ->withHeader('Content-Type', 'application/json');

    }

    public static function SumarMonto($id)
    {
      CuentaORM::where('id', $id)->update(['monto' => PedidoController::GetSum($id)]);
    }

    public function ModificarUno($request, $response, $args)
    {

        try {

            $cuenta = new CuentaORM();
            $aux = new CuentaORM();
            $aux = json_decode(file_get_contents("php://input"));
            $cuenta = $cuenta->find($aux->id);
            $cuenta->id = $aux->id;
            $cuenta->estado = $aux->estado;
            $cuenta->id_cliente = $aux->id_cliente;
            $cuenta->id_mesa = $aux->id_mesa;
            $cuenta->id_mozo = $aux->id_mozo;
            $cuenta->save();
            $payload = json_encode(array("mensaje" => "Cuenta modificada con exito"));
            $response->getBody()->write("$payload");
            
        } catch (\Throwable $th) {
            $response->getBody()->write("Error: No se encuentra la Cuenta");
        }
        ob_end_clean();
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
