<?php

error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as ResponseMW;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;



require __DIR__ . '/../vendor/autoload.php';
// boot eloquent


require_once './db/AccesoDatos.php';
require_once './middlewares/MiddleWares.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/CuentaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/AdminController.php';
require_once './controllers/ProductoCSVController.php';
require_once 'config.php';

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/app');



// Add error middleware
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Routes
$app->group('/login', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . ':LogIn');
})->add(\MiddleWares::class . ':VerificarUsuario');

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(MiddleWares::class . ':VerificarAdmin');
    $group->put('/', \UsuarioController::class . ':ModificarUno')->add(MiddleWares::class . ':VerificarAdmin');
    $group->delete('/', \UsuarioController::class . ':BorrarUno')->add(MiddleWares::class . ':VerificarAdmin');
  });

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('/', \ProductoController::class . ':TraerTodos');
  $group->get('/{id}', \ProductoController::class . ':TraerUno');
  $group->post('[/]', \ProductoController::class . ':CargarUno');
  $group->put('/', \ProductoController::class . ':ModificarUno');
    $group->delete('/', \ProductoController::class . ':BorrarUno');
  });
  
  $app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{id}', \MesaController::class . ':TraerUno');
    $group->post('[/]', \MesaController::class . ':CargarUno');
    $group->put('[/]', \MesaController::class . ':ModificarUno');
    $group->delete('/', \MesaController::class . ':BorrarUno');
  });

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->post('[/]', \PedidoController::class . ':CargarUno');
    $group->delete('[/]', \PedidoController::class . ':CancelarEntregar');
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/estacion/{estacion}', \EstadoPedidoController::class . ':TraerPedidoPorEstacion');
    $group->get('/cuenta/', \EstadoPedidoController::class . ':TraerPedidosPorCuenta');
    $group->put('[/]', \EstadoPedidoController::class . ':ModificarEstadoPedido');
  });

$app->group('/productoscsv', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoCSVController::class . ':TraerTodos');
    $group->get('/producto/', \ProductoCSVController::class . ':TraerUno');
    $group->post('[/]', \ProductoCSVController::class . ':Cargar');
    $group->put('[/]', \ProductoCSVController::class . ':Modificar');
    $group->delete('[/]', \ProductoCSVController::class . ':Eliminar');
  });
  
  $app->group('/cuentas', function (RouteCollectorProxy $group) {
    $group->post('[/]', \CuentaController::class . ':CargarUno');
    $group->put('[/]', \CuentaController::class . ':ModificarUno');
    $group->delete('[/]', \CuentaController::class . ':CerrarCuenta');
  });
  
  $app->group('/admin', function (RouteCollectorProxy $group) {
    $group->get('/empleados', \AdminController::class . ':HorarioEmpleados');
    $group->get('/empleadosplus', \AdminController::class . ':HorarioEmpleadosPlus');
    $group->get('/empleadosplusplus', \AdminController::class . ':HorarioEmpleadosPlusPlus');
    $group->get('/operaciones', \AdminController::class . ':OperacionesSector');
    $group->get('/empleadosector', \AdminController::class . ':OperacionesSectorPorEmpleado');
    $group->get('/operacionesempleados', \AdminController::class . ':OperacionesPorEmpleado');
    $group->get('/masvendido', \AdminController::class . ':ProductoMasVendido');
    $group->get('/menosvendido', \AdminController::class . ':ProductoMenosVendido');
    $group->get('/entregadostarde', \AdminController::class . ':PedidosEntregadosTarde');
    $group->get('/pedidoscancelados', \AdminController::class . ':PedidosCancelados');
    $group->get('/descargar', \AdminController::class . ':DownloadFile');
    
  });
  
  
$app->get('[/]', function (Request $request, Response $response ) {    

  $response->getBody()->write("Slim Framework 4 PHP");
    return $response;

});



//$app->add(\MiddleWares::class . ':VerificarJWT');
//$app->add(\MiddleWares::class . "::MostrarDos");

$app->run();




