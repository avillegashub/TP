<?php

use Slim\Psr7\Response;

use Psr\Http\Message\ResponseInterface as ResponseMW;

use Psr\Http\Message\ServerRequestInterface as Request;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
/*
class MiClase
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

        //$contenidoAPI = (string) $response->getBody();
        
        $response = new Response();
        
        echo "MostrarDos-2   <br>";

        $response->getBody()->write("<br> NADA <br> ");

        return $response;
    }
}*/