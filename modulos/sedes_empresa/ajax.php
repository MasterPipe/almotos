<?php

/**
 *
 * @package     FOLCS
 * @subpackage  Sedes de la Empresa
 * @author      Pablo Andres Velez Vidal <pavelez@genesyscorp.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2013 GENESYS
 * @version     0.1
 *
 * */
global $url_accion, $forma_procesar, $forma_id, $forma_datos, $forma_cantidadRegistros, $forma_cantidad, $forma_cadenaItems, $forma_pagina, $forma_orden, $forma_nombreOrden, $url_cadena, $forma_dialogo, $forma_consultaGlobal;


if (isset($url_accion)) {
    switch ($url_accion) {
        case 'add' : $datos = ($forma_procesar) ? $forma_datos : array();
            adicionarItem($datos);
            break;
        case 'see' : cosultarItem($forma_id);
            break;
        case 'edit' : $datos = ($forma_procesar) ? $forma_datos : array();
            modificarItem($forma_id, $datos);
            break;
        case 'delete' : $confirmado = ($forma_procesar) ? true : false;
            eliminarItem($forma_id, $confirmado, $forma_dialogo);
            break;

        case 'search' : buscarItem($forma_datos, $forma_cantidadRegistros);
            break;
        case 'move' : paginador($forma_pagina, $forma_orden, $forma_nombreOrden, $forma_consultaGlobal, $forma_cantidadRegistros);
            break;
        case 'listar' : listarItems($url_cadena);
            break;
        case 'eliminarVarios' : $confirmado = ($forma_procesar) ? true : false;
            eliminarVarios($confirmado, $forma_cantidad, $forma_cadenaItems);
            break;
    }
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @return null 
 */
function cosultarItem($id) {
    global $textos, $sql;

    if (!isset($id) || (isset($id) && !$sql->existeItem('sedes_empresa', 'id', $id))) {
        $respuesta = array();
        $respuesta['error'] = true;
        $respuesta['mensaje'] = $textos->id('NO_HA_SELECCIONADO_ITEM');

        Servidor::enviarJSON($respuesta);
        return NULL;
    }

    $objeto = new Ciudad($id);
    $respuesta = array();

    $codigo = HTML::parrafo($textos->id('NOMBRE'), 'negrilla margenSuperior');
    $codigo .= HTML::parrafo($objeto->nombre, '', '');
    $codigo .= HTML::parrafo($textos->id('ESTADO_DPTO'), 'negrilla margenSuperior');
    $codigo .= HTML::parrafo($objeto->Estado, '', '');
    $codigo .= HTML::parrafo($textos->id('PAIS'), 'negrilla margenSuperior');
    $codigo .= HTML::parrafo($objeto->pais, '', '');


    $respuesta['generar'] = true;
    $respuesta['codigo'] = $codigo;
    $respuesta['titulo'] = HTML::parrafo($textos->id('CONSULTAR_ITEM'), 'letraBlanca negrilla subtitulo');
    $respuesta['destino'] = '#cuadroDialogo';
    $respuesta['ancho'] = 450;
    $respuesta['alto'] = 300;



    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $datos 
 */
function adicionarItem($datos = array()) {
    global $textos, $sql;

    $objeto = new SedeEmpresa();
    $destino = '/ajax' . $objeto->urlBase . '/add';
    $respuesta = array();

    if (empty($datos)) {
        $codigo = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('datos[dialogo]', '', 'idDialogo');
        $codigo .= HTML::parrafo($textos->id('NOMBRE'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[nombre]', 30, 255, '', 'campoObligatorio');
        $codigo .= HTML::parrafo($textos->id('CIUDAD'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[id_ciudad]', 50, 255, '', 'autocompletable campoObligatorio', '', array('title' => HTML::urlInterna('CIUDADES', 0, true, 'listar')), $textos->id('AYUDA_USO_AUTOCOMPLETAR'), HTML::urlInterna('CIUDADES', 0, true, 'add'));
        $codigo .= HTML::parrafo($textos->id('DIRECCION'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[direccion]', 30, 50, '', '', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('CELULAR'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[celular]', 30, 50, '', 'campoObligatorio', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('TELEFONO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[telefono]', 30, 50, '', '', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('FAX'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[fax]', 30, 50, '', '', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('EMAIL'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[email]', 30, 50, '', '', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('FECHA_APERTURA'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[fecha_apertura]', 12, 12, '', 'fechaAntigua campoObligatorio', '', array('alt' => $textos->id('SELECCIONE_FECHA_APERTURA')));
        $codigo .= HTML::parrafo($textos->id('ACTIVO'), 'negrilla margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', true), '');
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_AGREGADO'), 'textoExitoso', 'textoExitoso');
        $codigo1 = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo'] = $codigo1;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo'] = HTML::parrafo($textos->id('ADICIONAR_ITEM'), 'letraBlanca negrilla subtitulo');
        $respuesta['ancho'] = 450;
        $respuesta['alto'] = 550;
    } else {
        $respuesta['error'] = true;

        if (empty($datos['nombre'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_NOMBRE');
        } elseif (empty($datos['id_ciudad'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CIUDAD');
        } elseif (!$sql->existeItem('lista_ciudades', 'cadena', $datos['id_ciudad'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_NO_EXISTE_CIUDAD');
        } elseif (empty($datos['direccion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DIRECCION');
        } elseif (empty($datos['celular']) && empty($datos['telefono'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TELEFONO');
        } else {

            $idItem = $objeto->adicionar($datos);
            if ($idItem) {
                /*                 * ************** Creo el nuevo item que se insertara via ajax *************** */
                $objeto = new SedeEmpresa($idItem);

                $celdas = array($objeto->nombre, $objeto->direccion, $objeto->telefono, $objeto->celular, $objeto->email);
                $claseFila = '';
                $idFila = $idItem;
                $celdas1 = HTML::crearNuevaFila($celdas, $claseFila, $idFila);


                $respuesta['error'] = false;
                $respuesta['accion'] = 'insertar';
                $respuesta['contenido'] = $celdas1;
                $respuesta['idContenedor'] = '#tr_' . $idItem;
                $respuesta['idDestino'] = '#tablaRegistros';

                if ($datos['dialogo'] == '') {
                    $respuesta['insertarNuevaFila'] = true;
                } else {
                    $respuesta['insertarNuevaFilaDialogo'] = true;
                    $respuesta['ventanaDialogo'] = $datos['dialogo'];
                }
            } else {
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $datos 
 */
function modificarItem($id, $datos = array()) {
    global $textos, $sql;

    $objeto = new SedeEmpresa($id);
    $destino = '/ajax' . $objeto->urlBase . '/edit';
    $respuesta = array();

    if (empty($datos)) {
        $codigo = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::campoOculto('datos[dialogo]', '', 'idDialogo');
        $codigo .= HTML::parrafo($textos->id('NOMBRE'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[nombre]', 30, 255, $objeto->nombre, 'campoObligatorio');
        $codigo .= HTML::parrafo($textos->id('CIUDAD'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[id_ciudad]', 50, 255, $objeto->ciudad, 'autocompletable campoObligatorio', '', array('title' => HTML::urlInterna('CIUDADES', 0, true, 'listar')), $textos->id('AYUDA_USO_AUTOCOMPLETAR'), HTML::urlInterna('CIUDADES', 0, true, 'add'));
        $codigo .= HTML::parrafo($textos->id('DIRECCION'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[direccion]', 30, 50, $objeto->direccion, '', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('CELULAR'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[celular]', 30, 50, $objeto->celular, 'campoObligatorio', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('TELEFONO'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[telefono]', 30, 50, $objeto->telefono, '', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('FAX'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[fax]', 30, 50, $objeto->fax, '', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('EMAIL'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[email]', 30, 50, $objeto->email, '', '', array('title' => $textos->id('')));
        $codigo .= HTML::parrafo($textos->id('FECHA_APERTURA'), 'negrilla margenSuperior');
        $codigo .= HTML::campoTexto('datos[fecha_apertura]', 12, 12, $objeto->fechaApertura, 'fechaAntigua campoObligatorio', '', array('alt' => $textos->id('SELECCIONE_FECHA_APERTURA')));
        $codigo .= HTML::parrafo($textos->id('ACTIVO'), 'negrilla margenSuperior');
        $codigo .= HTML::parrafo(HTML::campoChequeo('datos[activo]', true), '');
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_MODIFICADO'), 'textoExitoso', 'textoExitoso');
        $codigo1 = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo'] = $codigo1;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo'] = HTML::parrafo($textos->id('MODIFICAR_ITEM'), 'letraBlanca negrilla subtitulo');
        $respuesta['ancho'] = 450;
        $respuesta['alto'] = 550;
    } else {
        $respuesta['error'] = true;

        $existeSede = $sql->existeItem('sedes_empresa', 'nombre', $datos['nombre'], 'id != "' . $id . '"');

        if (empty($datos['nombre'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_NOMBRE');
        } elseif (empty($datos['id_ciudad'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CIUDAD');
        } elseif (!$sql->existeItem('lista_ciudades', 'cadena', $datos['id_ciudad'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_NO_EXISTE_CIUDAD');
        } elseif ($existeSede) {
            $respuesta['mensaje'] = $textos->id('ERROR_EXISTE_SEDE');
        } elseif (empty($datos['direccion'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_DIRECCION');
        } elseif (empty($datos['celular']) && empty($datos['telefono'])) {
            $respuesta['mensaje'] = $textos->id('ERROR_FALTA_TELEFONO');
        } else {
            $idItem = $objeto->modificar($datos);
            if ($idItem) {
                /*                 * ************** Creo el nuevo item que se insertara via ajax *************** */
                $objeto = new SedeEmpresa($id);
                $celdas = array($objeto->nombre, $objeto->direccion, $objeto->telefono, $objeto->celular, $objeto->email);
                $celdas1 = HTML::crearFilaAModificar($celdas);


                $respuesta['error'] = false;
                $respuesta['accion'] = 'insertar';
                $respuesta['contenido'] = $celdas1;
                $respuesta['idContenedor'] = '#tr_' . $id;
                $respuesta['idDestino'] = '#tr_' . $id;

                if ($datos['dialogo'] == '') {
                    $respuesta['modificarFilaTabla'] = true;
                } else {
                    $respuesta['modificarFilaDialogo'] = true;
                    $respuesta['ventanaDialogo'] = $datos['dialogo'];
                }
            } else {
                $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
            }
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $textos
 * @global type $sql
 * @param type $id
 * @param type $confirmado 
 */
function eliminarItem($id, $confirmado, $dialogo) {
    global $textos;

    $objeto = new SedeEmpresa($id);
    $destino = '/ajax' . $objeto->urlBase . '/delete';
    $respuesta = array();

    if (!$confirmado) {
        $nombre = HTML::frase($objeto->nombre, 'negrilla');
        $nombre1 = str_replace('%1', $nombre, $textos->id('CONFIRMAR_ELIMINACION'));
        $codigo = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('id', $id);
        $codigo .= HTML::campoOculto('dialogo', '', 'idDialogo');
        $codigo .= HTML::parrafo($nombre1);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR')), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_ELIMINADO'), 'textoExitoso', 'textoExitoso');
        $codigo1 = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo'] = $codigo1;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo'] = HTML::parrafo($textos->id('ELIMINAR_ITEM'), 'letraBlanca negrilla subtitulo');
        $respuesta['ancho'] = 350;
        $respuesta['alto'] = 150;
    } else {

        if ($objeto->eliminar()) {

            $respuesta['error'] = false;
            $respuesta['accion'] = 'insertar';
            $respuesta['idDestino'] = '#tr_' . $id;
            if ($dialogo == '') {
                $respuesta['eliminarFilaTabla'] = true;
            } else {
                $respuesta['eliminarFilaDialogo'] = true;
                $respuesta['ventanaDialogo'] = $dialogo;
            }
        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * 
 * @global type $textos
 * @global type $sql
 * @param type $datos 
 */
function buscarItem($data, $cantidadRegistros = NULL) {
    global $textos, $configuracion;

    $data = explode('[', $data);
    $datos = $data[0];

    if (empty($datos)) {
        $respuesta['error'] = true;
        $respuesta['mensaje'] = $textos->id('ERROR_FALTA_CADENA_BUSQUEDA');
    } else if (!empty($datos) && strlen($datos) < 2) {
        $respuesta['error'] = true;
        $respuesta['mensaje'] = str_replace('%1', '2', $textos->id('ERROR_TAMA�O_CADENA_BUSQUEDA'));
    } else {
        $item = '';
        $respuesta = array();
        $objeto = new SedeEmpresa();
        $registros = $configuracion['GENERAL']['registrosPorPagina'];
        if (!empty($cantidadRegistros)) {
            $registros = (int) $cantidadRegistros;
        }
        $pagina = 1;
        $registroInicial = 0;


        $palabras = explode(' ', $datos);

        $condicionales = $data[1];

        if ($condicionales == '') {
            $condicion = '(s.nombre REGEXP "(' . implode('|', $palabras) . ')")';
        } else {
            //$condicion = str_replace(']', ''', $data[1]);
            $condicionales = explode('|', $condicionales);

            $condicion = '(';
            $tam = sizeof($condicionales) - 1;
            for ($i = 0; $i < $tam; $i++) {
                $condicion .= $condicionales[$i] . ' REGEXP "(' . implode('|', $palabras) . ')" ';
                if ($i != $tam - 1) {
                    $condicion .= ' OR ';
                }
            }
            $condicion .= ')';
        }

        $arregloItems = $objeto->listar($registroInicial, $registros, array('0'), $condicion, 's.nombre');

        if ($objeto->registrosConsulta) {//si la consulta trajo registros
            $datosPaginacion = array($objeto->registrosConsulta, $registroInicial, $registros, $pagina, $objeto->registrosConsulta);
            $item .= $objeto->generarTabla($arregloItems, $datosPaginacion);
            $info = HTML::parrafo('Tu busqueda trajo ' . $objeto->registrosConsulta . ' resultados', 'textoExitosoNotificaciones');
        } else {
            $datosPaginacion = 0;
            $item .= $objeto->generarTabla($textos->id('NO_HAY_REGISTROS'), $datosPaginacion);
            $info = HTML::parrafo('Tu busqueda no trajo resultados, por favor intenta otra busqueda', 'textoErrorNotificaciones');
        }

        $respuesta['error'] = false;
        $respuesta['accion'] = 'insertar';
        $respuesta['contenido'] = $item;
        $respuesta['idContenedor'] = '#tablaRegistros';
        $respuesta['idDestino'] = '#contenedorTablaRegistros';
        $respuesta['paginarTabla'] = true;
        $respuesta['info'] = $info;
    }

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $configuracion
 * @param type $pagina
 * @param type $orden
 * @param type $nombreOrden
 * @param type $consultaGlobal 
 */
function paginador($pagina, $orden = NULL, $nombreOrden = NULL, $consultaGlobal = NULL, $cantidadRegistros = NULL) {
    global $configuracion;

    $item = '';
    $respuesta = array();
    $objeto = new Ciudad();

    $registros = $configuracion['GENERAL']['registrosPorPagina'];

    if (!empty($cantidadRegistros)) {
        $registros = (int) $cantidadRegistros;
    }

    if (isset($pagina)) {
        $pagina = $pagina;
    } else {
        $pagina = 1;
    }

    if (isset($consultaGlobal) && $consultaGlobal != '') {

        $data = explode('[', $consultaGlobal);
        $datos = $data[0];
        $palabras = explode(' ', $datos);

        if ($data[1] != '') {
            $condicionales = explode('|', $data[1]);

            $condicion = '(';
            $tam = sizeof($condicionales) - 1;
            for ($i = 0; $i < $tam; $i++) {
                $condicion .= $condicionales[$i] . ' REGEXP "(' . implode('|', $palabras) . ')" ';
                if ($i != $tam - 1) {
                    $condicion .= ' OR ';
                }
            }
            $condicion .= ')';

            $consultaGlobal = $condicion;
        } else {
            $consultaGlobal = '(s.nombre REGEXP "(' . implode('|', $palabras) . ')")';
        }
    } else {
        $consultaGlobal = '';
    }

    if (!isset($nombreOrden)) {
        $nombreOrden = $objeto->ordenInicial;
    }


    if (isset($orden) && $orden == 'ascendente') {//ordenamiento
        $objeto->listaAscendente = true;
    } else {
        $objeto->listaAscendente = false;
    }

    if (isset($nombreOrden) && $nombreOrden == 'estado') {//ordenamiento
        $nombreOrden = 'activo';
    }

    $registroInicial = ($pagina - 1) * $registros;


    $arregloItems = $objeto->listar($registroInicial, $registros, array('0'), $consultaGlobal, $nombreOrden);

    if ($objeto->registrosConsulta) {//si la consulta trajo registros
        $datosPaginacion = array($objeto->registrosConsulta, $registroInicial, $registros, $pagina);
        $item .= $objeto->generarTabla($arregloItems, $datosPaginacion);
    }

    $respuesta['error'] = false;
    $respuesta['accion'] = 'insertar';
    $respuesta['contenido'] = $item;
    $respuesta['idContenedor'] = '#tablaRegistros';
    $respuesta['idDestino'] = '#contenedorTablaRegistros';
    $respuesta['paginarTabla'] = true;

    Servidor::enviarJSON($respuesta);
}

/**
 *
 * @global type $sql
 * @param type $cadena 
 */
function listarItems($cadena) {
    global $sql;

    $respuesta = array();
    $consulta = $sql->seleccionar(array('sedes_empresa'), array('id', 'nombre'), 'nombre LIKE "%'.$cadena.'%" AND id != "0"', '', 'nombre ASC', 0, 20);

    while ($fila = $sql->filaEnObjeto($consulta)) {
        $respuesta1 = array();
        $respuesta1['label'] = $fila->nombre;
        $respuesta1['value'] = $fila->id;
        $respuesta[] = $respuesta1;
        
    }

    Servidor::enviarJSON($respuesta);
}

/**
 * Funcion Eliminar
 * @global type $textos
 * @param type $id
 * @param type $confirmado 
 */
function eliminarVarios($confirmado, $cantidad, $cadenaItems) {
    global $textos;


    $destino = '/ajax/sedes_empresa/eliminarVarios';
    $respuesta = array();

    if (!$confirmado) {
        $titulo = HTML::frase($cantidad, 'negrilla');
        $titulo1 = str_replace('%1', $titulo, $textos->id('CONFIRMAR_ELIMINACION_VARIOS'));
        $codigo = HTML::campoOculto('procesar', 'true');
        $codigo .= HTML::campoOculto('cadenaItems', $cadenaItems, 'cadenaItems');
        $codigo .= HTML::parrafo($titulo1);
        $codigo .= HTML::parrafo(HTML::boton('chequeo', $textos->id('ACEPTAR'), '', 'botonOk', 'botonOk'), 'margenSuperior');
        $codigo .= HTML::parrafo($textos->id('REGISTRO_ELIMINADO'), 'textoExitoso', 'textoExitoso');
        $codigo1 = HTML::forma($destino, $codigo);

        $respuesta['generar'] = true;
        $respuesta['codigo'] = $codigo1;
        $respuesta['destino'] = '#cuadroDialogo';
        $respuesta['titulo'] = HTML::parrafo($textos->id('ELIMINAR_VARIOS_REGISTROS'), 'letraBlanca negrilla subtitulo');
        $respuesta['ancho'] = 350;
        $respuesta['alto'] = 150;
    } else {

        $cadenaIds = substr($cadenaItems, 0, -1);
        $arregloIds = explode(',', $cadenaIds);

        $eliminarVarios = true;
        foreach ($arregloIds as $val) {
            $objeto = new SedeEmpresa($val);
            $eliminarVarios = $objeto->eliminar();
        }

        if ($eliminarVarios) {

            $respuesta['error'] = false;
            $respuesta['textoExito'] = true;
            $respuesta['mensaje'] = $textos->id('ITEMS_ELIMINADOS_CORRECTAMENTE');
            $respuesta['accion'] = 'recargar';
        } else {
            $respuesta['mensaje'] = $textos->id('ERROR_DESCONOCIDO');
        }
    }

    Servidor::enviarJSON($respuesta);
}

?>