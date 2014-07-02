<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Cargo *
 * @author      Pablo Andr�s V�lez Vidal <pavelez@colomboamericano.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Genesys Soft.
 * @version     0.2
 * 
 * Clase encargada de gestionar la informaci�n del listado de cargos de colaboradores existentes en el negocio para el proceso de la gesti�n de
 * recursos humanos. Este modulo es utilizado como relaci�n por el modulo de empleados, y har� parte activa del modulo de gesti�n humana.
 *
 **/

class Cargo {


    /**
     * C�digo interno o identificador del cargo en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del m�dulo de cargos
     * @var cadena
     */
    public $urlBase;
      
    /**
     * URL relativa del m�dulo de cargos
     * @var cadena
     */
    public $url;

     /**
     * C�digo interno o identificador del modulo
     * @var entero
     */
    public $idModulo;
 
     
    /**
     * C�digo interno del tipo de cargo
     * @var entero
     */
    public $nombre;
 
    /**
     * Codigo en letras de el cargo de medida
     * @var cadena
     */
    public $responsabilidades;
    
    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $activo;
    
     /**
     * Indicador del orden cronol�gio de la lista de registros
     * @var l�gico
     */
    public $listaAscendente = TRUE;

    /**
     * N�mero de registros de la lista
     * @var entero
     */
    public $registros = NULL;

    /**
     * N�mero de registros activos de la lista de foros
     * @var entero
     */
    public $registrosActivos = NULL;
    
    /**
     * N�mero de registros activos de la lista de foros
     * @var entero
     */
    public $registrosConsulta = NULL;    
    
    /**
     * Orden predeterminado para organizar los listados
     * @var entero
     */
    public $ordenInicial = NULL;       
    

    /**
     *
     * Inicializar un objeto
     *
     * @param entero $id C�digo interno o identificador de el cargo en la base de datos
     *
     */
    public function __construct($id = NULL) {
        global $sql, $modulo;
        $this->urlBase  = '/'.$modulo->url;
        $this->url      = $modulo->url;
        $this->idModulo = $modulo->id;
        //Saber el numero de registros
        $this->registros = $sql->obtenerValor('cargos', 'COUNT(id)', 'id != "0"');
        //Saber el numero de registros activos
        $this->registrosActivos = $sql->obtenerValor('cargos', 'COUNT(id)', 'activo = "1"');
        //establecer el valor del campo predeterminado para organizar los listados
        $this->ordenInicial = 'nombre';        
       

        if (isset($id)) {
            $this->cargar($id);        
        }
     }//Fin del metodo constructor




    /**
     *
     * Cargar los datos del objeto
     *
     * @param entero $id C�digo interno o identificador del objeto en la base de datos
     *
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem('cargos', 'id', intval($id))) {

            $tablas = array(
                'c'  => 'cargos'
            );

            $columnas = array(
                'id'                 => 'c.id',
                'nombre'             => 'c.nombre',
                'responsabilidades'  => 'c.responsabilidades',             
                'activo'             => 'c.activo'
            );

            $condicion = 'c.id = "'.$id.'"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }
                                
            }
        }
    }//Fin del metodo Cargar




    /**
     *
     * Adicionar un objeto
     *
     * @param  arreglo $datos       Datos del objeto a adicionar
     * @return entero               C�digo interno o identificador del objeto en la base de datos (NULL si hubo error)
     *
     */
    public function adicionar($datos) {
        global $sql;

        $datosObjeto = array();

        $datosObjeto['nombre']              = $datos['nombre'];
        $datosObjeto['responsabilidades']   = $datos['responsabilidades'];


        if (isset($datos['activo'])) {
            $datosObjeto['activo']   = '1';            

        } else {
            $datosObjeto['activo']     = '0';
        }

        $consulta = $sql->insertar('cargos', $datosObjeto);
        $idItem   =  $sql->ultimoId;
        if ($consulta) {
             
             return $idItem;

        } else {
            return NULL;
        }//fin del if($consulta)

    }//fin del metodo adicionar cargos




    /**
     *
     * Modificar un objeto
     *
     * @param  arreglo $datos       Datos del objeto a modificar
     * @return l�gico               Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
 public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }
        $datosObjeto = array();

        $datosObjeto['nombre']              = $datos['nombre'];
        $datosObjeto['responsabilidades']   = $datos['responsabilidades'];      

        if (isset($datos['activo'])) {
            $datosObjeto['activo']   = '1';            

        } else {
            $datosObjeto['activo']   = '0';
        }
        //$sql->depurar = true;
        $consulta = $sql->modificar('cargos', $datosObjeto, 'id = "'.$this->id.'"');


     if($consulta){
         return 1;  

     }else{
        return NULL;

     }//fin del if(consulta)

 }//fin del metodo Modificar




    /**
     *
     * Eliminar un objeto
     *
     * @param entero $id    C�digo interno o identificador del objeto en la base de datos
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() {
        global $sql;
       
        if (!isset($this->id)) {
            return NULL;
        }

        if(!($consulta = $sql->eliminar('cargos', 'id = "'.$this->id.'"'))){                  
            return false;
            
         }else{
           return true;

         }//fin del si funciono eliminar
  
        
    }//Fin del metodo eliminar objeto



    

    /**
     *
     * Listar los objetos
     *
     * @param entero  $cantidad    N�mero de objetos a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
     * @return arreglo             Lista de cargos
     *
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicionGlobal = NULL, $orden = NULL) {
        global $sql, $textos;

        /*** Validar la fila inicial de la consulta ***/
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*** Validar la cantidad de registros requeridos en la consulta ***/
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*** Validar que la condici�n sea una cadena de texto ***/
        if (!is_string($condicionGlobal)) {
            $condicion = '';
        }

        /*** Validar que la excepci�n sea un arreglo y contenga elementos ***/
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'c.id NOT IN ('.$excepcion.')';
        }

        /*** Definir el orden de presentaci�n de los datos ***/
        if(!isset($orden)){
            $orden = $this->ordenInicial;
        }
        if ($this->listaAscendente) {
            $orden = $orden.' ASC';

        } else {
            $orden = $orden.' DESC';
        }    

        $tablas = array(
            'c'  => 'cargos'
           
        );

        $columnas = array(
            'id'                 => 'c.id',
            'nombre'             => 'c.nombre',
            'responsabilidades'  => 'c.responsabilidades',             
            'activo'             => 'c.activo'
        );
        
        if (!empty($condicionGlobal)) {
            if($condicion != ''){
                $condicion .= ' AND ';
            }
            $condicion .= $condicionGlobal;
        } 


        if (is_null($this->registrosConsulta)) {
            $sql->seleccionar($tablas, $columnas, $condicion);
            $this->registrosConsulta = $sql->filasDevueltas;
        }
       // $sql->depurar = true;
        
        $consulta = $sql->seleccionar($tablas, $columnas, $condicion, 'c.id', $orden, $inicio, $cantidad);
        //echo $sql->sentenciaSql;

        if ($sql->filasDevueltas) {
            $lista = array();

            while ($objeto = $sql->filaEnObjeto($consulta)) {
                $objeto->url       = $this->urlBase.'/'.$objeto->id;
                $objeto->idModulo  = $this->idModulo;
                
                if ($objeto->activo) {
                    $objeto->estado = HTML::frase($textos->id('ACTIVO'), 'activo');
                } else {
                    $objeto->estado = HTML::frase($textos->id('INACTIVO'), 'inactivo');
                }                  
                   
                $lista[] = $objeto;
            }
        }

        return $lista;

    }//Fin del metodo de listar las cargos
    
    
    
    
    
    
    public function generarTabla($arregloRegistros, $datosPaginacion = NULL){
        global $textos;
        //Declaracion de las columnas que se van a mostrar en la tabla
        $datosTabla = array(           
            HTML::parrafo( $textos->id('NOMBRE')                ,  'centrado' ) => 'nombre|c.nombre',
            HTML::parrafo( $textos->id('ESTADO')                ,  'centrado' ) => 'estado'
        );        
        //ruta a donde se mandara la accion del doble click
        $rutaPaginador = '/ajax'.$this->urlBase.'/move';
        
        return Recursos::generarTablaRegistros($arregloRegistros, $datosTabla, $rutaPaginador, $datosPaginacion).HTML::crearMenuBotonDerecho('CARGOS');
        
    }

}