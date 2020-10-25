<?php

App::uses('Component', 'Controller');

class IndirizziClientiUtilComponent extends Component
{
	public function getIndirizzoPrincipale($indirizzi)
	{
		$indirizzo = "";
		foreach($indirizzi as $i) {
			if($i['PRINCIPALE'] == 'SI') {
				$indirizzo = $i;
				break;
			}
		}
		if(empty($indirizzo)) {
			//nessun indirizzo principale. Seleziona il primo disponibile
			$indirizzo = $indirizzi[0];
		}
		return $indirizzo;
	}

	public function getDbKey($giornoConsegna)
	{
		if($giornoConsegna == 1) return 'DISP_LUN';
		else if($giornoConsegna == 2) return 'DISP_MAR';
		else if($giornoConsegna == 3) return 'DISP_MER';
		else if($giornoConsegna == 4) return 'DISP_GIO';
		else if($giornoConsegna == 5) return 'DISP_VEN';
	}

	public function getGiornoConsegnaAsN($indirizzo)
	{
		$giorno = 1;
		if(strtolower($indirizzo['GIORNO_CONSEGNA']) == 'lun') $giorno = 1;
		else if(strtolower($indirizzo['GIORNO_CONSEGNA']) == 'mar') $giorno = 2;
		else if(strtolower($indirizzo['GIORNO_CONSEGNA']) == 'mer') $giorno = 3;
		else if(strtolower($indirizzo['GIORNO_CONSEGNA']) == 'gio') $giorno = 4;
		else if(strtolower($indirizzo['GIORNO_CONSEGNA']) == 'ven') $giorno = 5;
		
		return $giorno;
	}
}
