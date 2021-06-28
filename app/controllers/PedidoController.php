<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';
require_once './controllers/EstadoPedidoController.php';
require_once './controllers/PedidoCanceladoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/CuentaController.php';
require_once './controllers/UsuarioController.php';
require_once './files/pdf/fpdf.php';

use \App\Models\Pedido as PedidoORM;

class PedidoController 
{
    public function CargarUno($request, $response, $args)
    {
        // Creamos el Pedido
        $parametros = $request->getParsedBody();
        $pedido = new PedidoORM();
        $pedido->id_cuenta = $parametros['id_cuenta'];
        $pedido->id_producto = $parametros['id_producto'];
        $pedido->estacion = ProductoController::GetProducto($pedido->id_producto)->estacion;
        $pedido->save();
        CuentaController::CambiarEstado($pedido->id_cuenta, "Esperando pedido");
        $payload = json_encode(array("mensaje" => "Alta Exitosa"));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

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

    public function CancelarPedido($request, $response, $args)
    {
        $aux = json_decode(file_get_contents("php://input"));
        PedidoCanceladoController::GuardarCancelado();
        try {
            $pedido = PedidoORM::find($aux->id);
            $pedido->forceDelete();
            $payload = json_encode(array("mensaje" => "Pedido {$aux->id} Cancelado"));

        } catch (Throwable $th) {
            
            throw new Exception("No se encuentra pedido");
        }
        $response->getBody()->write("$payload");
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidosPorCuenta($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $listaPedidos = json_encode(PedidoORM::withTrashed()->where('id_cuenta', $arr['id_cuenta'])->get());
        $response->getBody()->write($listaPedidos);

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidoPorEstacion($request, $response, $args)
    {

        $jwt = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $jwt)[1]);
        $payload = array('data' => AutentificadorJWT::ObtenerData($token));

        $listaPedidos = json_encode(PedidoORM::all()->where('estacion', $payload['data']->tipo)->where('estado', 'pendiente'));
        $response->getBody()->write($listaPedidos);

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function PrepararPedido($request, $response, $args)
    {
        $jwt = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $jwt)[1]);
        $payload = array('data' => AutentificadorJWT::ObtenerData($token));
        $aux = json_decode(file_get_contents("php://input"));
        $pedido = new PedidoORM();
        $pedido = PedidoORM::find($aux->id);
        $pedido->id_empleado = $payload['data']->id;
        $pedido->estado = "En Preparacion";
        $pedido->tiempo_estimado = $aux->tiempo_estimado;
        $pedido->comienzo_preparacion = (new DateTime())->getTimestamp();
        $pedido->save();
        ob_end_clean();
        $response->getBody()->write("Orden {$aux->id} en preparacion");
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function EntregarPedido($request, $response, $args)
    {
        $aux = json_decode(file_get_contents("php://input"));
        $pedido = new PedidoORM();
        $pedido = PedidoORM::find($aux->id);
        $pedido->estado = "Listo Para Servir";
        $pedido->finalizada_preparacion = (new DateTime())->getTimestamp();
        $pedido->tiempo_real = ($pedido->comienzo_preparacion + $pedido->tiempo_estimado) - (new DateTime())->getTimestamp();
        $pedido->save();
        ob_end_clean();
        $response->getBody()->write("Orden {$aux->id} Lista para servir");

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidoPorServir($request, $response, $args)
    {
        $jwt = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $jwt)[1]);
        $payload = array('data' => AutentificadorJWT::ObtenerData($token));
        $cuentas = CuentaController::TraerPorIdMozo($payload['data']->id);
        $lista = PedidoORM::all()->whereIn('id_cuenta', $cuentas)->where('estado', 'Listo Para Servir');
        $response->getBody()->write(json_encode($lista));
        return $response
            ->withHeader('Content-Type', 'application/json');

    }

    public function ServirPedido($request, $response, $args)
    {
        $aux = json_decode(file_get_contents("php://input"));
        $pedido = new PedidoORM();
        $pedido = PedidoORM::find($aux->id);
        $pedido->estado = "Servido";
        $pedido->save();
        $pedido->delete();
        CuentaController::CambiarEstado($pedido->id_cuenta, "Comiendo");
        ob_end_clean();
        $response->getBody()->write("Orden {$aux->id} servida");

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ImprimirFactura($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        CuentaController::CambiarEstado($arr['id_cuenta'], "Pagando");
        $listaCuenta = json_encode(PedidoORM::withTrashed()->where('id_cuenta', $arr['id_cuenta'])->get());
        $listaCuenta = PedidoORM::withTrashed()->where('id_cuenta', $arr['id_cuenta'])->get();
        $listaProductos = self::FormatList($listaCuenta);
        $total = self::GetTotal($listaProductos);
        $cuenta = CuentaController::GetCuenta($arr['id_cuenta']);
        $mozo = UsuarioController::GetUser($cuenta->id_mozo);

        $jwt = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $jwt)[1]);
        $payload = array('data' => AutentificadorJWT::ObtenerData($token));
        $payload['data']->usuario;
        $payload['data']->nombre;
        $payload['data']->apellido;

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->MultiCell(200, 5, "Factura!!!");
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(200, 5, "Info Restaurant");
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->MultiCell(200, 5, "Datos Cliente");
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(200, 5, "Nombre:" . $payload['data']->nombre);
        $pdf->MultiCell(200, 5, "Apellido:" . $payload['data']->apellido);
        $pdf->MultiCell(200, 5, "Usuario:" . $payload['data']->usuario);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->MultiCell(200, 5, "Servidos por:");
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(200, 5, $mozo->nombre . "," . $mozo->apellido);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->MultiCell(200, 5, "Consumos:");
        $pdf->SetFont('Arial', '', 10);
        foreach ($listaProductos as $key) {
            $pdf->MultiCell(200, 5, $key['nombre'] . str_pad($key['precio'], 25, " ", STR_PAD_LEFT));
        }
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->MultiCell(200, 5, str_pad("TOTAL", 60, " ", STR_PAD_LEFT));
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(200, 0, str_pad($total, 84, " ", STR_PAD_LEFT));
        $pdf->Output();
    }

    public static function GetTotal($lista)
    {

        $aux = 0;

        foreach ($lista as $key) {
            $aux += $key['precio'];
        }
        return $aux;
    }
    public static function FormatList($lista)
    {
        $nuevaLista = array();

        foreach ($lista as $key) {

            $producto = ProductoController::GetProducto($key->id_producto);
            $aux['nombre'] = str_pad($producto->nombre, 50, " -", STR_PAD_RIGHT);
            $aux['precio'] = $producto->precio;
            array_push($nuevaLista, $aux);
        }

        return $nuevaLista;
    }

    public static function GetSum($id)
    {

        $listaPedidosEnCuenta = PedidoORM::withTrashed()->where('id_cuenta', $id)->get();
        $monto = 0;
        foreach ($listaPedidosEnCuenta as $key) {

            $producto = ProductoController::GetProducto($key->id_producto);
            $monto += $producto->precio;
            
        }

        return $monto;
    }

}
