<?php
require_once './models/Usuario.php';
require_once './models/Cuenta.php';
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './models/Producto.php';
require_once './models/EstadoPedido.php';
require_once './models/EstadoUsuario.php';
require_once './models/Pedidoscancelado.php';
require_once './files/pdf/fpdf.php';

use mikehaertl\wkhtmlto\Pdf;
use Dompdf\Dompdf;
use Slim\Psr7\Response;
use \App\Models\Usuario as usuarioORM;
use \App\Models\Cuenta as cuentaORM;
use \App\Models\Mesa as mesaORM;
use \App\Models\Pedido as pedidoORM;
use \App\Models\Producto as productoORM;
use \App\Models\EstadoPedido as EstadoPedidoORM;
use \App\Models\EstadoUsuario as EstadoUsuarioORM;
use \App\Models\Pedidoscancelado as pedidoscanceladoORM;


class AdminController {

  public function HorarioEmpleados($request, $response, $args)
  {
    $sectores =array ("bar","cerveza", "cocina", "candybar", "mozo");

    foreach ($sectores as $sector) {
      
              $lista = array();
              $listaUsuarios = usuarioORM::all()->where('tipo',$sector);
              foreach ($listaUsuarios as $key ) {
                $entrys = EstadoUsuarioORM::withTrashed()->where('id_usuario',$key['id'])->get();
                $listaEntry = array();
                foreach ($entrys as $keyEntry ) {
                  $entry = array("LogIn:" => $keyEntry['created_at'],"LogOut:" => $keyEntry['fechaBaja']);
                  array_push($listaEntry, $entry);
                }
                $empleado = array("Nombre:" => $key['nombre'], "Apellido:" => $key['apellido'], "Entradas:" => $listaEntry);
                array_push($lista, $empleado);
              }
              $response->getBody()->write(json_encode(array($sector => $lista)));
      
    }

      return $response
          ->withHeader('Content-Type', 'application/json');
  }

  public function HorarioEmpleadosPlus($request, $response, $args)
  {
    $sectores =array ("bar","cerveza", "cocina", "candybar", "mozo");

    foreach ($sectores as $sector) {
      
              $lista = array();
              $listaUsuarios = usuarioORM::all()->where('tipo',$sector);
              foreach ($listaUsuarios as $key ) {
                $entrys = EstadoUsuarioORM::withTrashed()->where('id_usuario',$key['id'])->whereBetween('created_at', ['2021-06-13 00:00:00', '2021-06-13 23:59:59'])->get();
                $listaEntry = array();
                foreach ($entrys as $keyEntry ) {
                  $entry = array("LogIn:" => $keyEntry['created_at'],"LogOut:" => $keyEntry['fechaBaja']);
                  array_push($listaEntry, $entry);
                }
                $empleado = array("Nombre:" => $key['nombre'], "Apellido:" => $key['apellido'], "Entradas:" => $listaEntry);
                array_push($lista, $empleado);
              }
              $response->getBody()->write(json_encode(array($sector => $lista)));
      
    }

      return $response
          ->withHeader('Content-Type', 'application/json');
  }

  public function HorarioEmpleadosPlusPlus($request, $response, $args)
  {
    $sectores =array ("bar","cerveza", "cocina", "candybar", "mozo");

    foreach ($sectores as $sector) {
      
              $lista = array();
              $listaUsuarios = usuarioORM::all()->where('tipo',$sector);
              foreach ($listaUsuarios as $key ) {
                $entrys = EstadoUsuarioORM::withTrashed()->where('id_usuario',$key['id'])->whereBetween('created_at', ['2021-06-13 00:00:00', '2021-06-13 23:59:59'])->get();
                $listaEntry = array();
                foreach ($entrys as $keyEntry ) {
                  $entry = array("LogIn:" => $keyEntry['created_at'],"LogOut:" => $keyEntry['fechaBaja']);
                  array_push($listaEntry, $entry);
                }
                $empleado = array("Nombre:" => $key['nombre'], "Apellido:" => $key['apellido'], "Entradas:" => $listaEntry);
                array_push($lista, $empleado);
              }
              //$response->getBody()->write(json_encode(array($sector => $lista)));
      
    }

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',12);
    foreach ($lista as $key ) {
      $pdf->MultiCell(200,5,json_encode($key['Nombre:']));
      $pdf->MultiCell(200,5,json_encode($key['Apellido:']));
      $pdf->MultiCell(200,5,json_encode($key['Entradas:']));
      $pdf->Ln();
      
    }
    
    $respuesta = $pdf->Output('F');// lo manda como String
    $response = new Response();
        $response = $response
            ->withHeader('Content-Type', 'application/pdf-file')
            ->withHeader('Content-Disposition', 'attachment; filename=renuncia.pdf')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache');
            ob_end_clean();
            $response->getBody()->write($respuesta);
            return $response;
  }

  public function OperacionesSector($request, $response, $args)  
  {  
    
        $sectores =array ("bar","cerveza","cocina", "candybar");
        $listaSectores = array();
        foreach ($sectores as $sector) {
          $aux = array ("Sector:" => $sector, "Cantidad Operaciones:" => EstadoPedidoORM::withTrashed()->where('estacion',$sector)->get()->count() );
          array_push($listaSectores, $aux);
        }
        $response->getBody()->write(json_encode(array("Cantidad por Sector" => $listaSectores)));
        return $response
        ->withHeader('Content-Type', 'application/json');
    
  }
  
  public function OperacionesSectorPorEmpleado($request, $response, $args)  
  {
    
    $sectores =array ("bar","cerveza", "cocina", "candybar");

    foreach ($sectores as $sector) {
      
              $lista = array();
              $listaUsuarios = usuarioORM::all()->where('tipo',$sector);
              foreach ($listaUsuarios as $key ) {
                $empleado = array("Nombre:" => $key['nombre'], "Apellido:" => $key['apellido'], "Cantidad Operaciones:" => EstadoPedidoORM::withTrashed()->where('id_empleado',$key['id'])->get()->count());
                array_push($lista, $empleado);
              }
              $response->getBody()->write(json_encode(array($sector => $lista)));
      
    }

      return $response
          ->withHeader('Content-Type', 'application/json');
          
          
  }

  public function OperacionesPorEmpleado($request, $response, $args)  
  { 
          $lista = array();
          $listaUsuarios = usuarioORM::all();
          foreach ($listaUsuarios as $key ) {
            $empleado = array("Nombre:" => $key['nombre'], "Apellido:" => $key['apellido'], "Cantidad Operaciones:" => EstadoPedidoORM::withTrashed()->where('id_empleado',$key['id'])->get()->count());
            array_push($lista, $empleado);
          }
          $response->getBody()->write(json_encode(array("Empleados:" => $lista)));
          return $response
              ->withHeader('Content-Type', 'application/json');

  }

  public function ProductoMasVendido($request, $response, $args)  
  {  
    $listaProductos = productoORM::all();
    $cantVendido = 0;
    $idMasVendido;
    foreach ($listaProductos as $producto ) {

      $cant = pedidoORM::all()->where('id_producto', $producto['id'])->count();
      if($cant > $cantVendido)
      {
        $cantVendido = $cant;
        $idMasVendido = $producto['id'];
      }
    }

    $producto = productoORM::find($idMasVendido);
    $arrayProducto = array ("ID:" => $producto['id'], "Nombre:" => $producto['nombre'], "Cantidad:" => $cantVendido);
    $sB = array("Producto mas vendido:" => $arrayProducto);

    $response->getBody()->write(json_encode(array($sB)));
          return $response
              ->withHeader('Content-Type', 'application/json');


  }

  public function ProductoMenosVendido($request, $response, $args)  
  {  
      $listaProductos = productoORM::all();
      $cantVendido = 5000000000000000;
      $idMasVendido;
      foreach ($listaProductos as $producto ) {

        $cant = pedidoORM::all()->where('id_producto', $producto['id'])->count();
        if($cant < $cantVendido)
        {
          $cantVendido = $cant;
          $idMasVendido = $producto['id'];
        }
      }

      $producto = productoORM::find($idMasVendido);
      $arrayProducto = array ("ID:" => $producto['id'], "Nombre:" => $producto['nombre'], "Cantidad:" => $cantVendido);
      $sB = array("Producto menos vendido:" => $arrayProducto);

      $response->getBody()->write(json_encode(array($sB)));
            return $response
                ->withHeader('Content-Type', 'application/json');
                
                
  }
              
  public function PedidosEntregadosTarde($request, $response, $args)  
  {  
                $lista = EstadoPedidoORM::onlyTrashed()->where('tiempo_real', '<', 0)->get();
                $response->getBody()->write(json_encode(array("Entregados Tarde:" => $lista)));
                      return $response
                          ->withHeader('Content-Type', 'application/json');
                          
  }

  public function PedidosCancelados($request, $response, $args)  
  { 
                          
                          $lista = pedidoscanceladoORM::all();
                          $response->getBody()->write(json_encode(array("Cancelados:" => $lista)));
                                return $response
                                    ->withHeader('Content-Type', 'application/json');
  }

  public function DownloadFile2($request, $response, $args)  
  { 
    $response = new Response();
    $path = './files/csv/producto.csv';
    header("Content-Type: application/csv");
    header("Content-Transfer-Encoding: Binary");
    header("Content-Length:".filesize($path));
    header("Content-Disposition: attachment; filename=yourfile.csv");
    //readfile($path);
    
    //$response->getBody()->write();
    return $response;
  }

  public function DownloadFile3($request, $response, $args)  
  { 
   
        $csv_file = './files/csv/producto.csv';
        $response = new Response();
        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=producto.csv')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen($csv_file, 'rb'))));
            ob_end_clean();
        return $response;


  }
  
  public function DownloadFile4($request, $response, $args)  
  { 
   
    
        $csv_file = './files/pdf/renuncia.pdf';
        $response = new Response();
        $response = $response
            ->withHeader('Content-Type', 'application/pdf-file')
            ->withHeader('Content-Disposition', 'attachment; filename=renuncia.pdf')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen($csv_file, 'rb'))));
            ob_end_clean();
        return $response;


  } 
  public function DownloadFile5($request, $response, $args)  
  { 
    $pdf = new Pdf;
$pdf->addPage('https://github.com/mikehaertl/phpwkhtmltopdf');
$pdf->saveAs('./files/pdf/ASDTEST.pdf');
$pdf->send('./files/pdf/ASDTEST.pdf');
if (!$pdf->saveAs('./files/pdf/ASDTEST.pdf')) {
  $error = $pdf->getError();
  echo($error);
  // ... handle error here
}
return $response;
  }


  public function MesaMasUsada($request, $response, $args)  {  }
  public function MesaMenosUsada($request, $response, $args)  {  }
  public function MesaMayorFacturacion($request, $response, $args)  {  }
  public function MesaMenorFacturacion($request, $response, $args)  {  }
  public function MesaMayorImporte($request, $response, $args)  {  }
  public function MesaMenorImporte($request, $response, $args)  {  }
  public function FacturadoEntreFechas($request, $response, $args)  {  }
  public function MejoresComentarios($request, $response, $args)  {  }
  public function PeoresComentarios($request, $response, $args)  {  }





}
