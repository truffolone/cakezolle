<?php

App::uses('Component', 'Controller');

class ClientiUtilComponent extends Component
{
	/*
		associa al cliente i modelli 'Spesa', 'AcquistoArticoloFisso', 'AcquistoMercatoLibero' come subarray vuoti se non presenti.
		Necessario per il corretto funzionamento di altri metodi
	*/
	function setMissingModels($cliente)
	{
		if(!isset($cliente['Spesa'])) $cliente['Spesa'] = array();
		if(!isset($cliente['AcquistoArticoloFisso'])) $cliente['AcquistoArticoloFisso'] = array();
		if(!isset($cliente['AcquistoMercatoLibero'])) $cliente['AcquistoMercatoLibero'] = array();

		return $cliente;
	}
	
	function getRecapitiPerTipo($recapiti, $tipo) {
		$recapitiPerTipo = array();
		$recapitiGenerali = array();
		
		foreach($recapiti as $r) {
			if(isset($r['Recapito'])) $r = $r['Recapito'];
			if($r['TIPO'] != 'email') continue;
			if( !filter_var($r['RECAPITO'], FILTER_VALIDATE_EMAIL ) ) continue;
			
			if(strpos($r['COMUNICAZIONI'], $tipo) !== false) {
				$recapitiPerTipo[] = $r['RECAPITO'];
			}
			if(empty($r['COMUNICAZIONI'])) {
				$recapitiGenerali[] = $r['RECAPITO'];
			}
		}
		if( empty($recapitiPerTipo) ) $recapitiPerTipo = $recapitiGenerali;
		
		if(empty($recapitiPerTipo)) $recapitiPerTipo = array('cliente.senza.mail@zolle.it');
		
		return $recapitiPerTipo;
		
	}
}
