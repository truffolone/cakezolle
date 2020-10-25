<?php

class SpesaHelperHelper extends AppHelper 
{
	var $helpers = array('Html','Form');

	//-----------LE DUE FUNZIONI SEGUENTI SONO LE STESSE IDENTICHE IN spesa_util.php---------------------

	/*
		verifica se la spesa è in consegna nella settimana selezionata
		
		@param spesa
			spesa di cui valutare se in consegna nella settimana
		@param weekStart
			timestamp nel primo giorno della settimana (ottenuto da strtotime)
		@param giornoConsegna
			giorno di consegna ottenuto dall'indirizzo
	*/
	function isInConsegna($spesa, $weekStart, $giornoConsegna)
	{
		$timeConsegna = $this->_getTimeConsegna($weekStart, $giornoConsegna);

		if( trim($spesa['PERIODICITA']) == '12345') {
			return true;
		}
		else if( trim($spesa['PERIODICITA']) == '135' || strtolower(trim($spesa['PERIODICITA'])) == 'di') {
			//settimane dispari dell'anno
			$settimanaAnno = date('W', $timeConsegna);
			if($settimanaAnno%2 == 1) return true;
			return false;
		}
		else if( trim($spesa['PERIODICITA']) == '24' || strtolower(trim($spesa['PERIODICITA'])) == 'pa') {
			//settimane pari dell'anno
			$settimanaAnno = date('W', $timeConsegna);
			if($settimanaAnno%2 == 0) return true;
			return false;
		}
		else {
			$settimanaAnno = date('W', $timeConsegna);
			//numeri di settimana specifici
			$settimane = explode(",", $spesa['PERIODICITA']);
			foreach($settimane as $settimana) {
				if( $settimanaAnno == trim($settimana) ) return true;
			}
			return false;
		}

		return false; //default		
	}

	function _getTimeConsegna($weekStart, $giorno)
	{
		//utilizzo GIORNO_CONSEGNA perchè NUOVO_GIORNO_CONSEGNA non sempre è settato
		if($giorno == 'lun') $giornoConsegna = 'Monday';
		else if($giorno == 'mar') $giornoConsegna = 'Tuesday';
		else if($giorno == 'mer') $giornoConsegna = 'Wednesday';
		else if($giorno == 'gio') $giornoConsegna = 'Thursday';
		else if($giorno == 'ven') $giornoConsegna = 'Friday';
		else $giornoConsegna = 'Monday'; //default

		//calcola a che settimana del mese appartiene il giorno di consegna nella settimana selezionata
		if($giornoConsegna == 'Monday') $timeConsegna = strtotime($weekStart); //è già 'Monday'
		else $timeConsegna = strtotime('next '.$giornoConsegna, strtotime($weekStart));

		return $timeConsegna;
	}
}
