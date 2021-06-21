<?php

require_once './models/ProductoCSV.php';

class ProductoCSVController {

  static function GetNextId()
    {
       if(Producto::_cargarListado()!=NULL)
       {
            $cuenta = count(Producto::_cargarListado());
            $aux = Producto::_cargarListado()[$cuenta - 1];
            return   (int)$aux["id_producto"];
       }
       return NULL;
    }


    public function Cargar($request, $response, $args)
    {   

      $parametros = $request->getParsedBody();
      $producto = new ProductoCSV();
      $producto->id = ProductoCSV::GetNextId();
      $producto->nombre = $parametros['nombre'];
      $producto->precio = $parametros['precio'];
      $producto->estacion = $parametros['estacion'];
      $producto->Cargar();
  
      $response->getBody()->write("Alta Exitosa");
        return $response
          ->withHeader('Content-Type', 'application/json');
    
    }
    
    public function Eliminar($request, $response, $args)
    {   
      $response->getBody()->write("Eliminado");
      return $response
        ->withHeader('Content-Type', 'application/json');
    
    }
    public function TraerTodos($request, $response, $args)
    {   
      $response->getBody()->write(json_encode(ProductoCSV::Load()));
      return $response
        ->withHeader('Content-Type', 'application/json');
    
    }
    public function TraerUno($request, $response, $args)
    {   
      $response->getBody()->write(json_encode(ProductoCSV::TraePorId()));
      return $response
        ->withHeader('Content-Type', 'application/json');
    
    }

}
