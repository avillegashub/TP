<?php
require_once './models/Usuario.php';
require_once './models/Encuesta.php';
require_once './models/Cuenta.php';
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './models/Producto.php';
require_once './models/EstadoPedido.php';
require_once './models/EstadoUsuario.php';
require_once './models/Pedidoscancelado.php';
require_once './files/pdf/fpdf.php';

use \App\Models\Cuenta as cuentaORM;
use \App\Models\EstadoUsuario as EstadoUsuarioORM;
use \App\Models\Mesa as mesaORM;
use \App\Models\Pedido as pedidoORM;
use \App\Models\Pedidoscancelado as pedidoscanceladoORM;
use \App\Models\Producto as productoORM;
use \App\Models\Usuario as usuarioORM;
use \App\Models\Encuesta as encuestaORM;

class AdminController
{

    public function HorarioEmpleados($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $sectores = array("bar", "cerveza", "cocina", "candybar", "mozo");

        foreach ($sectores as $sector) {

            $lista = array();
            $listaUsuarios = usuarioORM::all()->where('tipo', $sector);
            foreach ($listaUsuarios as $key) {
                $entrys = EstadoUsuarioORM::withTrashed()->where('id_usuario', $key['id'])->whereBetween('created_at', [$fecha1, $fecha2])->get();
                $listaEntry = array();
                foreach ($entrys as $keyEntry) {
                    $entry = array("LogIn:" => $keyEntry['created_at'], "LogOut:" => $keyEntry['fechaBaja']);
                    array_push($listaEntry, $entry);
                }
                $empleado = array("Nombre:" => $key['nombre'], "Apellido:" => $key['apellido'], "Entradas:" => $listaEntry);
                array_push($lista, $empleado);
            }
            $response->getBody()->write(json_encode(array($sector => $lista)));

        }
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function OperacionesSector($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $sectores = array("bar", "cerveza", "cocina", "candybar");

        $listaSectores = array();
        foreach ($sectores as $sector) {
            $aux = array("Sector:" => $sector, "Cantidad Operaciones:" => PedidoORM::withTrashed()->where('estacion', $sector)->whereBetween('created_at', [$fecha1, $fecha2])->get()->count());
            array_push($listaSectores, $aux);
        }
        $response->getBody()->write(json_encode(array("Cantidad por Sector" => $listaSectores)));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');

    }

    public function OperacionesSectorPorEmpleado($request, $response, $args)
    {

        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $sectores = array("bar", "cerveza", "cocina", "candybar");

        foreach ($sectores as $sector) {

            $lista = array();
            $listaUsuarios = usuarioORM::all()->where('tipo', $sector);
            foreach ($listaUsuarios as $key) {
                $empleado = array("Nombre:" => $key['nombre'], "Apellido:" => $key['apellido'], "Cantidad Operaciones:" => PedidoORM::withTrashed()->where('id_empleado', $key['id'])->whereBetween('created_at', [$fecha1, $fecha2])->get()->count());
                array_push($lista, $empleado);
            }
            $response->getBody()->write(json_encode(array($sector => $lista)));

        }
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');

    }

    public function OperacionesPorEmpleado($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $sectores = array("bar", "cerveza", "cocina", "candybar");

        $lista = array();
        $listaUsuarios = usuarioORM::all()->whereIn('tipo', $sectores);
        foreach ($listaUsuarios as $key) {
            $empleado = array("Nombre:" => $key['nombre'], "Apellido:" => $key['apellido'], "Cantidad Operaciones:" => PedidoORM::withTrashed()->where('id_empleado', $key['id'])->whereBetween('created_at', [$fecha1, $fecha2])->get()->count());
            array_push($lista, $empleado);
        }
        $response->getBody()->write(json_encode(array("Empleados:" => $lista)));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');

    }

    public function ProductoMasVendido($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];

        $listaProductos = productoORM::all();
        $cantVendido = 0;
        $idMasVendido;
        foreach ($listaProductos as $producto) {

            $cant = pedidoORM::withTrashed()->where('id_producto', $producto['id'])->whereBetween('created_at', [$fecha1, $fecha2])->count();
            if ($cant > $cantVendido) {
                $cantVendido = $cant;
                $idMasVendido = $producto['id'];
            }
        }

        $producto = productoORM::find($idMasVendido);
        $arrayProducto = array("ID:" => $producto['id'], "Nombre:" => $producto['nombre'], "Cantidad:" => $cantVendido);
        $sB = array("Producto mas vendido:" => $arrayProducto);

        $response->getBody()->write(json_encode(array($sB)));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');

    }

    public function ProductoMenosVendido($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $listaProductos = productoORM::all();
        $cantVendido = 5000000000000000;
        $idMasVendido;
        foreach ($listaProductos as $producto) {
            $cant = pedidoORM::all()->where('id_producto', $producto['id'])->whereBetween('created_at', [$fecha1, $fecha2])->count();
            if ($cant < $cantVendido) {
                $cantVendido = $cant;
                $idMasVendido = $producto['id'];
            }
        }
        $producto = productoORM::find($idMasVendido);
        $arrayProducto = array("ID:" => $producto['id'], "Nombre:" => $producto['nombre'], "Cantidad:" => $cantVendido);
        $sB = array("Producto menos vendido:" => $arrayProducto);
        $response->getBody()->write(json_encode(array($sB)));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');

    }

    public function PedidosEntregadosTarde($request, $response, $args)
    {

        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $lista = PedidoORM::onlyTrashed()->where('tiempo_real', '<', 0)->whereBetween('created_at', [$fecha1, $fecha2])->get();
        $response->getBody()->write(json_encode(array("Entregados Tarde:" => $lista)));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');

    }

    public function PedidosCancelados($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $lista = pedidoscanceladoORM::all()->whereBetween('created_at', [$fecha1, $fecha2]);
        $response->getBody()->write(json_encode(array("Cancelados:" => $lista)));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function MesaMasUsada($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $listaMesas = mesaORM::all();
        $cantUsos = 0;
        $idMasUsado;
        foreach ($listaMesas as $mesa) {
            $cant = cuentaORM::withTrashed()->where('id_mesa', $mesa['id'])->whereBetween('created_at', [$fecha1, $fecha2])->count();
            if ($cant > $cantUsos) {
                $cantUsos = $cant;
                $idMasUsado = $mesa['id'];
            }
        }
        $mesa = mesaORM::find($idMasUsado);
        $arrayProducto = array("ID:" => $mesa['id'], "Nombre:" => $mesa['nombre'], "Cantidad:" => $cantUsos);
        $sB = array("Mesa mas usada:" => $arrayProducto);
        $response->getBody()->write(json_encode($sB));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function MesaMenosUsada($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $listaMesas = mesaORM::all();
        $cantUsos = 5000000000000000000000000;
        $idMenosUsado;
        foreach ($listaMesas as $mesa) {
            $cant = cuentaORM::withTrashed()->where('id_mesa', $mesa['id'])->whereBetween('created_at', [$fecha1, $fecha2])->count();
            if ($cant < $cantUsos) {
                $cantUsos = $cant;
                $idMenosUsado = $mesa['id'];
            }
        }
        $mesa = mesaORM::find($idMenosUsado);
        $arrayProducto = array("ID:" => $mesa['id'], "Nombre:" => $mesa['nombre'], "Cantidad:" => $cantUsos);
        $sB = array("Mesa menos usada:" => $arrayProducto);
        $response->getBody()->write(json_encode($sB));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function MesaMayorFacturacion($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $listaMesas = mesaORM::all();
        $mayorFacturacion = 0;
        $mesaConMayorFacturacion;
        foreach ($listaMesas as $mesa) {
            $total = 0;
            $cuentasPorMesa = cuentaORM::withTrashed()->where('id_mesa', $mesa['id'])->whereBetween('created_at', [$fecha1, $fecha2])->get();
            foreach ($cuentasPorMesa as $cuenta) {
                $total += $cuenta['monto'];
            }
            if ($total > $mayorFacturacion) {
                $mayorFacturacion = $total;
                $mesaConMayorFacturacion = $mesa['id'];
            }

        }
        $sB = array("Mesa con Mayor Facturacion:" => $mesaConMayorFacturacion, "Monto: " => $mayorFacturacion);
        $response->getBody()->write(json_encode($sB));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function MesaMenorFacturacion($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $listaMesas = mesaORM::all();
        $menorFacturacion = 999999999999;
        $mesaConMenorFacturacion;
        foreach ($listaMesas as $mesa) {
            $total = 0;
            $cuentasPorMesa = cuentaORM::withTrashed()->where('id_mesa', $mesa['id'])->whereBetween('created_at', [$fecha1, $fecha2])->get();
            foreach ($cuentasPorMesa as $cuenta) {
                $total += $cuenta['monto'];
            }
            if ($total < $menorFacturacion) {
                $menorFacturacion = $total;
                $mesaConMenorFacturacion = $mesa['id'];
            }

        }
        $sB = array("Mesa con Menor Facturacion:" => $mesaConMenorFacturacion, "Monto: " => $menorFacturacion);
        $response->getBody()->write(json_encode($sB));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function MesaMayorImporte($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $limit = $arr['limit'];
        $montoMayor = 0;
        $cuentas = cuentaORM::withTrashed()->whereBetween('created_at', [$fecha1, $fecha2])->orderBy('monto', 'DESC')->get();
        $nuevaLista = array();
        for ($i = 0; $i < $limit; $i++) {
            $aux['mesa'] = $cuentas[$i]->id_mesa;
            $aux['monto'] = $cuentas[$i]->monto;
            array_push($nuevaLista, $aux);
        }

        $response->getBody()->write(json_encode($nuevaLista));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function MesaMenorImporte($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $limit = $arr['limit'];
        $montoMayor = 0;
        $cuentas = cuentaORM::withTrashed()->whereBetween('created_at', [$fecha1, $fecha2])->orderBy('monto', 'ASC')->get();
        $nuevaLista = array();
        for ($i = 0; $i < $limit; $i++) {
            $aux['mesa'] = $cuentas[$i]->id_mesa;
            $aux['monto'] = $cuentas[$i]->monto;
            array_push($nuevaLista, $aux);
        }

        $response->getBody()->write(json_encode($nuevaLista));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function FacturadoEntreFechas($request, $response, $args)
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $fecha1 = $arr['fecha1'];
        $fecha2 = $arr['fecha2'];
        $monto = cuentaORM::withTrashed()->whereBetween('created_at', [$fecha1, $fecha2])->sum('monto');
        $response->getBody()->write(json_encode(array("Facturado Entre: {$fecha1}  - {$fecha2}" => $monto)));
        ob_end_clean();
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
    

}
