<?php

error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;

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
require_once './controllers/EncuestaController.php';
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
$app->group('/log', function (RouteCollectorProxy $group) {
    $group->get('/login', \UsuarioController::class . ':LogIn')->add(\MiddleWares::class . ':VerificarUsuario');
    $group->get('/logout', \UsuarioController::class . ':LogOut')->add(\MiddleWares::class . ':VerificarSocio');
    $group->post('/encuesta', \EncuestaController::class . ':CargarUno');
    $group->get('/csv', \EncuestaController::class . ':DescargarCSV');
});

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(\MiddleWares::class . ':VerificarSocio');
    $group->put('/', \UsuarioController::class . ':ModificarUno')->add(\MiddleWares::class . ':VerificarSocio');
    $group->delete('/', \UsuarioController::class . ':BorrarUno')->add(\MiddleWares::class . ':VerificarSocio');
});

$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->get('/', \ProductoController::class . ':TraerTodos');
    $group->get('/{id}', \ProductoController::class . ':TraerUno');
    $group->post('[/]', \ProductoController::class . ':CargarUno')->add(\MiddleWares::class . ':VerificarSocio');
    $group->put('/', \ProductoController::class . ':ModificarUno')->add(\MiddleWares::class . ':VerificarSocio');
    $group->delete('/', \ProductoController::class . ':BorrarUno')->add(\MiddleWares::class . ':VerificarSocio');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/{id}', \MesaController::class . ':TraerUno');
    $group->get('/disponibles/', \MesaController::class . ':TraerDisponibles');
    $group->post('[/]', \MesaController::class . ':CargarUno')->add(\MiddleWares::class . ':VerificarSocio');
    $group->put('[/]', \MesaController::class . ':ModificarUno')->add(\MiddleWares::class . ':VerificarSocio');
    $group->delete('/', \MesaController::class . ':BorrarUno')->add(\MiddleWares::class . ':VerificarSocio');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
    
    $group->get('[/]', \PedidoController::class . ':TraerTodos');
    $group->get('/micuenta/', \PedidoController::class . ':TraerPedidosPorCuenta');
    $group->get('/factura/', \PedidoController::class . ':ImprimirFactura');
    $group->get('/miestacion/', \PedidoController::class . ':TraerPedidoPorEstacion')->add(\MiddleWares::class . ':VerificarEmpleado');
    $group->get('/porservir/', \PedidoController::class . ':TraerPedidoPorServir')->add(\MiddleWares::class . ':VerificarMozo');
    $group->post('[/]', \PedidoController::class . ':CargarUno')->add(\MiddleWares::class . ':VerificarMozo');
    $group->delete('/cancelar/', \PedidoController::class . ':CancelarPedido')->add(\MiddleWares::class . ':VerificarSocio');
    $group->put('/preparar/', \PedidoController::class . ':PrepararPedido')->add(\MiddleWares::class . ':VerificarEmpleado');
    $group->put('/entregar/', \PedidoController::class . ':EntregarPedido')->add(\MiddleWares::class . ':VerificarEmpleado');
    $group->delete('/servir/', \PedidoController::class . ':ServirPedido')->add(\MiddleWares::class . ':VerificarEmpleado');
});

$app->group('/productoscsv', function (RouteCollectorProxy $group) {
    $group->get('[/]', \ProductoCSVController::class . ':TraerTodos');
    $group->get('/producto/', \ProductoCSVController::class . ':TraerUno');
    $group->post('[/]', \ProductoCSVController::class . ':Cargar')->add(\MiddleWares::class . ':VerificarSocio');
    $group->put('[/]', \ProductoCSVController::class . ':Modificar')->add(\MiddleWares::class . ':VerificarSocio');
    $group->delete('[/]', \ProductoCSVController::class . ':Eliminar')->add(\MiddleWares::class . ':VerificarSocio');
});

$app->group('/cuentas', function (RouteCollectorProxy $group) {
    $group->get('[/]', \CuentaController::class . ':TraerTodos');
    $group->get('/cuenta/', \CuentaController::class . ':TraerUno')->add(\MiddleWares::class . ':VerificarSocio');
    $group->post('[/]', \CuentaController::class . ':CargarUno')->add(\MiddleWares::class . ':VerificarMesa')->add(\MiddleWares::class . ':VerificarMozo');
    $group->put('[/]', \CuentaController::class . ':ModificarUno')->add(\MiddleWares::class . ':VerificarSocio');
    $group->delete('[/]', \CuentaController::class . ':CerrarCuenta')->add(\MiddleWares::class . ':VerificarSocio');
});

$app->group('/admin', function (RouteCollectorProxy $group) {
    $group->get('/empleados', \AdminController::class .               ':HorarioEmpleados');
    $group->get('/operaciones', \AdminController::class .             ':OperacionesSector');
    $group->get('/empleadosector', \AdminController::class .          ':OperacionesSectorPorEmpleado');
    $group->get('/operacionesempleados', \AdminController::class .    ':OperacionesPorEmpleado');
    $group->get('/masvendido', \AdminController::class .              ':ProductoMasVendido');
    $group->get('/menosvendido', \AdminController::class .            ':ProductoMenosVendido');
    $group->get('/entregadostarde', \AdminController::class .         ':PedidosEntregadosTarde');
    $group->get('/pedidoscancelados', \AdminController::class .       ':PedidosCancelados');
    $group->get('/mesamasusada', \AdminController::class .            ':MesaMasUsada');
    $group->get('/mesamenosusada', \AdminController::class .          ':MesaMenosUsada');
    $group->get('/mesaconmayormonto', \AdminController::class .       ':MesaMayorFacturacion');
    $group->get('/mesaconmenormonto', \AdminController::class .       ':MesaMenorFacturacion');
    $group->get('/mesaconmayorfactura', \AdminController::class .     ':MesaMayorImporte');
    $group->get('/mesaconmenorfactura', \AdminController::class .     ':MesaMenorImporte');
    $group->get('/facturacion', \AdminController::class .             ':FacturadoEntreFechas');
    

})->add(\MiddleWares::class . ':VerificarAdmin');


$app->get('[/]', function (Request $request, Response $response) {

    $response->getBody()->write("Slim Framework 4 PHP");
    return $response;

});

$app->add(\MiddleWares::class . ':VerificarJWT');

$app->run();
