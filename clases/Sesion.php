<?php

/**
 * Sesion.php, clase del n�cleo del framework
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
 * Clase Sesi�n, encargada como su nombre lo indica de la gesti�n de las sesiones
 * en la aplicaci�n. A trav�s de sus metodos se pueden crear, consultar, modificar
 * y destruir sesiones PHP dentro del framework. Como se puede apreciar a lo largo de
 * la aplicaci�n, las variables super globales son leidas y reescritas, lo mismo sucede 
 * con el arreglo super global $_SESSION, todas y cada una de sus posiciones son leidas
 * y reescritas, as�, $_SESSION['nombreUsuario'] sera reescrita a $sesion_nombreUsuario.
 * Segun lo dicho, los valores de las sesiones pueden ser accedidos a trav�s de variables
 * globales normales y su nombre siempre iniciara por $sesion_.
 * 
 * Nota: para utilizar una variable de sesi�n en cualquier funci�n, dicha variable debe ser
 * previamente declarada como global, por ejemplo:
 * 
 * Situaci�n real: En el framework por defecto cuando un usuario se loguea se crea una variable
 * de sesi�n llamada usuarioSesion que contiene un objeto de la clase usuario con toda la informaci�n 
 * del usuario que inicio la sesi�n. Esto significa que en el arreglo super global existe la posici�n
 * $_SESSION['usuarioSesion'], y dentro del framework existe la variable global $sesion_usuarioSesion.
 * 
 * Seg�n lo anterior, si dentro de una funci�n necesito almacenar el usuario que realiza alguna acci�n
 * usaria por ejemplo:
 * 
 * function almacenarRegistro($registro){
 *  global $sesion_usuarioSesion;
 * 
 *  //c�digo que inserta el registro y usa "$sesion_usuarioSesion->id" para almacenar que usuario realizo la
 * // acci�n
 * 
 * }
 */
class Sesion {
    
    /**
     *
     * @var int identificador de la sesi�n 
     */
    public  static $id;


    /**
     * Iniciar la sesi�n, como su nombre lo indica, se encarga de iniciar la sesi�n y 
     * tambien se establece los l�mites de vida de la misma
     * 
     * @global string $nombre
     */
    public static function iniciar() {
        if (self::$id == "") {
            ini_set("session.cookie_lifetime",108000); 
            ini_set("session.gc_maxlifetime", 108000);
            session_start();
            
        }

        self::$id = session_id();

        foreach ($_SESSION as $variable => $valor) {
            $nombre  = "sesion_".$variable;
            global $$nombre;
            $$nombre = $valor;
        }
        
    }

    /**
     * Finalizar la sesi�n
     */
    public static function terminar() {
        self::destruir(self::$id);
        
    }

    /**
     * Registrar una variable en la sesi�n. Recibe dos parametros, uno es el
     * nombre que va a tener la variable y la otra es su valor, asi, por ejemplo
     * si quiero almacenar una variable llamada nombre con valor Pablo, lo har�a
     * as�: Sesion::registrar("nombre", "Pablo"). con esto ya tendr�a el texto
     * "Pablo" en $sesion_nombre;
     * 
     * @global string $variable el nombre de la posici�n
     * @global string|object|array $valor el valor a ser almacenado en la sesi�n
     * @param object $variable el valor en el super global $_SESSION y en la variable global $sesion_XXXx
     */
    public static function registrar($variable, $valor = "") {
        global $$variable;

        if (isset($valor)) {
            $$variable = $valor;
        }

        $nombre = "sesion_".$variable;

        if (isset($$variable)) {
            global $$nombre;

            $$nombre               = $$variable;
            $_SESSION["$variable"] = $$variable;
        }
        
    }



    /*** Eliminar una variable de sesi�n ***/
    public static function borrar($variable) {
        $nombre = "sesion_".$variable;

        global $$nombre;

        if (isset($$nombre)) {
            unset($$nombre);
            unset($_SESSION["$variable"]);
            
        }
        
    }

    /*** Escribir los datos de una sesi�n ***/
    public static function escribir($id, $contenido) {
        global $sesion_usuarioSesion;

        if (isset($sesion_usuarioSesion) && is_object($sesion_usuarioSesion)) {
//            $actualizaUsuario = self::$sql->modificar("sesiones", array("id_usuario" => $sesion_usuarioSesion->id, "disponible" => "1"), "id = '$id'");

        } elseif (isset($_SESSION["usuarioSesion"])) {
            $usuario          = $_SESSION["usuarioSesion"];
//            $actualizaUsuario = self::$sql->modificar("sesiones", array("id_usuario" => $usuario->id, "disponible" => "1"), "id = '$id'");
        }

        return $resultado;
    }

    /*** Destruir una sesi�n ***/
    public static function destruir($id) {

        foreach ($_SESSION as $variable => $valor) {
            unset($_SESSION[$variable]);
        }

        unset($_SESSION);

        return true;
        
    }


}
