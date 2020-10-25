<?php

class ConsegneHelperHelper extends AppHelper 
{
	var $helpers = array('Html','Form');

	function getGiornoConsegna($giornoConsegna)
	{
		$giorno = 'Lunedi';
		if($giornoConsegna == 'lun') $giorno = 'Lunedi';
		else if($giornoConsegna == 'mar') $giorno = 'Martedi';
		else if($giornoConsegna == 'mer') $giorno = 'Mercoledi';
		else if($giornoConsegna == 'gio') $giorno = 'Giovedi';
		else if($giornoConsegna == 'ven') $giorno = 'Venerdi';

		return $giorno;
	}

	function getDataConsegna($dataConsegna)
	{
		$tokens = explode('-', $dataConsegna);

		$mesi['01'] = 'Gennaio';
		$mesi['02'] = 'Febbraio';
		$mesi['03'] = 'Marzo';
		$mesi['04'] = 'Aprile';
		$mesi['05'] = 'Maggio';
		$mesi['06'] = 'Giugno';
		$mesi['07'] = 'Luglio';
		$mesi['08'] = 'Agosto';
		$mesi['09'] = 'Settembre';
		$mesi['10'] = 'Ottobre';
		$mesi['11'] = 'Novembre';
		$mesi['12'] = 'Dicembre';

		return $tokens[0].' '.$mesi[ $tokens[1] ].' '.$tokens[2];
	}

	function getDescrizionePeriodicita($periodicita)
	{
		$strPer = "";
		if($periodicita == '12345') $strPer = 'tutte le settimane';
		else if($periodicita == '135' || $periodicita == 'di') $strPer = 'settimane dispari';
		else if($periodicita == '24' || $periodicita == 'pa') $strPer = 'settimane pari';
					
		return $strPer;
	}
} 
