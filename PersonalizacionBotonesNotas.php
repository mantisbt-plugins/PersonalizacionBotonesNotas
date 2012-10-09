<?php

class PersonalizacionBotonesNotasPlugin extends MantisPlugin {

   private $resumenParaCorreo = "";
   
   function register() {
      $this->name = lang_get( 'plugin_personalizacionbotonesnotas_title' );
      $this->description = lang_get( 'plugin_personalizacionbotonesnotas_description' );
      $this->page = '';
      $this->version = '1.00';
      $this->requires = array( 'MantisCore' => '1.2.0', );
      $this->author = '';
      $this->contact = '';
      $this->url = '';
   }

   function init() {
      //ASOCIO EL METODO 'CONSEGUIRRESUMENPARACORREO' CON EL EVENTO 'EVENT_VIEW_BUGNOTES_START', EL CUAL SE DISPARA UNA UNICA VEZ, QUE ES
      //JUSTO ANTES DE QUE SE EMPIECE A PINTAR EL CONJUNTO DE BOTONES. ASI CONSIGO EL ASUNTO PARA LOS CORREOS, QUE SERA COMUN PARA TODAS LAS 
      //NOTAS.
      plugin_event_hook( 'EVENT_VIEW_BUGNOTES_START', 'conseguirResumenParaCorreo' );
      //ASOCIO EL METODO 'PINTARBOTONES' CON EL EVENTO 'EVENT_VIEW_BUGNOTE', EL CUAL SE DISPARA POR CADA NOTA
      plugin_event_hook( 'EVENT_VIEW_BUGNOTE', 'pintarBotones' );
   }
   
   function conseguirResumenParaCorreo($tipo_evento, $f_bug_id, $f_bug_notes) {	      
      $this->resumenParaCorreo = bug_format_summary( $f_bug_id, SUMMARY_FIELD );
      //LO SIGUIENTE ES PARA ELIMINAR EL IDENTIFICADOR NUMERICO (SEGUIDO DE LOS DOS PUNTOS) QUE VA ANTES DE LA DESCRIPCION DE LA INCIDENCIA
      $encontradoIdEnResumen = strpos($this->resumenParaCorreo, ":");
      if ($encontradoIdEnResumen != false) {
         if (strlen($this->resumenParaCorreo)>=$encontradoIdEnResumen+2) {
            $this->resumenParaCorreo = substr($this->resumenParaCorreo, $encontradoIdEnResumen+2, strlen($this->resumenParaCorreo));
         }
      }
   }

   function pintarBotones($tipo_evento, $f_bug_id, $f_bug_note_id, $private) {
      $cuerpo = bugnote_get_text( $f_bug_note_id );
      //ASI ELIMINO LOS SALTOS DE LINEA, RETORNO DE CARRO, COMILLAS SIMPLES Y COMILLAS DOBLES, QUE HACEN QUE SE PRODUZCA ERROR JAVASCRIPT.   
      //LOS SUSTITUYO POR CARACTERES QUE NO DEN PROBLEMAS Y QUE ADEMAS SIGAN TENIENDO EL MISMO SIGNIFICADO EN EL CORREO
      $cuerpo = str_replace("\n", "%0D%0A", $cuerpo);
      $cuerpo = str_replace("\r", "", $cuerpo);
      $cuerpo = str_replace("'", "`", $cuerpo);
      $cuerpo = str_replace("\"", "`", $cuerpo);
      
      $asuntoFirefox = $this->resumenParaCorreo;
      //PARA IE HACE FALTA REEMPLAZAR LOS CARACTERES ESPECIALES POR SUS REPRESENTACIONES UNICODE PUES SI NO SE REPRESENTAN MAL EN LOS CORREOS
      $asuntoIE = $this->reemplazarCaracteresEspeciales($asuntoFirefox);
      
      $cuerpoFirefox = $cuerpo;
      //PARA IE HACE FALTA REEMPLAZAR LOS CARACTERES ESPECIALES POR SUS REPRESENTACIONES UNICODE PUES SI NO SE REPRESENTAN MAL EN LOS CORREOS
      $cuerpoIE = $this->reemplazarCaracteresEspeciales($cuerpoFirefox);
                              
      $script = "<script>";
      $script = $script . "var primerElemento = document.getElementById('c" . $f_bug_note_id . "');";
      $script = $script . "var segundoElemento = primerElemento.getElementsByTagName(\"td\");";
      $script = $script . "var tercerElemento = segundoElemento[0];";
      $script = $script . "var nuevoBoton = document.createElement('input');";
      $script = $script . "nuevoBoton.type = 'submit';";
      $script = $script . "nuevoBoton.className = 'button-small';";
      $script = $script . "nuevoBoton.value = '" . lang_get( 'plugin_personalizacionbotonesnotas_enviar' ) . "';";                              
      $script = $script . 'var ua = navigator.userAgent.toLowerCase();';      
      $script = $script . 'if (ua.indexOf("msie")!=-1) { ';      
      $script = $script . "nuevoBoton.setAttribute('onclick', 'location.href=\'mailto:?subject=" . $asuntoIE . "&body=" . $cuerpoIE . "\'');";
      $script = $script . '} else { ';            
      $script = $script . "nuevoBoton.setAttribute('onclick', 'location.href=\'mailto:?subject=" . $asuntoFirefox . "&body=" . $cuerpoFirefox . "\'');";
      $script = $script . '}';
      $script = $script . "tercerElemento.appendChild(nuevoBoton);";
      $script = $script . '</script>';
      
      echo $script;                                                      
   }
   
   function reemplazarCaracteresEspeciales($cadena) {
      $resultado = str_replace("á", "%E1", $cadena);
      $resultado = str_replace("é", "%E9", $resultado);
      $resultado = str_replace("í", "%ED", $resultado);
      $resultado = str_replace("ó", "%F3", $resultado);
      $resultado = str_replace("ú", "%FA", $resultado);
      $resultado = str_replace("ñ", "%F1", $resultado);
      $resultado = str_replace("Á", "%C1", $resultado);
      $resultado = str_replace("É", "%C9", $resultado);
      $resultado = str_replace("Í", "%CD", $resultado);
      $resultado = str_replace("Ó", "%D3", $resultado);
      $resultado = str_replace("Ú", "%DA", $resultado);
      $resultado = str_replace("Ñ", "%D1", $resultado);
      
      return $resultado;
   }

}
