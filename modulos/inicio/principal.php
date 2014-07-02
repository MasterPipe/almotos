<?php

/**
 *
 * Archivo encargado de gestionar los elementos que se presentan en la p�gina principal:
 * - Resumen de noticias: Las cuatro (4) �ltimas noticias
 * - Resumen de blogs: Las cinco (5) �ltimas entradas del blog
 *
 * @package     FOLCS
 * @subpackage  Inicio
 * @author      Pablo Andr�s V�lez Vidal  .:PAVLOV..; <pavelez@colomboamericano.edu.co>
 * @author      Julian A. Mondrag�n <jmondragon@felinux.com.co>
 * @license     http://www.gnu.org/licenses/gpl.txt
 * @copyright   Copyright (c) 2009 CCCA
 * @version     0.1
 *
 **/

global $sesion_usuarioSesion;

/**
 * Resumen de las �ltimas cuatro noticias para animaci�n principal
 **/
$noticias          = new Noticia();
$contador          = 0;
$imagenes          = "";
$bloqueIzquierdo   = "";
$titulares         = array();
$miniaturas        = array();


if($noticias->registros){
    foreach ($noticias->listar(0, 4, "", "") as $noticia) {

        $contador++;
        $imagenes     .= HTML::enlace(HTML::imagen($noticia->imagenPrincipal, "noticia_$contador", "", array("alt" => "noticia_$contador")), $noticia->url);
        $miniaturas[]  = HTML::enlace(HTML::parrafo("".$contador, "noticia_$contador ", "", array("alt" => "noticia_$contador")), $noticia->url);
        //$miniaturas[]  = HTML::enlace(HTML::imagen($noticia->imagenMiniatura, "noticia_$contador", "", array("alt" => "noticia_$contador")), $noticia->url);
        $titulares[]   = HTML::enlace(HTML::parrafo($noticia->titulo, "noticia_$contador", "", array("alt" => "noticia_$contador")), $noticia->url);

    }
}


$resumenNoticias   = HTML::contenedor(HTML::lista($titulares, "", "", "bannerNoticias"), "bannerNoticias ");
$resumenNoticias  .= HTML::contenedor($textos->id("QUE_HA_PASADO"), "tituloBanner");

//$resumenNoticias  .= HTML::contenedor("", "bannerTransparencia");

$resumenNoticias  .= HTML::contenedor($imagenes, "", "animacionNoticias");
$resumenNoticias  .= HTML::lista($miniaturas, "", "", "titularesNoticias");
$resumenNoticias   = HTML::bloqueNoticias("resumenNoticias", $textos->id("NOTICIAS"), $resumenNoticias);


$pagina = new Pagina();
$paginas = array();

        foreach ($pagina->listar(0, 5, array(0)) as $elemento) {
            
            $paginas[$elemento->titulo] = $elemento->contenido;
         
        }

$bloqueIzquierdo .= HTML::pestanas("pestanasResumenes", $paginas);

//$bloqueIzquierdo = "que joda, que me mato esto";

Plantilla::$etiquetas["BLOQUE_NOTICIA"]  = $resumenNoticias;
Plantilla::$etiquetas["BLOQUE_CENTRAL"]  = $bloqueIzquierdo;


?>
