<?php

/**
 * @package     FOM
 * @subpackage  Notas cr�dito de proveedores
 * @author      Pablo Andr�s V�lez Vidal <pavelez8@misena.edu.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2012 Genesys Corporation.
 * @version     0.2
 * 
 * Notas credito que son generadas para el modulo
 * compra de mercancia.
 * 
 * Modulo : compras.
 * tablas: notas_credito_proveedor y articulos_modificados_ncp
 * integridad referencial: 
 *
 * */
class NotaCreditoProveedor {

    /**
     * C�digo interno o identificador del registro
     * @var entero
     */
    public $id;

    /**
     * C�digo de la factura a la que va relacionada esta nota
     * @var entero
     */
    public $idFactura;
    
    /**
     * Representacion del objeto factura
     * @var objeto Representacion del objeto factura (@see FacturaCompra)
     */
    public $factura;    

    /**
     * C�digo interno del modulo usuario
     * @var entero
     */
    public $idModulo;

    /**
     * Monto de la nota
     * @var objeto
     */
    public $montoNota;

    /**
     * iva de la nota
     * @var entero
     */
    public $ivaNota;
    
    /**
     * total de la nota
     * @var entero
     */
    public $totalNota;    

    /**
     * fecha en que se realia  la nota
     * @var cadena
     */
    public $fechaNota;

    /**
     * observaciones realizadas a la nota
     * @var entero
     */
    public $conceptoNota;

    /**
     * archivo digital que representa la nota
     * @var string ruta absoluta al archivo digital de la nota
     */
    public $notaDigital;
    
    /**
     * enlace al archivo digital que representa la nota
     * @var string ruta absoluta al archivo digital de la nota
     */
    public $rutaNotaDigital;    
    
    /**
     * determina si se modificaron las cantidades de los articulos en la factura de compra
     * @var boleano 
     */
    public $inventarioModificado;    

    /**
     * listado de articulos que fueron modificados
     * @var array
     */
    public $listaArticulos;

    /**
     * Orden predeterminado para organizar los listados
     * @var entero
     */
    public $ordenInicial = NULL;

    /**
     * Inicializar los datos de una factura de compra
     * @param entero $id C�digo interno o identificador de la factura
     */
    public function __construct($id = NULL) {
        global $modulo;
        
        $this->idModulo = $modulo->id;


        if (isset($id)) {
            $this->cargar($id);
        }
    }


    /**
     * Cargar los datos de una nota
     * 
     * @param entero $id C�digo interno o identificador de la nota en la base de datos
     */
    public function cargar($id) {
        global $sql, $configuracion, $textos;

        if (isset($id) && $sql->existeItem('notas_credito_proveedores', 'id', intval($id))) {

            $tablas = array(
                'ncp' => 'notas_credito_proveedores'
            );

            $columnas = array(
                'id'                        => 'ncp.id',
                'idFactura'                 => 'ncp.id_factura',
                'montoNota'                 => 'ncp.monto_nota',
                'ivaNota'                   => 'ncp.iva_nota',
                'conceptoNota'              => 'ncp.concepto_nota',
                'fechaNota'                 => 'ncp.fecha_nota',
                'notaDigital'               => 'ncp.archivo',
                'inventarioModificado'      => 'ncp.inventario_modificado',
                'totalNota'                 => 'SUM(ncp.monto_nota + ncp.iva_nota)',
            );

            $condicion = 'ncp.id = "' . $id . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                $fila = $sql->filaEnObjeto($consulta);

                foreach ($fila as $propiedad => $valor) {
                    $this->$propiedad = $valor;
                }

                if (!empty($this->notaDigital)) {
                    $this->rutaNotaDigital = $configuracion["RUTAS"]["media"] . "/" . $configuracion["RUTAS"]["archivos"] . "/nota_credito_proveedor/" . $this->notaDigital;
                    $botonEliminarArchivo = HTML::imagen($configuracion['SERVIDOR']['media'] . $configuracion['RUTAS']['imagenesEstilos'] . 'eliminarRojo.png', 'margenIzquierda cursorManito', 'eliminarNotaCreditoDigital', array('ayuda' => $textos->id('ELIMINAR_NOTA_DIGITAL'), 'idnota' => $this->id));
                    $this->notaDigital = HTML::enlace($textos->id('NOTA_CREDITO_DIGITAL_PROVEEDOR') . ' -> ' . $this->idFactura, $this->rutaNotaDigital, 'margenSuperiorDoble letraVerde negrilla', '', array('target' => '_blank')) . $botonEliminarArchivo;
                    $this->notaDigital = HTML::parrafo($this->notaDigital, '');
                    
                } else {
                    $this->notaDigital = 'No se ha subido la nota digital';
                    
                }

                if($this->inventarioModificado){
                    /**
                    * Tablas y columnas para cargar los archivos relacionados a una factura 
                    */
                    $tablas1 = array(
                        'af' => 'articulos_modificados_ncp'
                    );

                    $columnas1 = array(
                        'id'                => 'af.id',
                        'idNotaCredito'     => 'af.id_nota_credito_proveedor',
                        'idArticulo'        => 'af.id_articulo',
                        'cantidadAnterior'  => 'af.cantidad_anterior',
                        'cantidadNueva'     => 'af.cantidad_nueva',
                    );

                    $condicion1 = 'af.id_nota_credito_proveedor = "' . $id . '"';

                    $consulta1 = $sql->seleccionar($tablas1, $columnas1, $condicion1);

                    /**
                    * una vez se consulta se guarda la informaci�n en el arreglo listaArticulos, que contendra un objeto stdClass en cada una de sus posiciones
                    */
                    if ($sql->filasDevueltas) {
                        while ($objeto = $sql->filaEnObjeto($consulta1)) {
                            $objeto->articulo = new Articulo($objeto->idArticulo);
                            $this->listaArticulos[] = $objeto;
                            
                        }
                        
                    }
                    
                }
                
                $this->factura = new FacturaCompra($this->idFactura);
                
            }
            
        }
        
    }

    /**
     * Metodo encargado de agregar a la factura de este objeto una nota de credito expedida por el proveedor
     * para ser contabilizada. recibe parametro con datos como el total, el iva y el concepto. Tambi�n permite
     * cargarle la copia de la nota digital, en caso de que el proveedor envie la nota digital, o si es fisica,
     * sea escaneada.
     * 
     * @param type $datos
     * @param type $nuevas_cantidades 
     */
    public function adicionar($datos){
        global $sql, $archivo_nota_digital;
        //almaceno en nuevas variables los datos que ser�n eliminados del arreglo
        $nuevasCantidades       = $datos['nueva_cantidad'];
        $datos['archivo']       = '';
        
        if($datos['inventario_modificado']){
            $datos['inventario_modificado'] = '1';
        }
        
        //elimino los datos del arreglo para que concuerde con los datos en la BD
        unset($datos['nueva_cantidad']);
        unset($datos['dialogo']);

        //verifico si se ha cargado un soporte digital
        if(isset($archivo_nota_digital) && !empty($archivo_nota_digital['tmp_name'])){//de ser cierto, lo guardo en el servidor
            $archivo_digital = $this->cargarNotaDigital($archivo_nota_digital, 'nota_credito_proveedor');            
            $datos['archivo'] = $archivo_digital;//y agrego el campo para la insercion a la BD
            
        }      
        //inserto los datos en la tabla
        $sql->iniciarTransaccion();
        
        $insertarNota  = $sql->insertar('notas_credito_proveedores', $datos);
        $idNotaCredito = $sql->ultimoId;
        
        if(!$insertarNota){//si falla la insercion de los datos en la tabla
            if($archivo_digital) {
                $this->eliminarNotaDigital('nota_credito_proveedor', $archivo_digital);//elimino la nota del servidor
            }
            
            $sql->cancelarTransaccion();
            return false;
            
        }
        
        if($datos['inventario_modificado']){ //si se marc� la opci�n de modificar de las cantidades del inventario
            $inventario = new Inventario();
            
            foreach($nuevasCantidades as $key => $value){
                
                $arr_1 = explode('_', $key);
                //$idArticuloFactura  = $arr_1[0];//identificador del registro en la tabla articulo factura compra
                $cantidadActual     = $arr_1[0];
                $idArticulo         = $arr_1[1];
                $idBodega           = $arr_1[2];
                $idArticuloFactura  = $arr_1[3];
                $nuevaCantidad      = $value;
                
                if ($cantidadActual == $nuevaCantidad) {
                    continue;
                }

                $queryInv = FALSE;
                
                 if ($nuevaCantidad < $cantidadActual){
                     $cantidadAModificar = $cantidadActual - $nuevaCantidad;
                     $queryInv = $inventario->descontar($idArticulo, $cantidadAModificar, $idBodega);

                } else {
                    //Revisar si en una nota credito hay la posibilidad que la cantidad del articulo sea mayor a la existente
                    $cantidadAModificar = $nuevaCantidad - $cantidadActual;
                    $queryInv = $inventario->adicionar($idArticulo, $cantidadAModificar, $idBodega);    
                    
                }
                
                if($queryInv){
                    $datos_amncp = array(
                                        "id_nota_credito_proveedor"  => $idNotaCredito,
                                        "id_articulo_factura_compra" => $idArticuloFactura,
                                        "id_articulo"                => $idArticulo,
                                        "cantidad_anterior"          => $cantidadActual,
                                        "cantidad_nueva"             => $nuevaCantidad,
                                        "fecha"                      => date("Y-m-d H:i:s"),
                                        );

                    $query = $sql->insertar("articulos_modificados_ncp", $datos_amncp);

                    if(!$query){
                        $sql->cancelarTransaccion();
                        return false;
                    }

                } else {
                    $sql->cancelarTransaccion();
                    return false;

                }              
                
            }
        }
        
        $contabilidadCompras = new ContabilidadCompras();
        
        $contabilizarNCP = $contabilidadCompras->contabilizarNCP($idNotaCredito);
        
        if (!$contabilizarNCP) {
            $sql->cancelarTransaccion();
            return false;
        }
        
        $sql->finalizarTransaccion();
        return true;
        
    }

  

    /**
     *
     * Eliminar una nota
     *
     * @param entero $id    C�digo interno o identificador de una nota en la base de datos
     * @return l�gico       Indica si el procedimiento se pudo realizar correctamente o no
     *
     */
    public function eliminar() 
    {
        global $sql, $configuracion;

        if (!isset($this->id)) {
            return false;
        }
        
        $sql->iniciarTransaccion();
        
        $consulta = $sql->eliminar('notas_credito_proveedores', 'id = "' . $this->id . '"');

        if ($consulta) {            
            $consulta = $sql->eliminar('articulos_modificados_ncp', 'id_factura = "' . $this->id . '"');
            
            if ($this->facturaDigital) {
                $configuracionRuta = $configuracion['RUTAS']['media'] . '/' . $configuracion['RUTAS']['archivos'] . '/facturas_compra/' . $this->id;
                Archivo::eliminarArchivoDelServidor(array($configuracionRuta));
                
            }
            $sql->finalizarTransaccion();
            return true;
            
        } else {
            $sql->cancelarTransaccion();
            return false;
            
        }
        
    }
    
    
    /**
     * Cargar los datos de una nota
     * @param entero $id C�digo interno o identificador de la nota en la base de datos
     */
    public function listar($idFactura) {
        global $sql;
        
        if (isset($idFactura) && $sql->existeItem('facturas_compras', 'id', intval($idFactura))) {
            
            $tablas = array(
                'ncp'   => 'notas_credito_proveedores'
            );

            $columnas = array(
                'id'                        => 'ncp.id', 
                'idFactura'                 => 'ncp.id_factura',                
                'conceptoNota'              => 'ncp.concepto_nota',
                'montoNota'                 => 'ncp.monto_nota',
                'ivaNota'                   => 'ncp.iva_nota',
                'fechaNota'                 => 'ncp.fecha_nota',
            );

            $condicion = 'ncp.id_factura = "' . $idFactura . '"';

            $consulta = $sql->seleccionar($tablas, $columnas, $condicion);

            if ($sql->filasDevueltas) {
                
                $listaNotas = array();
                
                while ($objeto = $sql->filaEnObjeto($consulta)) {
                    if (strlen($objeto->conceptoNota) > 45) {
                        $objeto->conceptoNota = substr($objeto->conceptoNota, 0, 44) . '.';
                    }     
                    
                    $objeto->totalNota = $objeto->ivaNota + $objeto->montoNota;
                    $objeto->totalNota = '$ '.Recursos::formatearNumero( $objeto->totalNota, '$');
                    
                    $listaNotas[] = $objeto;
                }
                
                return $listaNotas;
 
            }
            
            
        }
    }    

    /**
     * Metodo que se encarga de guardar el archivo digital de la nota enviada por el proveedor
     *
     * @global type $sql
     * @global type $configuracion
     * @param type $archivo = archivo digital
     * @param type $tipo = tipo de la nota, cr�dito o d�bito
     * @return boolean 
     */
    public function cargarNotaDigital($archivo, $tipo) {
        global $configuracion;

        $validarFormato = Archivo::validarArchivo($archivo, $configuracion['VALIDACIONES']['notas_credito']);

        if (!$validarFormato) {
            $configuracionRuta = $configuracion["RUTAS"]["media"] . "/" . $configuracion["RUTAS"]["archivos"] . '/'.$tipo.'/';
            $recurso = Archivo::subirArchivoAlServidor($archivo, $configuracionRuta);
            
            if ($recurso) {
                return $recurso;
                
            } else {
                return false;
                
            }
            
        } else {
            return false;
            
        }
        
    }

    /**
     * Metodo que se encarga de eliminar la nota digital
     * 
     * @global objeto $sql objeto global de interacci�n con la BD
     * @global array $configuracion arreglo global donde se almacenan los parametros de configuraci�n
     * @return boolean true or false dependiendo del exito de la operaci�n
     */
    public function eliminarNotaDigital() {
        global $sql;

        $recurso = Archivo::eliminarArchivoDelServidor(array($this->rutaNotaDigital));
        
        if ($recurso) {
            $datosFactura = array('archivo' => '');
            $consulta = $sql->modificar('notas_credito_proveedores', $datosFactura, 'id = "' . $this->id . '"');
            
            if ($consulta) {
                return true;
                
            } else {//espera tres sec y vuelve y lo intenta
                sleep(3);
                $sql->modificar('notas_credito_proveedores', $datosFactura, 'id = "' . $this->id . '"');
                return true;
                
            }
            
        } else {
            return false;
            
        }
        
    }   
    
    /**
     * Verifica si un articulo fue modificado en una nota credito previa y devuelve la cantidad
     * real del articulo despues de aplicar dicha nota.
     * 
     * @global type $sql
     * @param type $idArticuloFactura = id del registro en la tabla articulos_factura_compra
     * @return int|boolean devuelve la cantidad actual del articulo o FALSE si no hay una nota credito previa
     */
    public static function verificarNotaPrevia($idArticuloFactura) {
        global $sql;

        $datos = array();

        $tabla          = "articulos_modificados_ndp";
        $columna        = array("cantidad_nueva", "fecha");
        $condicion      = "id_articulo_factura_compra = '".$idArticuloFactura."'";
        $orden          = "id DESC";

        $consulta = $sql->seleccionar($tabla, $columna , $condicion, "", $orden, 0, 1);

        if ($sql->filasDevueltas == 1) {
            $datos[] = $sql->filaEnObjeto($consulta);

        }      

        $tabla1          = "articulos_modificados_ncp";
        $columna1        = array("cantidad_nueva", "fecha");
        $condicion1      = "id_articulo_factura_compra = '".$idArticuloFactura."'";
        $orden1          = "id DESC";

        $consulta1 = $sql->seleccionar($tabla1, $columna1 , $condicion1, "", $orden1, 0, 1);    

        if ($sql->filasDevueltas == 1) {
            $datos[] = $sql->filaEnObjeto($consulta1);

        }        
        
        if (empty($datos[0]) && empty($datos[1])) {
            return false;
            
        } else if (!empty($datos[0]) && empty($datos[1])) {
            return $datos[0]->cantidad_nueva;
            
        } else if (empty($datos[0]) && !empty($datos[1])) {
            return $datos[1]->cantidad_nueva;
            
        } else {
            if (strtotime($datos[0]->fecha) > strtotime($datos[1]->fecha)) {
                return $datos[0]->cantidad_nueva;
            } else {
                return $datos[1]->cantidad_nueva;
            }
        }

    } 

}  
