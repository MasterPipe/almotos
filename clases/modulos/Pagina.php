<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Paginas
 * @author      Francisco J. Lozano c. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

class Pagina {

    /**
     * C�digo interno o identificador de la p�gina en la base de datos
     * @var entero
     */
    public $id;


    /**
     * Valor num�rico que determina el orden o la posici�n de la p�gina en la base de datos
     * @var entero
     */
    public $orden;

    /**
     * URL relativa del m�dulo de p�ginas
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa de una p�gina espec�fica
     * @var cadena
     */
    public $url;

    /**
     * C�digo interno o identificador del usuario creador de la p�gina en la base de datos
     * @var entero
     */
    public $idAutor;

    /**
     * Sobrenombre o apodo del usuario creador de la p�gina
     * @var cadena
     */
    public $autor;

    /**
     * Ruta de la imagen (miniatura) que representa al usuario creador de la p�gina
     * @var cadena
     */
    public $fotoAutor;

    /**
     * T�tulo de la p�gina
     * @var cadena
     */
    public $titulo;

    /**
     * Contenido completo de la p�gina
     * @var cadena
     */
    public $contenido;

    /**
     * Fecha de creaci�n de la p�gina
     * @var fecha
     */
    public $fechaCreacion;

    /**
     * Fecha de publicaci�n de la p�gina
     * @var fecha
     */
    public $fechaPublicacion;

    /**
     * Fecha de la �ltima modificaci�n de la p�gina
     * @var fecha
     */
    public $fechaActualizacion;
    /**
     * Indicador de disponibilidad del registro
     * @var l�gico
     */
    public $activo;

    /**
     * Indicador del orden cronol�gio de la lista de p�ginas
     * @var l�gico
     */
    public $listaAscendente = TRUE;

    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * Inicializar el p�gina
     * @param entero $id C�digo interno o identificador de la p�gina en la base de datos
     */
    public function __construct($id = NULL) {     
        global $modulo;
        
        $this->urlBase = "/".$modulo->url;
        $this->url     = $modulo->url;

        if (!empty($id)) {
            $this->cargar($id);
        }
    }

    /**
     * Cargar los datos de un p�gina
     * @param entero $id C�digo interno o identificador de la p�gina en la base de datos
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem("paginas", "id", intval($id))) {

            $tablas = array(
                "g" => "paginas",
                "u" => "usuarios"
            );

            $columnas = array(
                "id"                 => "g.id",
                "orden"              => "g.orden",
                "idAutor"            => "g.id_usuario",
                "autor"              => "u.usuario",
                "titulo"             => "g.titulo",
                "contenido"          => "g.contenido",
                "fechaCreacion"      => "UNIX_TIMESTAMP(g.fecha_creacion)",
                "fechaPublicacion"   => "UNIX_TIMESTAMP(g.fecha_publicacion)",
                "fechaActualizacion" => "UNIX_TIMESTAMP(g.fecha_actualizacion)",
                "activo"             => "g.activo"
            );

            $condicion = "g.id_usuario = u.id  AND g.id = '$id'";

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);
            Recursos::escribirTxt($sql->sentenciaSql);
            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase."/".$this->id;
            }
        }
    }

    /**
     * Adicionar un p�gina
     * @param  arreglo $datos       Datos de la p�gina a adicionar
     * @return entero               C�digo interno o identificador de la p�gina en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql, $sesion_usuarioSesion;

        $paginas = $sql->seleccionar(array("paginas"), array("orden" => "MAX(orden)"));
        $orden   = 0;

        if ($sql->filasDevueltas == 1) {

            $pagina = $sql->filaEnObjeto($paginas);
            $orden  = $pagina->orden + 50000;
            $orden /= 2;

        } elseif ($sql->filasDevueltas == 0) {
            $orden = 500000;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";
            $datos["fecha_publicacion"] = date("Y-m-d H:i:s");

        } else {
            $datos["activo"] = "0";
            $datos["fecha_publicacion"] = "";
        }

        $datos = array(
            "orden"               => $orden,
            "titulo"              => $datos["titulo"],
            "contenido"           => $datos["contenido"],
            "id_usuario"          => $sesion_usuarioSesion->id,
            "fecha_creacion"      => date("Y-m-d H:i:s"),
            "fecha_publicacion"   => $datos["fecha_publicacion"],
            "fecha_actualizacion" => date("Y-m-d H:i:s"),
            "activo"              => $datos["activo"]
        );

        $consulta = $sql->insertar("paginas", $datos);

        if ($consulta) {
            return $sql->ultimoId;

        } else {
            return NULL;
        }
    }

    /**
     * Modificar un p�gina
     * @param  arreglo $datos       Datos de la p�gina a modificar
     * @return l�gico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        if (isset($datos["activo"])) {
            $datos["activo"] = "1";
            $datos["fecha_publicacion"] = date("Y-m-d H:i:s");

        } else {
            $datos["activo"] = "0";
            $datos["fecha_publicacion"] = "";
        }

        $datos = array(
            "titulo"              => $datos["titulo"],
            "contenido"           => $datos["contenido"],
            "fecha_actualizacion" => date("Y-m-d H:i:s"),
            "activo"              => $datos["activo"]
        );

        $consulta = $sql->modificar("paginas", $datos, "id = '".$this->id."'");
        return $consulta;
    }

    /**
     * Eliminar un p�gina
     * @param entero $id    C�digo interno o identificador de la p�gina en la base de datos
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function eliminar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->eliminar("paginas", "id = '".$this->id."'");
        return $consulta;
    }

    /**
     * Subir de nivel una p�gina
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function subir() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("paginas"), array("id", "orden"), "orden < '".$this->orden."'", "id", "orden DESC", 0, 1);
        
        if ($sql->filasDevueltas) {
            $pagina      = $sql->filaEnObjeto($consulta);
            $temporal    = ($this->orden + $pagina->orden)/2;
            $abajo       = $sql->modificar("paginas",array("orden" => $temporal), "id = '".$this->id."'");
           /*arriba = */ $sql->modificar("paginas",array("orden" => $this->orden), "id = '".$pagina->id."'");
            $abajo       = $sql->modificar("paginas",array("orden" => $pagina->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }

    /**
     * Bajar de nivel una p�gina
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function bajar() {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $consulta = $sql->seleccionar(array("paginas"), array("id", "orden"), "orden > '".$this->orden."'", "id", "orden ASC", 0, 1);

        if ($sql->filasDevueltas) {
            $pagina      = $sql->filaEnObjeto($consulta);
            $temporal    = ($this->orden + $pagina->orden)/2;
            $arriba      = $sql->modificar("paginas",array("orden" => $temporal), "id = '".$this->id."'");
            /*abajo = */ $sql->modificar("paginas",array("orden" => $this->orden), "id = '".$pagina->id."'");
            $arriba      = $sql->modificar("paginas",array("orden" => $pagina->orden), "id = '".$this->id."'");
        }

        return $consulta;
    }
 
    /**
     * Listar las p�ginas
     * @param entero  $cantidad    N�mero de p�ginas a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
     * @return arreglo             Lista de p�ginas
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicion = NULL) {
        global $sql;

        /*** Validar la fila inicial de la consulta ***/
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*** Validar la cantidad de registros requeridos en la consulta ***/
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*** Validar que la condici�n sea una cadena de texto ***/
        if (!is_string($condicion)) {
            $condicion = "";
        }

        /*** Validar que la excepci�n sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(",", $excepcion);
            $condicion .= "g.id NOT IN ($excepcion)";
        }

        /*** Definir el orden de presentaci�n de los datos ***/
        if ($this->listaAscendente) {
            $orden = "g.orden ASC";
        } else {
            $orden = "g.orden DESC";
        }

        $tablas = array(
            "g" => "paginas",
            "u" => "usuarios"
        );

        $columnas = array(
            "id"                 => "g.id",
            "orden"              => "g.orden",
            "idAutor"            => "g.id_usuario",
            "autor"              => "u.usuario",
            "titulo"             => "g.titulo",
            "contenido"          => "g.contenido",
            "fechaCreacion"      => "UNIX_TIMESTAMP(g.fecha_creacion)",
            "fechaPublicacion"   => "UNIX_TIMESTAMP(g.fecha_publicacion)",
            "fechaActualizacion" => "UNIX_TIMESTAMP(g.fecha_actualizacion)",
            "activo"             => "g.activo"
        );

        if (!empty($condicion)) {
            $condicion .= " AND ";
        }

        $condicion .= "g.id_usuario = u.id";

        if (is_null($this->registros)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registros = $sql->filasDevueltas;
        }

        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, "", $orden, $inicio, $cantidad);
        
        $lista = array();
        if ($sql->filasDevueltas) {           

            while ($pagina = $sql->filaEnObjeto($consulta)) {
                $pagina->url = $this->urlBase."/".$pagina->id;
                $lista[]   = $pagina;
            }
        }

        return $lista;

    }
}

?>