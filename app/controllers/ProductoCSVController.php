<?php

require_once './models/ProductoCSV.php';

class ProductoCSVController {

  static function GetNextId()
    {
       if(Producto::_cargarListado()!=NULL)
       {
            $cuenta = count(ProductoCSV::_cargarListado());
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
    
    public function Modificar($request, $response, $args)
    {   
      $aux = json_decode(file_get_contents("php://input"));
      $lista = ProductoCSV::Load();
      foreach ($lista as $key) {
        if($key->id == $aux->id)
        {
          $key->nombre = $aux->nombre;
          $key->precio = $aux->precio;
          $key->estacion = $aux->estacion;
        }
      }
      ProductoCSV::Save($lista);

      $response->getBody()->write("Modificado");
      return $response
        ->withHeader('Content-Type', 'application/json');
    
    }
    public function Eliminar($request, $response, $args)
    {   

      $aux = json_decode(file_get_contents("php://input"));
      $index = null;
      ob_end_clean();
      $lista = ProductoCSV::Load();
      for ($i=0; $i < count($lista) ; $i++) { 
        if($lista[$i]->id == $aux->id)
        {
          $index = $i;
          break;
        }
      }  
      unset($lista[$index]);
      
      ProductoCSV::Save(array_values($lista));
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
      parse_str($_SERVER['QUERY_STRING'], $arr);
      $response->getBody()->write(json_encode(ProductoCSV::TraePorId($arr['id'])));
      return $response
        ->withHeader('Content-Type', 'application/json');
    
    }

}
