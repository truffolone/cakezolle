<?php

class ArticoloHelperHelper extends AppHelper 
{
	var $helpers = array('Html','Form');
	
	function isDisponibile($articolo, $giornoConsegna) {
		
		$disponibileAF = true;
		$disponibileML = true;

		if($articolo['Articolo']['DISP_ML'] == 0) $disponibileML = false;
		if($articolo['Articolo']['DISP_AF'] == 0) $disponibileAF = false;

		if( $articolo['Articolo']['DISP_'.strtoupper($giornoConsegna)] == 0 ) {
			$disponibileML = false;
			$disponibileAF = false;
		}

		return ($disponibileML | $disponibileAF);
	}

	//same function in articolo_util component
	function getArticoloPrezzoVendita($articolo) {
		
		//vedi l'associazione nel model Articolo per i dettagli
		if(empty($articolo['PrezzoArticolo'])) return null;
		if(empty($articolo['PrezzoArticolo'][0]['PREZZO_VENDITA'])) return null;
		//in alcuni casi su zolla il prezzo è valorizzato con la virgola invece che con il punto
		return str_replace(",", ".", $articolo['PrezzoArticolo'][0]['PREZZO_VENDITA']);
		
	}
}
