<?php

include_once './controllers/UsuarioController.php';
include_once './controllers/EstadoUsuarioController.php';
include_once 'AutentificadorJWT.php';

use Slim\Psr7\Response;

use Psr\Http\Message\ResponseInterface as ResponseMW;

use Psr\Http\Message\ServerRequestInterface as Request;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;



class MiddleWares
{
  

    public static function MostrarUno(Request $request, RequestHandler $handler) : Response
    {

        echo "MostrarUno-1   <br>";
        
        $response =  $handler->handle($request);
        
        echo "<br>";
        echo "<br>";
        
        var_dump($response);
        echo "<br>";
        echo "<br>";
        
        $contenidoAPI = (string) $response->getBody();
        
        $response = new Response();
        
        $response->getBody()->write("GETBODY 1 ENTRA");
        echo "MostrarUno-2<br>";

        $response->getBody()->write(" {$contenidoAPI} <br> ");

        return $response;
    }

    public static function MostrarDos(Request $request, RequestHandler $handler) : Response
    {

        echo "MostrarDos-1   <br>";
        
        
        $response =  $handler->handle($request);

        $response->getBody()->write("GETBODY 2 ENTRA");
        
        echo "<br>";
        echo "<br>";
        
        var_dump($response);
        echo "<br>";
        echo "<br>";

        $contenidoAPI = (string) $response->getBody();
        
        $response = new Response();
        
        echo "MostrarDos-2   <br>";

        $response->getBody()->write("<br> {$contenidoAPI} <br> ");

        return $response;
    }

    public function VerificarUsuario(Request $request, RequestHandler $handler) : Response {
        
       
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";
        $response = new Response();
        

	
			$datos = UsuarioController::LogIn($request);
            if(isset($datos['usuario']))
            {
                //Inserto un EstadoUsuario
                EstadoUsuarioController::AltaEstadoUsuario($datos);
                $token= AutentificadorJWT::CrearToken($datos);
                $response->getBody()->write("{$token}");	
                return $response;   
            }
			$response->getBody()->write("Error: {$datos['error']}");  
            return $response;   
	}

    public function VerificarJWT(Request $request, RequestHandler $handler) : Response {

       // var_dump($_SERVER);
        if($_SERVER["REQUEST_URI"] != 'app/login' && $_SERVER["REQUEST_METHOD"] != "GET")
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
            if($payload['data']->tipo == 'Admin')
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

    public function VerificarNombreUsuario(Request $request, RequestHandler $handler) : Response {
        
  
    }
}