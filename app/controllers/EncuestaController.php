<?php
require_once './models/Encuesta.php';
use Slim\Psr7\Response;
use \App\Models\Encuesta as encuestaORM;

class EncuestaController 
{


    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $encuesta = new encuestaORM();
        $encuesta->id = $parametros['id'];
        $encuesta->mesa = $parametros['mesa'];
        $encuesta->restaurante = $parametros['restaurante'];
        $encuesta->mozo = $parametros['mozo'];
        $encuesta->cocina = $parametros['cocina'];
        $encuesta->comentarios = $parametros['comentarios'];
        $encuesta->save();

        $payload = json_encode(array("mensaje" => "Adios"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = encuestaORM::all();
        $payload = json_encode(array("listaEncuesta" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function DescargarCSV($request, $response, $args)
    {
        $lista = encuestaORM::all();
        self::formatCSV($lista);
        $csv_file = './files/csv/encuestas.csv';
        $response = new Response();
        $response = $response
        ->withHeader('Content-Type', 'application/octet-stream')
        ->withHeader('Content-Disposition', 'attachment; filename=producto.csv')
        ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
        ->withHeader('Pragma', 'no-cache')
        ->withBody((new \Slim\Psr7\Stream(fopen($csv_file, 'rb'))));
        ob_end_clean();
            return $response;
    }

    public static function formatCSV($lista)
    {
            $file = fopen('./files/csv/encuestas.csv', "w");
            fwrite($file,"numero de cuenta,mesa,restaurante,mozo,cocina,comentarios,");
            fwrite($file, "\n");
    
            foreach ($lista as $value) {
                fwrite($file, $value->id . ",");
                fwrite($file, $value->mesa . ",");
                fwrite($file, $value->restaurante . ",");
                fwrite($file, $value->mozo. ",");
                fwrite($file, $value->cocina. ",");
                fwrite($file, $value->comentarios. ",");
                fwrite($file, "\n");
            }
            fclose($file);
    }



}
