<?php

require_once './controllers/UsuarioController.php';

class MWLogIn{


public function VerifyLogIn($request, $response, $next){

    if($request->isGet())
    {
    // $response->getBody()->write('<p>NO necesita credenciales para los get </p>');
     $response = $next($request, $response);
    }
    else{
      // $nuevaRespuesta = UsuarioController::LogIn($request, $response);
    }

}












}








?>