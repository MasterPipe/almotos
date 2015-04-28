<?php

/**
 * @package     FOM
 * @subpackage  Cruce Cuentas 
 * @author      Pablo Andr�s V�lez Vidal 
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Genesys Soft.
 * @version     0.2
 * 
 * Cruce de Cuentas (compra/venta) utilizados para organizar la parte de facturacion contable
 * 
 * Clase encargada de gestionar la informaci�n del listado de tipos de compra existentes en el sistema. En este m�dulo se pueden
 * agregar, consultar, eliminar o modificar la informaci�n de los tipos de compra. Este modulo se relaciona con el modulo de TBD...
 * 
 * Aqui se crean por ejemplo los tipos de compra o venta, ejemplo: un tipo de compra ser�a "Venta a cr�dito y parte de cheque al contado", esto quiere decir 
 * que en este tipo de transacci�n una parte de la venta se har� a un cliente a cr�dito, y el resto ingresar� por un cheque.
 * 
 * tabla principal: cruce_cuentas.
 * 
 * */
class CruceCuentas {

    /**
     * C�digo interno o identificador de la unidad en la base de datos
     * @var entero
     */
    public $id;

    /**
     * URL relativa del m�dulo 
     * @var cadena
     */
    public $urlBase;

    /**
     * URL relativa del m�dulo 
     * @var cadena
     */
    public $url;

    /**
     * C�digo interno o identificador del modulo
     * @var entero
     */
    public $idModulo;

    /**
     * Nombre del tipo de compra
     * @var entero
     */
    public $nombre; 

    /**
     * Codigo operacion
     * @var entero
     */
    public $codigo;  

    /**
     * Determina si el registro esta activo
     * @var entero
     */
    public $activo;  
    
    /**
     * Arreglo con el listado de cuentas que se afectan directamente por la partida doble
     * @var l�gico
     */
    public $listaCuentas = array();    

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
     * N�mero de registros activos de la lista 
     * @var entero
     */
    public $registrosActivos = NULL;

    /**
     * N�mero de registros activos de la lista 
     * @var entero
     */
    public $registrosConsulta = NULL;

    /**
     * Orden predeterminado para organizar los listados
     * @var entero
     */
    public $ordenInicial = NULL;

    /**
     * Inicializar un tipo_compra
     * @param entero $id C�digo interno o identificador del tipo_compra en la base de datos
     */
    public function __construct($id = NULL) {
        global $sql, $modulo;
        
        $this->urlBase          = '/' . $modulo->url;
        $this->url              = $modulo->url;
        $this->idModulo         = $modulo->id;
        //Saber el numero de registros
        $this->registros        = $sql->obtenerValor('cruce_cuentas', 'COUNT(id)', 'id != "0" ');
        //Saber el numero de registros activos
        $this->registrosActivos = $sql->obtenerValor('cruce_cuentas', 'COUNT(id)', 'activo = "1" AND id != "0" ');
        //establecer el valor del campo predeterminado para organizar los listados
        $this->ordenInicial     = 'nombre';

        if (isset($id)) {
            $this->cargar($id);
        }
    }


    /**
     * Cargar los datos de un tipo_compra
     * @param entero $id C�digo interno o identificador del tipo_compra en la base de datos
     */
    public function cargar($id) {
        global $sql;

        if (isset($id) && $sql->existeItem('cruce_cuentas', 'id', intval($id))) {

            $tablas = array(
                'tc' => 'cruce_cuentas'
            );

            $columnas = array(
                'id'            => 'tc.id',
                'nombre'        => 'tc.nombre',
                'tipo'          => 'tc.tipo',
                'activo'        => 'tc.activo',
                'descripcion'   => 'tc.descripcion',
                'principal'     => 'tc.principal'
            );

            $condicion = 'tc.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                $this->url = $this->urlBase . '/' . $this->id;
                
                //verificar si el proveedor tiene cuentas bancarias, en caso de que tenga, hacer la consulta y armar la lista de objetos
                $tablas2        = array('cp' => 'cuentas_tipo_compra', 'pc' => 'plan_contable');
                $columnas2      = array('id' => 'cp.id', 'cuenta' => 'pc.nombre', 'codigoCuenta' => 'pc.codigo_contable', 'tipoCuenta' => 'cp.tipo');
                $sql->depurar   = true;
                $this->listaCuentas = $sql->seleccionar($tablas2, $columnas2, 'cp.id_cuenta = pc.id AND cp.id_tipo_compra = "' . $id . '"');                
                
            }
        }
    }


    /**
     * Adicionar un tipo_compra
     * @param  arreglo $datos       Datos del tipo_compra a adicionar
     * @return entero               C�digo interno o identificador del tipo_compra en la base de datos (NULL si hubo error)
     */
    public function adicionar($datos) {
        global $sql;

        $datosItem = array();

        $datosItem['nombre']        = $datos['nombre'];
        $datosItem['descripcion']   = $datos['descripcion'];
        $datosItem['tipo']          = $datos['tipo'];

        if (isset($datos['activo'])) {
            $datosItem['activo'] = '1';
            
        } else {
            $datosItem['activo'] = '0';
            
        }
        
        $sql->iniciarTransaccion();
        
        if (isset($datos['principal'])) {
            $datosItem['principal'] = '1';
            $datosPrincipal         = array('principal' => '0');
            
            $modificar = $sql->modificar('cruce_cuentas', $datosPrincipal, 'principal = "1"');
            
            if(!$modificar){
                $sql->cancelarTransaccion();
            }
            
        } else {
            $datosItem['principal'] = '0';
            
        }        

        $consulta = $sql->insertar('cruce_cuentas', $datosItem);

        if ($consulta) {
            $idItem = $sql->ultimoId;
            $sql->finalizarTransaccion();
            return $idItem;
            
        } else {
            $sql->cancelarTransaccion();
            return false;
            
        }//fin del if($consulta)
        
    }


    /**
     * Modificar un tipo_compra
     * @param  arreglo $datos       Datos del tipo_compra a modificar
     * @return l�gico               Indica si el procedimiento se pudo realizar correctamente o no
     */
    public function modificar($datos) {
        global $sql;

        if (!isset($this->id)) {
            return NULL;
        }

        $datosItem = array();

        $datosItem['nombre']        = $datos['nombre'];
        $datosItem['descripcion']   = $datos['descripcion'];
        $datosItem['tipo']          = $datos['tipo'];

        if (isset($datos['activo'])) {
            $datosItem['activo'] = '1';
            
        } else {
            $datosItem['activo'] = '0';
            
        }

        $sql->iniciarTransaccion();
        
        if (isset($datos['principal'])) {
            $datosItem['principal'] = '1';
            $datosPrincipal         = array('principal' => '0');
            
            $modificar = $sql->modificar('cruce_cuentas', $datosPrincipal, 'principal = "1"');
            
            if(!$modificar){
                $sql->cancelarTransaccion();
            }            
            
        } else {
            $datosItem['principal'] = '0';
            
        }        
        
        $consulta = $sql->modificar('cruce_cuentas', $datosItem, 'id = "' . $this->id . '" ');
        
        if ($consulta) {
            $sql->finalizarTransaccion();
            return true;
            
        } else {
            $sql->cancelarTransaccion();
            return false;
        }

    }


    /**
     * Eliminar un tipo_compra
     * @param entero $id    C�digo interno o identificador de un tipo_compra en la base de datos
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     */

    public function eliminar() {
        global $sql, $textos;

        //arreglo que ser� devuelto como respuesta
        $respuestaEliminar = array(
            'respuesta' => false,
            'mensaje'   => $textos->id('ERROR_DESCONOCIDO'),
        );
        
        if (!isset($this->id)) {
            return $respuestaEliminar;
        }
         
        //hago la validacion de la integridad referencial
        $arreglo1 = array('tipo_compra_cuenta_afectada',   'id_tipo_compra    = "'.$this->id.'"', $textos->id('TIPO_COMPRA_CUENTA_AFECTADA'));//arreglo del que sale la info a consultar
        $arreglo2 = array('cuentas_tipo_compra',           'id_tipo_compra    = "'.$this->id.'"', $textos->id('CUENTAS_TIPO_COMPRA'));//arreglo del que sale la info a consultar
        
        $arregloIntegridad  = array($arreglo1, $arreglo2);//arreglo de arreglos para realizar las consultas de integridad referencial, (ver documentacion de metodo)
        $integridad         = Recursos::verificarIntegridad($textos->id('TIPO_DE_COMPRA'), $arregloIntegridad);

        /**
         * si hay problemas con la integridad referencial, la variable integridad tiene como valor,
         * un texto diciendo que tabla contiene n cantidad de relaciones con esta
         */
        if ($integridad != "") {
            $respuestaEliminar['mensaje'] = $integridad;
            return $respuestaEliminar;
            
        }
              
        $sql->iniciarTransaccion();
        $consulta = $sql->eliminar('cruce_cuentas', 'id = "' . $this->id . '"');
        
        if (!($consulta)) {
            $sql->cancelarTransaccion("Fallo en el archivo " . __FILE__ . " en la linea " .  __LINE__);
            return $respuestaEliminar;
            
        } else {
            $sql->finalizarTransaccion();
            //todo sali� bien, se envia la respuesta positiva
            $respuestaEliminar['respuesta'] = true;
            return $respuestaEliminar;
        }
        
    }

    /**
     * Listar los cruce_cuentas
     * @param entero  $cantidad    N�mero de cruce_cuentas a incluir en la lista (0 = todas las entradas)
     * @param arreglo $excepcion   Arreglo con los c�digos internos o identificadores a omitir en la lista
     * @param cadena  $condicion   Condici�n adicional (SQL)
     * @return arreglo             Lista de cruce_cuentas
     */
    public function listar($inicio = 0, $cantidad = 0, $excepcion = NULL, $condicionGlobal = NULL, $orden = NULL) {
        global $textos;

        $sqlGlobal = new SqlGlobal();        
        
        /*         * * Validar la fila inicial de la consulta ** */
        if (!is_int($inicio) || $inicio < 0) {
            $inicio = 0;
        }

        /*         * * Validar la cantidad de registros requeridos en la consulta ** */
        if (!is_int($cantidad) || $cantidad <= 0) {
            $cantidad = 0;
        }

        /*         * * Validar que la condici�n sea una cadena de texto ** */
        if (!is_string($condicionGlobal)) {
            $condicion = '';
        }

        /*         * * Validar que la excepci�n sea un arreglo y contenga elementos ** */
        if (isset($excepcion) && is_array($excepcion) && count($excepcion)) {
            $excepcion = implode(',', $excepcion);
            $condicion .= 'cc.id NOT IN (' . $excepcion . ')';
        }

        /*         * * Definir el orden de presentaci�n de los datos ** */
        if (!isset($orden)) {
            $orden = $this->ordenInicial;
        }
        if ($this->listaAscendente) {
            $orden = $orden . ' ASC';
        } else {
            $orden = $orden . ' DESC';
        }


        $tablas = array(
            'cc' => 'cruce_cuentas'
        );

        $columnas = array(
            'id'        => 'cc.id',
            'nombre'    => 'cc.nombre',
            'codigo'    => 'cc.codigo',
            'activo'    => 'cc.activo',
        );

        if (!empty($condicionGlobal)) {
            if ($condicion != '') {
                $condicion .= ' AND ';
            }
            $condicion .= $condicionGlobal;
        }

        if (is_null($this->registrosConsulta)) {
            $sqlGlobal->seleccionar($tablas, $columnas, $condicion);
            $this->registrosConsulta = $sqlGlobal->filasDevueltas;
        }
        
        $consulta = $sqlGlobal->seleccionar($tablas, $columnas, $condicion, 'cc.id', $orden, $inicio, $cantidad);
        
        if ($sqlGlobal->filasDevueltas) {
            $lista = array();

            while ($objeto = $sqlGlobal->filaEnObjeto($consulta)) {                
                $varEstado = ($objeto->activo) ? 'ACTIVO' : 'INACTIVO';                        
                $objeto->estado = HTML::frase($textos->id((string)$varEstado), strtolower($varEstado));
                
                $lista[] = $objeto;
            }
        }

        return $lista;
    }


    /**
     * Metodo que arma la grilla para mostrarse desde la pagina principal
     * @global type $textos
     * @param type $arregloRegistros
     * @param type $datosPaginacion
     * @return type 
     */
    public function generarTabla($arregloRegistros, $datosPaginacion = NULL) {
        global $textos, $sesion_usuarioSesion;
        //Declaracion de las columnas que se van a mostrar en la tabla
        $datosTabla = array(
            HTML::parrafo($textos->id('NOMBRE'), 'centrado')        => 'nombre|cc.nombre',
            HTML::parrafo($textos->id('CODIGO'), 'centrado')          => 'codigo|cc.codigo',
            HTML::parrafo($textos->id('ESTADO'), 'centrado')        => 'estado',
        );
        //ruta a donde se mandara la accion del doble click
        $ruta = '/ajax' . $this->urlBase . '/move';
        
        $agregarCuentaAfectada = '';

        $puedeAgregarCuentaAfectada = Perfil::verificarPermisosBoton('botonAgregarCuentaAfectadaCompra');
        
        if ($puedeAgregarCuentaAfectada || $sesion_usuarioSesion->id == 0) {
            $agregarCuentaAfectada1    = HTML::formaAjax($textos->id('AGREGAR_CUENTAS_AFECTADAS'), 'contenedorMenuCuentas', 'agregarCuentaAfectada', '', '/ajax/cruce_cuentas/adicionarCuenta', array('id' => ''));
            $agregarCuentaAfectada     = HTML::contenedor($agregarCuentaAfectada1, '', 'botonAgregarCuentaAfectadaCompra');
        }

        $botonesExtras = array($agregarCuentaAfectada);        

        return Recursos::generarTablaRegistros($arregloRegistros, $datosTabla, $ruta, $datosPaginacion) . HTML::crearMenuBotonDerecho('CRUCE_CUENTAS', $botonesExtras);
    }

}