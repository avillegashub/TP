<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
include_once './controllers/EstadoUsuarioController.php';
use \App\Models\Usuario as usuarioORM;

class UsuarioController implements IApiUsable
{

    public function TraerTodos($request, $response, $args)
    {
        $lista = usuarioORM::all();
        foreach ($lista as $key ) {
            $key->clave ="XXXXXXXXX";
        }
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        try {
            $response->getBody()->write(json_encode(usuarioORM::findOrFail($args['id'])));
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args)
    {
        // Creamos el usuario
        $parametros = $request->getParsedBody();
        $usuario = new UsuarioORM();
        $usuario->usuario = $parametros['usuario'];
        $usuario->nombre = $parametros['nombre'];
        $usuario->apellido = $parametros['apellido'];
        $usuario->clave = md5($parametros['clave']);
        $usuario->tipo = $parametros['tipo'];
        $usuario->save();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        // Creamos el usuario
        $usuario = new UsuarioORM();
        $aux = new UsuarioORM();
        $aux = json_decode(file_get_contents("php://input"));
        $usuario = $usuario->find($aux->id);
        $usuario->usuario = $aux->usuario;
        $usuario->nombre = $aux->nombre;
        $usuario->apellido = $aux->apellido;
        $usuario->clave = md5($aux->clave);
        $usuario->tipo = $aux->tipo;
        $usuario->save();
        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
        $response->getBody()->write("$payload");
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        try {

            $usuario = new UsuarioORM();
            $un_usuario = $usuario->find(json_decode(file_get_contents("php://input"))->id);
            $response->getBody()->write(json_encode($un_usuario->delete()));
        } catch (Exception $ex) {
            $response->getBody()->write(json_encode($ex->getMessage()));
        }
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function LogIn($request)
    {
      parse_str($_SERVER['QUERY_STRING'], $arr);
      $parametros = $request->getParsedBody();
      $payload = new UsuarioORM();
      $usuario = $arr['usuario'];
      $clave = $arr['clave'];
      $payload = $payload->whereUsuario($usuario)->first();
      
      if (isset($payload->clave)) {
        if (strcmp($payload->clave, md5($clave)) == 0) {
          return $payload;
        } else {
          return array('error' => "Clave Inválida");
        }
        
      } else {
        return array('error' => "Usuario Inválido");
      }
    }

    public static function LogOut($request, $response, $args)
    {
      parse_str($_SERVER['QUERY_STRING'], $arr);
      $parametros = $request->getParsedBody();
      $payload = new UsuarioORM();
      $user = UsuarioORM::where('usuario', $arr['usuario'])->first();
      EstadoUsuarioController::BajaEstadoUsuario($user);

      //$response->getBody()->write("Usuario:  {$user->usuario}  se ha desconectado");
        
        return $response
          ->withHeader('Content-Type', 'application/json');
  
    }
    
    public static function GetUser($id)
    {
      $item = new usuarioORM();
        return $item->find($id);
    }

}
