<?php

include_once './controllers/UsuarioController.php';
include_once './models/EstadoUsuario.php';
include_once './models/Mesa.php';
include_once './controllers/EstadoUsuarioController.php';
include_once 'AutentificadorJWT.php';

use Slim\Psr7\Response;

use Psr\Http\Message\ResponseInterface as ResponseMW;

use Psr\Http\Message\ServerRequestInterface as Request;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use \App\Models\EstadoUsuario as estadoUsuarioORM;

use \App\Models\Mesa as mesaORM;



class MiddleWares
{
  


    public function VerificarUsuario(Request $request, RequestHandler $handler) : Response {

       
            $response = new Response();
			$datos = UsuarioController::LogIn($request);
            $estado = new estadoUsuarioORM();
            $estado = estadoUsuarioORM::where('id_usuario', $datos['id'])->first() ;
            if(isset($datos['usuario']))
            {
                if(!(isset($estado['estado']) && $estado->estado == 'LoggedIn'))
                {
                    EstadoUsuarioController::AltaEstadoUsuario($datos);
                }
               $token= AutentificadorJWT::CrearToken($datos);
                $response->getBody()->write("{$token}");	
                return $response;   
            }
			$response->getBody()->write("Error: {$datos['error']}");  
            return $response;   
	}

    public function VerificarJWT(Request $request, RequestHandler $handler) : Response {

        if($_SERVER["REQUEST_URI"] != 'app/log' && $_SERVER["REQUEST_METHOD"] != "GET")
        {
            $response = new Response();
            $jwt = $request->getHeaderLine('Authorization');
            try{
                $token = trim(explode("Bearer", $jwt)[1]);
                AutentificadorJWT::VerificarToken($token);
                $payload = json_encode(array('data'=>AutentificadorJWT::ObtenerData($token)));
            }
            catch(Exception $e){
                $response->getBody()->write(json_encode(array('error'=>$e->getMessage()))); 
                return $response;
            }
            
        }
        $response =  $handler->handle($request);
        return $response;   
	}
    
    public function VerificarAdmin(Request $request, RequestHandler $handler) : Response {
        
        $response = new Response();
        $jwt = $request->getHeaderLine('Authorization');
        
        try{
            $token = trim(explode("Bearer", $jwt)[1]);
            AutentificadorJWT::VerificarToken($token);
            $payload = array('data'=>AutentificadorJWT::ObtenerData($token));
            if($payload['data']->tipo == 'admin')
            {
                $response =  $handler->handle($request);
            }else
            {
                throw new Exception("No es Admin");
            }
        }
        catch(Exception $e){
            $response->getBody()->write(json_encode(array('error'=>$e->getMessage()))); 
            return $response;
        }
        return $response;   
	}

    public function VerificarEmpleado(Request $request, RequestHandler $handler) : Response {
        
        $response = new Response();
        $jwt = $request->getHeaderLine('Authorization');
        
        try{
            $token = trim(explode("Bearer", $jwt)[1]);
            AutentificadorJWT::VerificarToken($token);
            $payload = array('data'=>AutentificadorJWT::ObtenerData($token));
            if($payload['data']->tipo != 'cliente')
            {
                $response =  $handler->handle($request);
            }else
            {
                throw new Exception("Acceso Restringido");
            }
        }
        catch(Exception $e){
            $response->getBody()->write(json_encode(array('error'=>$e->getMessage()))); 
            return $response;
        }
        return $response;   
	}
    
    public function VerificarSocio(Request $request, RequestHandler $handler) : Response {
        
        $response = new Response();
        $jwt = $request->getHeaderLine('Authorization');
        
        try{
            $token = trim(explode("Bearer", $jwt)[1]);
            AutentificadorJWT::VerificarToken($token);
            $payload = array('data'=>AutentificadorJWT::ObtenerData($token));
            if($payload['data']->tipo == 'socio')
            {
                $response =  $handler->handle($request);
            }else
            {
                throw new Exception("Acceso Restringido");
            }
        }
        catch(Exception $e){
            $response->getBody()->write(json_encode(array('error'=>$e->getMessage()))); 
            return $response;
        }
        return $response;   
	}
    public function VerificarMozo(Request $request, RequestHandler $handler) : Response {
        
        $response = new Response();
        $jwt = $request->getHeaderLine('Authorization');
        
        try{
            $token = trim(explode("Bearer", $jwt)[1]);
            AutentificadorJWT::VerificarToken($token);
            $payload = array('data'=>AutentificadorJWT::ObtenerData($token));
            if($payload['data']->tipo == 'mozo')
            {
                $response =  $handler->handle($request);
            }else
            {
                throw new Exception("No Es mozo");
            }
        }
        catch(Exception $e){
            $response->getBody()->write($e->getMessage()); 
            return $response;
        }
        return $response;   
	}

    public function VerificarMesa(Request $request, RequestHandler $handler) : Response {
        
        $response = new Response();
        try{
            $mesa = mesaORM::findOrFail($request->getParsedBody()['id_mesa']);
            if($mesa->estado == 1)
            {
                $response =  $handler->handle($request);
            }
            else{
                throw new Exception();
            }
        }
        catch(Exception){
            $response->getBody()->write(json_encode(array('error'=>'Mesa No Disponible'))); 
            return $response;
        }
        return $response;   
	}

    
}