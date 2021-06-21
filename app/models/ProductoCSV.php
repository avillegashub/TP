<?php

class ProductoCSV 
{
   public $id;
   public $nombre;
   public $precio;
   public $estacion;

/*
    public function __construct($n, $c, $t, $s, $p)
    {
           $this->nombre = $n;
           $this->codigo_de_barra = $c;
           $this->estacion = $t;
           $this->precio = $p;
    }
 */   
    static function GetNextId()
    {
        $lista = self::Load();
       if($lista!=NULL)
       {
            $producto = $lista[count($lista) - 1];
            return   (int)$producto->id +1;
       }
       return 11111;
    }

    public function Cargar()
    {
        $lista = self::load();
        if($lista == NULL)
        {
            $lista = array();
        }
        array_push($lista, $this);
        self::Save($lista);
    }

    public function Modificar($id, $nombre, $precio, $estacion)
    {
        $lista = self::load();
        foreach ($lista as $producto) {
            if($producto->id == $id)
            {
                return $producto;
            }
        }
        return NULL;
    }
    public static function TraePorId()
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        $lista = self::load();
        foreach ($lista as $producto) {
            if($producto->id == $arr['id'])
            {
                return $producto;
            }
        }
        return NULL;
    }

    public function Eliminar($id)
    {
        $lista = self::load();
        for ($i=0; $i < count($lista)-1 ; $i++) { 
            if($lista[$i]->id == $id)
            {
                unset($lista[$i]);
                break;
            }
        }
    }

    static function Save($lista)
    {
        $file = fopen('./files/csv/producto.csv', "w");

        foreach ($lista as $value) {
            fwrite($file, $value->id . ",");
            fwrite($file, $value->nombre . ",");
            fwrite($file, $value->precio . ",");
            fwrite($file, $value->estacion. ",");
            fwrite($file, "\n");
        }
        fclose($file);
    }

    static function Load()
    {
        $path ='./files/csv/producto.csv';
        if(file_exists($path))
        {
            $miArchivo = fopen($path, "r");
            $retorno = array();

            if ($miArchivo) {
                while (($linea = fgets($miArchivo, 512)) != FALSE) {
                    $aux = explode(",", $linea);
                    $new = new ProductoCSV();
                    $new->id = $aux[0];
                    $new->nombre = $aux[1];
                    $new->precio = $aux[2];
                    $new->estacion = $aux[3];
                    array_push($retorno, $new);
                }

                fclose($miArchivo);
                return $retorno;
            }
        }
            
        return NULL;
    }
    
}

?>