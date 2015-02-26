<?php

/**
 * Texto.php Clase del n�cleo del framework.
 *
 * @package     FOLCS
 * @subpackage  Base
 * @author      Francisco J. Lozano B. <fjlozano@felinux.com.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 FELINUX LTDA
 * @version     0.1
 *
 **/

/**
 * Clase encargada de la traducci�n de los textos. Esta clase se encarga 
 * por cada petici�n que se realize a un modulo particular de buscar 
 * b�sicamente dos archivos e incluirlos en el c�digo PHP. Uno de estos es el archivo general de idioma
 * (idiomas/es/general.php) y el archivo de idioma del m�dulo, llamado igual que el m�dulo, asi por
 * ejemplo en el caso de usuarios se encontrar� un archivo /idiomas/es/usuarios.php que contendr� la informaci�n
 * de todos los textos de la aplicaci�n en un arreglo que funciona en el modelo "LLAVE" => "Valor", seg�n esto, a lo
 * largo de la aplicaci�n encontraremos los textos de la siguiente forma:
 * $textos->id('NOMBRES'); -> si nos encontramos en el m�dulo de usuarios esto significa que o bien tengo un indice
 * "NOMBRES" en el archivo usuarios.php, o bien tengo un indice "NOMBRES" en el archivo general.php. En caso de no 
 * existir dicho indice, aparecera directamente el valor suministrado al metodo id(), en este caso "NOMBRES".
 */
class Texto {

    /**
     * Indicador del estado de carga de los textos generales
     * @var l�gico
     */
    public $generales;


    /**
     * Lista de m�dulos para los cuales ya se han cargado los textos
     * @var arreglo
     */
    public $modulos;

    /**
     *
     * Inicializar el objeto con el contenido de los textos para el m�dulo especificado
     *
     * @param cadena $modulo    Nombre �nico del m�dulo en la base de datos
     *
     */
    function __construct($modulo = NULL) {
        global $configuracion, $sesion_idioma, $textos;

        if (empty($textos)) {
            $textos = array();
        }

        if (!$this->generales) {
            $archivo = $configuracion['RUTAS']['idiomas'].'/'.$sesion_idioma.'/'.$configuracion['RUTAS']['archivoGeneral'].'.php';

            if (file_exists($archivo) && is_readable($archivo)) {
                require_once $archivo;
            }

            foreach ($textos as $llave => $texto) {
                $this->{$llave} = $texto;
            }

            $this->generales = true;
        }

        if (!$this->modulos[$modulo]) {
            if (!empty($modulo)) {
                $archivo = $configuracion['RUTAS']['idiomas'].'/'.$sesion_idioma.'/'.strtolower($modulo).'.php';

                if (file_exists($archivo) && is_readable($archivo)) {
                    require_once $archivo;
                }

                foreach ($textos as $llave => $texto) {
                    $this->{$llave} = $texto;
                }
            }

            $this->modulos[$modulo] = true;
        }
    }

    /**
     *
     * Devuelve el texto asociado a la llave indicada
     *
     * @param  cadena $llave    Llave asociada al texto que se debe mostrar
     * @return cadena
     *
     */
    function id($llave) {

        if (isset($this->{$llave})) {
            return $this->{$llave};

        } else {
            return $llave;
        }
    }
    
}
