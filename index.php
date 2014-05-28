<?php
/**
 *
 * @package     FOLCS
 * @author      Pablo Andr�s V�lez Vidal
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2014
 * @version     0.1
 *
 **/

/**
 * Nombre del directorio que almacena los archivos de configuraci�n
 * @var cadena
 */
require_once('configuracion/general.php');
require_once('configuracion/contabilidad.php');

/**
 * Funcion encargada de cargar las clases requeridas
 * @param type $className
 */
function myAutoloader($className) 
{
        try {
                //carga las clases basicas del framework
                if(is_readable("clases/".$className . '.php')) {
                    require_once("clases/".$className . '.php');

                }

                //carga las clases de los modulos
                if(is_readable("clases/modulos/".$className . '.php')) {
                    require_once("clases/modulos/".$className . '.php');

                }                    

        }catch (Exception $e) {
                echo "Unable to load $className. \n";
                echo $e->getMessage(), "\n";

        }

}

spl_autoload_register('myAutoloader');

/**
 * Redefinir los nombres de las variables para hacerlas globales
 */
Servidor::exportarVariables();

/**
 * Crear un objeto de conexi�n a la base de datos
 */
$sql = new SQL();

/**
 * Iniciar la gesti�n de la sesi�n
 */
Sesion::iniciar();

/**
 * Definir y registrar el idioma a utilizar durante la sesi�n
 */
if (!isset($sesion_idioma)) {
    Sesion::registrar("idioma", $configuracion["GENERAL"]["idioma"]);
}

/**
 * Definir y registrar el tema a utilizar durante la sesi�n
 */
if (!isset($sesion_tema)) {
    Sesion::registrar("tema", $configuracion["GENERAL"]["tema"]);
}

/**
 * Obtener el nombre del m�dulo a partir de la URL dada para iniciarlo
 */
if (isset($url_modulo)) { 
    $consulta = $sql->seleccionar(array("modulos"), array("nombre"), "url = '$url_modulo'");

    if ($sql->filasDevueltas) {
        $modulo = $sql->filaEnObjeto($consulta);
    }

} else {
    $modulo = NULL;
}

/**
 * Procesar las peticiones recibidas v�a AJAX
 */
if (isset($url_via) && $url_via == "ajax" && !is_null($modulo)) {
    $peticionAJAX = true;
    $modulo       = new Modulo($modulo->nombre);
    $modulo->procesar();

/**
 * Procesar las peticiones recibidas normalmente
 */
} else {
    $peticionAJAX = false;

    /**
     * Verificar si se ha solicitado un m�dulo e iniciarlo
     */
    if (!is_null($modulo)) {
        $modulo = new Modulo($modulo->nombre);

        /**
         * Redireccionar al m�dulo de gesti�n de errores cuando el m�dulo solicitado no existe
         */
        if (!isset($modulo->id)) {
            $modulo = new Modulo("ERROR");
        }

    /**
     * Redireccionar al m�dulo de inicio cuando no se ha especificado alg�n m�dulo
     */
    } else {
        Plantilla::$principal = true;
        $modulo = new Modulo("INICIO");
    }

    /**
     * Enviar al cliente el contenido generado despu�s de procesar la solicitud
     */
    Plantilla::iniciar($modulo);
    $modulo->procesar();
    Servidor::enviarHTML();
}
