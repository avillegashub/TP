<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model{

    use SoftDeletes;
    
    protected $primaryKey = 'id';

    public $incrementing = true;
    public $timestamps = true;

    const DELETED_AT = 'fechaBaja';

    protected $fillable = ['nombre','estado'];
/*
class Mesa
{
    public $id;
    public $cliente_id;
    public $pedidos;
    public $pedidos_id;
    public $estado;

   
    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (cliente_id, pedidos_id, estado) VALUES (:cliente_id, :pedidos_id, :estado)");
        $consulta->bindValue(':cliente_id', $this->cliente_id, PDO::PARAM_STR);
        $consulta->bindValue(':pedidos_id', $this->pedidos_id, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    
    }

    public function cargarPedido($producto_id, $cantidad)
    {
        $pedido = new Pedido();
        $pedido->producto = Producto::obtenerProductoConId($producto_id);
        $pedido->cantidad = $cantidad;
        $pedido->estado = "pendiente";
        $pedido->sector = $pedido->producto->clase;
        echo($pedido->producto->nombre);

        $pedidos = array();
        $path = "./archivos/1.json";
       



        if(file_exists($path))
        {
            $pedidos = self::CargarListaJson($path);
            
        }

        array_push($pedidos, $pedido);

        var_dump($pedidos);
        
            self::GuardarListaJson($pedidos, $path );
        

        
    }

    static function CargarListaJson($path)
    {
        if(file_exists($path))
        {
            return json_decode(file_get_contents($path), true);
        }
        return -1;
    }
    static function GuardarListaJson($lista, $path)
    {
        $miArchivo = fopen($path, "w");
        fwrite($miArchivo, json_encode($lista));
        fclose($miArchivo);
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerId()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerMesa($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, cliente_id, pedidos_id, estado FROM mesas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function modificarUsuario()
    {
        /*
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET producto = :producto, clave = :clave WHERE id = :id");
        $consulta->bindValue(':producto', $this->producto, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }
    
    public static function borrarUsuario($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaBaja = :fechaBaja WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $producto, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }
    */
}