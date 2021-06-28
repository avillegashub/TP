<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';
use \App\Models\Producto as productoORM;

class ProductoController implements IApiUsable{

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
          $response->getBody()->write(json_encode(productoORM::findOrFail($args['id'])));
        } catch (Exception $ex) {
          $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
          ->withHeader('Content-Type', 'application/json');
        }

  public static  function TraerPorId($id)
    {
        $producto = new productoORM();
        return $producto->find($id)->estacion;
    }

  public static  function GetProducto($id)
    {
        $producto = new productoORM();
        return $producto->find($id);
    }
        

    public function CargarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $producto = new ProductoORM();
      $producto->nombre = $parametros['nombre'];
      $producto->precio = $parametros['precio'];
      $producto->estacion = $parametros['estacion'];
      $producto->save();

      $payload = json_encode(array("mensaje" => "Producto creado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

        public function ModificarUno($request, $response, $args)
        {
                // Creamos el usuario
            $producto = new ProductoORM();
            $aux = new ProductoORM();
            $aux = json_decode(file_get_contents("php://input"));
            $producto =  $producto->find($aux->id);
            $producto->nombre = $aux->nombre;
            $producto->precio = $aux->precio;
            $producto->estacion = $aux->estacion;
            $producto->save();
            $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
            $response->getBody()->write("$payload");
            return $response
              ->withHeader('Content-Type', 'application/json');
        }

        public function BorrarUno($request, $response, $args)
        {
          try {

            $producto = new ProductoORM();
            $un_producto =  $producto->find(json_decode(file_get_contents("php://input"))->id);
            $response->getBody()->write(json_encode($un_producto->delete()));
          } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
          }
          return $response
            ->withHeader('Content-Type', 'application/json');
        }
}

/*
class ProductoController extends Producto implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    
    $nombre = $parametros['nombre'];
    $precio = $parametros['precio'];
    $clase = $parametros['clase'];
    
    // Creamos el producto
    $usr = new Producto();
    $usr->nombre = $nombre;
    $usr->precio = $precio;
    $usr->clase = $clase;
    $usr->crearProducto();
    
    $payload = json_encode(array("mensaje" => "Producto creado con exito"));
    
    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
  
  
  
  public function TraerUno($request, $response, $args)
  {
    // Buscamos nombre por nombre
    $usr = $args['nombre'];
    $nombre = Producto::obtenerProducto($usr);
    $payload = json_encode($nombre);
    
    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
  
  /*
  public function TraerTodos($request, $response, $args)
  {
    $lista = productoORM::all();
    $payload = json_encode(array("listaProducto" => $lista));
    
    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
  
  public function TraerTodos($request, $response, $args)
  {
    $lista = Producto::obtenerTodos();
    $payload = json_encode(array("listaProducto" => $lista));
    
    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
  
  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    
    $nombre = $parametros['nombre'];
    Producto::modificarUsuario($nombre);
    
    $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
    
    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
  
  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    
    $usuarioId = $parametros['usuarioId'];
    Producto::borrarUsuario($usuarioId);
    
    $payload = json_encode(array("mensaje" => "Producto borrado con exito"));
    
    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
  }
}

*/
?>