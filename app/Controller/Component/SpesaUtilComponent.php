<?php

App::uses('Component', 'Controller');

class SpesaUtilComponent extends Component
{
	public $components = array('Session');
	
	/*
		@param spese 
			spese da raggruppare
		return
			spese raggruppate per settimana, per tutte le settimane di validità di una data spesa a partire dalla settimana corrente 
			(NON mi limito alla settimana corrente e alle prossime 4 perchè uso questo metodo anche per altro)
			Ogni settimana è identificata con la data (Y-m-d) del primo giorno
			Vengono restituite TUTTE le spese, comprese quelle sospese (mi serve per sapere quali sono le settimane sospese e quindi
			quali sono le consegne che possono essere riattivate)
	*/
	function groupByWeek($spese)
	{
		$giornoConsegna = $this->Session->read('giornoConsegna');
		
		if(empty($spese)) $spese = array(); //il chiamante potrebbe non avere nulla settato
		$speseSettimana = array();
		//poichè posso attivare/sospendere le prossime 4 settimane al massimo, avrò al massimo 6 settimane (la corrente + le prossime 5).
		//Infatti se sospendo la 4° settimana successiva alla corrente, dovrò aggiungere una row con data inizio pari alla 5° settimana
		//Tuttavia, se oggi è un giorno dopo venerdi, la modifica comincerà tra 2 settimane, quindi in realtà ne ho 7
		$currWeek = (date('N') == 1) ? date('Y-m-d', strtotime('last Monday', strtotime('tomorrow'))) : date('Y-m-d', strtotime('last Monday'));		
		for($i=0;$i<7;$i++) {		
			$speseSettimana[ $currWeek ] = array();
			$currWeek = date('Y-m-d', strtotime('next Monday', strtotime($currWeek)));
		}

		$weeks = array_keys($speseSettimana);

		foreach($spese as $spesa)
		{
			if(empty($spesa['DATA_INIZIO'])) continue; //non elaboro le spese che per errore non hanno data inizio
			
			foreach($weeks as $week) 
			{
				if( 
					strtotime($spesa['DATA_INIZIO']) <= strtotime($week) && 
					( empty($spesa['DATA_FINE']) || strtotime($spesa['DATA_FINE']) >= strtotime($week) )
				)
				{
					//la spesa sia che sia attiva, sia che sia sospesa (DATA_INZIO = DATA_FINE) appartiene a questa settimana
					if( $this->isInConsegna($spesa, $week, $giornoConsegna) )
						array_push($speseSettimana[$week], $spesa);		
				}
			}
		}
		
		return $speseSettimana;
	}

	function getRecordsAdded($recordsDb, $recordsSession)
	{
		$records = array();

		//record da aggiungere: record senza ID
		foreach($recordsSession as $record) {
			if(!isset($record['ID_CLIENTE_TIPO_SPESA'])) { 
				array_push($records, $record);
			}
		}

		return $records;
	}	

	function getRecordsModified($recordsDb, $recordsSession)
	{
		$records = array();

		//record da modificare: record con id presenti sul db locale e in sessione ma con diversi valori
		foreach($recordsSession as $record) {
			if(isset($record['ID_CLIENTE_TIPO_SPESA'])) {
				foreach($recordsDb as $recordDb) {
					if($record['ID_CLIENTE_TIPO_SPESA'] == $recordDb['ID_CLIENTE_TIPO_SPESA']) {
						if(
							$record['PERIODICITA'] != $recordDb['PERIODICITA'] ||
							$record['DATA_INIZIO'] != $recordDb['DATA_INIZIO'] ||
							$record['DATA_FINE'] != $recordDb['DATA_FINE'] ) {
							array_push($records, $record);
						}
						break;
					}
				}
			}
		}
		return $records;
	}

	function getRecordsDeleted($recordsDb, $recordsSession)
	{
		$records = array();

		//record da cancellare: record con id presenti sul db locale ma non in sessione
		foreach($recordsDb as $recordDb) {
			if(isset($recordDb['ID_CLIENTE_TIPO_SPESA'])) {
				$foundInSession = false;
				foreach($recordsSession as $record) {
					if($record['ID_CLIENTE_TIPO_SPESA'] == $recordDb['ID_CLIENTE_TIPO_SPESA']) {
						$foundInSession = true;
						break;
					}
				}
				if(!$foundInSession) {
					array_push($records, $recordDb);
				}
			}
		}

		return $records;
	}

	//-----------LE DUE FUNZIONI SEGUENTI SONO LE STESSE IDENTICHE IN spesa_helper.php---------------------

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
