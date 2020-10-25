<?php

App::uses('Component', 'Controller');

class ConsegneUtilComponent extends Component
{
	public $components = array('Session');
	
	function printDate($date) {
		return $this->giornoNumToStr(date('N', strtotime($date))).' '.$this->getDataConsegna( date('d-m-Y', strtotime($date)) );
	}
	
	/**
	 * funzione usata per visualizzare gli articoli a ML chiuso
	 */
	function getDataAcquistoDuranteUltimoML($spese, $giornoConsegna, $env='MLV') {
		$dettagliML = $this->Session->read($env == 'MLV' ? 'ML.dettagli' : 'MLP.dettagli');
		if( !empty($dettagliML['raw_data']) ) return ''; // la invoco/uso solo se ml è chiuso
		// ottieni il giorno di consegna nella settimana corrente
		$days = array(
			1 => 'Monday',
			2 => 'Tuesday',
			3 => 'Wednesday',
			4 => 'Thursday',
			5 => 'Friday',
			6 => 'Saturday',
			7 => 'Sunday'
		);
		$env = strtolower($env);
		$n_giorno_consegna = $this->getGiornoConsegnaAsN($giornoConsegna);
		$t_consegna_potenziale = strtotime($days[$n_giorno_consegna].' this week'); // con php > 5.3 la settimana inzia orrettamente da lunedi
		// calcola la data di chiusura del ML per questa settimana
		$days_diff = Configure::read($env.'.'.$giornoConsegna.'.ml.giorno_chiusura');
		$d_chiusura = date('Y-m-d', $t_consegna_potenziale - $days_diff*24*60*60);
		$t_chiusura = strtotime( $d_chiusura.' '.Configure::read($env.'.'.$giornoConsegna.'.ml.orario_chiusura') );
		// a seconda di quanti giorni prima il ML chiude, alla data corrente la data di chiusura del ML potrebbe essere quella
		// della settimana precedente, se necessario spostati avanti di una settimana o più settimane (per MLP potrebbero essere due)
		while( $t_chiusura + 7*24*60*60 < time() ) {
			$t_chiusura += 7*24*60*60;
		}
		
		// calcola la data di consegna riferita a quell'ultimo ml
		$t_consegna_potenziale = $t_chiusura + $days_diff*24*60*60;
		// determina la data per cui il cliente acquistava nell'ultimo ML:
		// - se il cliente riceve con una periodicità "complessiva" (data dalla somma di tutte le sue spese) 12345 è t_consegna
		// - se il cliente ha una periodicità complessiva pari o dispari sarà t_consegna o la successiva (a seconda)
		// - se ha solo spese con periodicità a settimana fissa NON considero la cosa (se riceve solo in poche settimane
		//   dell'anno con ogni probabilità non è abilitato per il ML)
		$periodicita_complessiva = '';
		foreach($spese as $s) {
			$p = trim($s['PERIODICITA']);
			if($p == '12345') {
				$periodicita_complessiva = '12345';
				break;
			}
			elseif($p == '135' || $p == 'di') {
				if($periodicita_complessiva == '24') {
					$periodicita_complessiva = '12345';
					break;
				}
				$periodicita_complessiva = '135';
			}
			elseif($p == '24' || $p == 'pa') {
				if($periodicita_complessiva == '135') {
					$periodicita_complessiva = '12345';
					break;
				}
				$periodicita_complessiva = '24';
			}
		}
		
		if($periodicita_complessiva == '') {
			return ''; // non posso individuare la data
		}
		
		$W = date('W', $t_consegna_potenziale);
		if( $periodicita_complessiva == '12345' ) {
			return date('Y-m-d', $t_consegna_potenziale);
		}
		elseif( $periodicita_complessiva == '135' ) {
			if( $W%2 == 0 ) { // riceve la prossima settimana
				return date('Y-m-d', $t_consegna_potenziale + 7*24*60*60);
			}
			else { // riceve questa settimana
				return date('Y-m-d', $t_consegna_potenziale);
			}
		}
		else { // periodicità 24
			if( $W%2 == 1 ) { // riceve la prossima settimana
				return date('Y-m-d', $t_consegna_potenziale + 7*24*60*60);
			}
			else { // riceve questa settimana
				return date('Y-m-d', $t_consegna_potenziale);
			}
		}
		
	}

	function giornoNumToStr($n) {
		$days[1] = 'Lunedi';
		$days[2] = 'Martedi';
		$days[3] = 'Mercoledi';
		$days[4] = 'Giovedi';
		$days[5] = 'Venerdi';
		$days[6] = 'Sabato';
		$days[7] = 'Domenica';

		return $days[$n];
	}

	function getGiornoConsegnaAsStr($giornoConsegna)
	{
		$days['lun'] = 'Lunedi';
		$days['mar'] = 'Martedi';
		$days['mer'] = 'Mercoledi';
		$days['gio'] = 'Giovedi';
		$days['ven'] = 'Venerdi';
		$days['sab'] = 'Sabato';
		$days['dom'] = 'Domenica';

		return $days[$giornoConsegna];
	}

	function getGiornoConsegnaAsN($giornoConsegna) {
		$days['lun'] = 1;
		$days['mar'] = 2;
		$days['mer'] = 3;
		$days['gio'] = 4;
		$days['ven'] = 5;
		$days['sab'] = 6;
		$days['dom'] = 7;

		return $days[$giornoConsegna];
	}

	function getIndiceSettimanaInizioModifiche($spese_settimana, $giornoConsegna)
	{
		$weeks = array_keys($spese_settimana);
		for($i=0;$i<sizeof($weeks);$i++) {
			//ottieni la data di consegna per la settimana corrente
			$dataConsegnaCurrWeek = date('Y-m-d', strtotime($weeks[$i])+24*60*60*($this->getGiornoConsegnaAsN($giornoConsegna)-1) );
			//calcola la deadline per le modifiche della settimana corrente
			$deadlineCurrWeek = strtotime($dataConsegnaCurrWeek.' '.Configure::read('mlv.'.$giornoConsegna.'.spese.orario_chiusura')) + 24*60*60*Configure::read('mlv.'.$giornoConsegna.'.spese.giorno_chiusura');
			if(time() < $deadlineCurrWeek) {
				//posso modificare la settimana corrente
				return $i;
			}		
		}
		
		return -1;
	}

	function getIndiceSettimanaAcquistoAF($af_settimana, $giornoConsegna)
	{
		$weeks = array_keys($af_settimana);
		for($i=0;$i<sizeof($weeks);$i++) {
			//ottieni la data di consegna per la settimana corrente
			$dataConsegnaCurrWeek = date('Y-m-d', strtotime($weeks[$i])+24*60*60*($this->getGiornoConsegnaAsN($giornoConsegna)-1) );
			//calcola la deadline per le modifiche della settimana corrente
			$deadlineCurrWeek = strtotime($dataConsegnaCurrWeek.' '.Configure::read('mlv.'.$giornoConsegna.'.af.orario_chiusura')) + 24*60*60*Configure::read('mlv.'.$giornoConsegna.'.af.giorno_chiusura');
			if(time() < $deadlineCurrWeek) {
				//posso modificare la settimana corrente
				return $i;
			}		
		}
		
		return -1;
	}

	function getIndiceSettimanaAcquistoML($spese_settimana, $giornoConsegna, $env='MLV') {
		$settimanaML = -1;
		$weeks = array_keys($spese_settimana);
		
		$env = strtolower($env);
		
		for($i=0;$i<sizeof($weeks);$i++) {
			//ottieni la data di consegna per la settimana corrente
			$dataConsegnaCurrWeek = date('Y-m-d', strtotime($weeks[$i])+24*60*60*($this->getGiornoConsegnaAsN($giornoConsegna)-1) );
			//calcola la deadline per le modifiche della settimana corrente
			$aperturaMlCurrWeek = strtotime($dataConsegnaCurrWeek.' '.Configure::read($env.'.'.$giornoConsegna.'.ml.orario_apertura')) + 24*60*60*Configure::read($env.'.'.$giornoConsegna.'.ml.giorno_apertura');
			$chiusuraMlCurrWeek = strtotime($dataConsegnaCurrWeek.' '.Configure::read($env.'.'.$giornoConsegna.'.ml.orario_chiusura')) + 24*60*60*Configure::read($env.'.'.$giornoConsegna.'.ml.giorno_chiusura');
			if($aperturaMlCurrWeek <= time() && time() < $chiusuraMlCurrWeek) {
				$settimanaML = $i;
				break;
			}		
		}
		
		if($settimanaML != -1) {
			// ATTENZIONE: SE UN CLIENTE RICEVE SOLO SPESE ALTERNATE (ES. RICEVE QUALCOSA SOLO NELLE SETTIMANE DISPARI O PARI)
			// OTTERREI UN RISULTATO ERRATO PERCHÈ ANDREBBE AD AGGIUNGERE SPESE IN SETTIMANE ERRATE (SENZA SCATOLA!)
			// SE NON CI SONO SPESE DISPONIBILI PER QUELLA SETTIMANA (NOTA: spese_settimana CONTIENE GIÀ LE SPESE CORRETTE
			// IN BASE ALLA PERIODOCITÀ) IL CLIENTE NON DEVE POTER OPERARE DURANTE QUESTA SETTIMANA
			// ( CLIENTI POSSONO OPERARE SOLO NELLA SETTIMANA PRECEDENTE ALLA CONSEGNA )
			if( empty($spese_settimana[ $weeks[$settimanaML] ]) ) $settimanaML = -1;
		}
		
		return $settimanaML;
	}

	function getGiornoAperturaMlAsStr($ml_settimana, $giornoConsegna, $env = 'MLV') {
		$env = strtolower($env);
		$weeks = array_keys($ml_settimana);
		$dataConsegnaCurrWeek = date('Y-m-d', strtotime($weeks[0])+24*60*60*($this->getGiornoConsegnaAsN($giornoConsegna)-1) );
		$aperturaMl = strtotime($dataConsegnaCurrWeek.' '.Configure::read($env.'.'.$giornoConsegna.'.ml.orario_apertura')) + 24*60*60*Configure::read($env.'.'.$giornoConsegna.'.ml.giorno_apertura');
		$n = date('N', $aperturaMl);
		return $this->giornoNumToStr($n).' ore '.Configure::read($env.'.'.$giornoConsegna.'.ml.orario_apertura');
	}


	/*
		verifica che la situazione del cliente in termini di consegne durante le settimane sia ok
		
		@param spese_settimana
			spese raggruppate per settimana
		@param af_settimana
			articoli fissi raggruppati per settimana
		@param ml_settimana
			mercato libero raggruppati per settimana
	*/
	function areConsegneOk($spese_settimana, $af_settimana, $ml_settimana)
	{
		$weeks = array_keys($spese_settimana); //NOTA: sono le stesse settimane anche per af e ml		
		foreach($weeks as $week) {
			if(!$this->isSettimanaOk($spese_settimana[$week], $af_settimana[$week], $ml_settimana[$week])) {
				debug($spese_settimana[$week]);
				debug($af_settimana[$week]);
				debug($ml_settimana[$week]);
				//die;
				return false;
			} 
		}
		return true;
	}

	/*
		verifica che la settimana corrente sia ok, ovvero che o tutto sia sospeso o tutto sia in consegna (una settimana o è tutta sospesa
		o tutta in consegna!)
	
		@param spese
			spese nella settimana
		@param af
			articoli fissi nella settimana
		@param ml
			mercato libero nella settimana
	*/
	function isSettimanaOk($spese, $af, $ml)
	{
		$spese_num = 0;
		foreach($spese as $s) {
			if( strpos($s['TIPO_SPESA'],'S_LI') === 0 ) continue; // verifico se comincia per S_LI perchè esistono spese S_LI* di sistema
			if($this->isRecordAnnullato($s)) continue;	
			$spese_num++;
		}
		$af_num = 0;
		foreach($af as $a) {
			if($this->isRecordAnnullato($a)) continue;	
			$af_num++;
		}
		$ml_num = sizeof($ml);
		
		if( $spese_num == 0 && $af_num == 0 && $ml_num == 0) return true; //semplicemente non c'è nessuna consegna in quella settimana

		if(!$this->isSettimanaSospesa($spese, $af, $ml) && !$this->isSettimanaAttiva($spese, $af, $ml)) return false;
		return true;
	}

	function isSettimanaSospesa($spese, $af, $ml)
	{
		$spese_num = 0;
		$spese_attive = 0;
		$spese_sospese = 0;
		foreach($spese as $s) {
			if( strpos($s['TIPO_SPESA'],'S_LI') === 0 ) continue; // verifico se comincia per S_LI perchè esistono spese S_LI* di sistema
			if($this->isRecordAnnullato($s)) continue;	
			$spese_num++;
			if($this->isRecordSospeso($s)) $spese_sospese++;
			else $spese_attive++;
		}
		$af_num = 0;
		$af_attivi = 0;
		$af_sospesi = 0;
		foreach($af as $a) {
			if($this->isRecordAnnullato($a)) continue;	
			$af_num++;
			if($this->isRecordSospeso($a)) $af_sospesi++;
			else $af_attivi++;	
		}
		$ml_num = sizeof($ml);
		
		if( $spese_num == 0 && $af_num == 0 && $ml_num == 0) return false; //semplicemente non c'è nessuna consegna in quella settimana
		if( $spese_sospese == $spese_num && $af_sospesi == $af_num && $ml_num == 0) return true;
		return false;
	}
	
	function isSettimanaAttiva($spese, $af, $ml)
	{
		$spese_num = 0;
		$spese_attive = 0;
		$spese_sospese = 0;
		foreach($spese as $s) {
			if( strpos($s['TIPO_SPESA'],'S_LI') === 0 ) continue; // verifico se comincia per S_LI perchè esistono spese S_LI* di sistema
			if($this->isRecordAnnullato($s)) continue;	
			$spese_num++;
			if($this->isRecordSospeso($s)) $spese_sospese++;
			else $spese_attive++;
		}
		$af_num = 0;
		$af_attivi = 0;
		$af_sospesi = 0;
		foreach($af as $a) {
			if($this->isRecordAnnullato($a)) continue;	
			$af_num++;
			if($this->isRecordSospeso($a)) $af_sospesi++;
			else $af_attivi++;	
		}
		$ml_num = sizeof($ml);
		
		if( $spese_num == 0 && $af_num == 0 && $ml_num == 0) return false; //semplicemente non c'è nessuna consegna in quella settimana
		if( $spese_attive == $spese_num && $af_attivi == $af_num) return true;
		return false;
	}

	/*
		verifica se la consegna può essere sospesa o attivata
	*/
	function isConsegnaModificabile($data, $giornoConsegna)
	{
		if(!$this->isPrimoGiornoSettimana($data)) return false;

		$dataConsegna = date('Y-m-d', strtotime($data)+24*60*60*($this->getGiornoConsegnaAsN($giornoConsegna)-1) );
		//calcola la deadline per la modifica della settimana
		$deadline = strtotime($dataConsegna.' '.Configure::read('mlv.'.$giornoConsegna.'.spese.orario_chiusura')) + 24*60*60*Configure::read('mlv.'.$giornoConsegna.'.spese.giorno_chiusura');
		if(time() < $deadline) {
			return true;
		}
		return false;		
	}

	function isPrimoGiornoSettimana($data)
	{
		if( strtotime($data) === FALSE ) return false; //argomento non è una data valida
		if( date('N', strtotime($data)) == 1 ) return true;
		return false; 
	}

	/*
		restituisce uno unique id che non collida con nessuno di quelli già presenti nel campo con chiave $key di ogni elemento di $records
	*/
	function getUniqueTmpID($records, $key)
	{
		do {
			$isUnique = true;
			$uniqueID = 'Z'.strtoupper(uniqid());
			foreach($records as $record) {
				if($record[$key] == $uniqueID) {
					$isUnique = false;
					break;
				}
			}
		}
		while(!$isUnique);
		
		return $uniqueID;
	}

	/*
		prepara i record Spesa, AcquistoArticoloFisso, AcquistoMercatoLibero associati ai clienti per la scrittura su fattoria
		rimuovendo tutti gli id temporanei creati per i record
	*/
	function removeTmpIDs($cliente)
	{
		//esegui i controlli su una copia perchè altrimenti eseguo degli unset all'interno un loop che potrebbero generare conflitti 
		//nella lettura degli indici
		$clCopy = $cliente;

		for($i=0;$i<sizeof($clCopy['Spesa']);$i++) {
			if(isset($clCopy['Spesa'][$i]['ID_CLIENTE_TIPO_SPESA'])) {
				if(!is_numeric($clCopy['Spesa'][$i]['ID_CLIENTE_TIPO_SPESA'])) unset($cliente['Spesa'][$i]['ID_CLIENTE_TIPO_SPESA']);
			}
		}
		for($i=0;$i<sizeof($clCopy['AcquistoArticoloFisso']);$i++) {
			if(isset($clCopy['AcquistoArticoloFisso'][$i]['ID_CLIENTE_ARTICOLO_SPESA'])) {
				if(!is_numeric($clCopy['AcquistoArticoloFisso'][$i]['ID_CLIENTE_ARTICOLO_SPESA'])) unset($cliente['AcquistoArticoloFisso'][$i]['ID_CLIENTE_ARTICOLO_SPESA']);
			}
		}
		for($i=0;$i<sizeof($clCopy['AcquistoMercatoLibero']);$i++) {
			if(isset($clCopy['AcquistoMercatoLibero'][$i]['ID'])) {
				if(!is_numeric($clCopy['AcquistoMercatoLibero'][$i]['ID'])) unset($cliente['AcquistoMercatoLibero'][$i]['ID']);
			}
		}

		return $cliente;
	}

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

	function isRecordSospeso($r) {
		if(!isset($r['DATA_FINE'])) return false; //i record che crea l'area riservata non hanno il campo valorizzato
		if($r['DATA_INIZIO'] == $r['DATA_FINE']) return true;
		return false;
	}
		
	function isRecordAnnullato($r) {
		if(!isset($r['DATA_FINE'])) return false; //i record che crea l'area riservata non hanno il campo valorizzato
		if(strtotime($r['DATA_INIZIO'])-24*60*60 == strtotime($r['DATA_FINE']) ) return true;
		return false;
	}

	function getDateDaVisualizzare($spese_settimana, $af_settimana, $ml_settimana, $indirizzo, $giornoConsegna) {
		$weeks = array_keys($spese_settimana);

		$indice_af = $this->getIndiceSettimanaAcquistoAF($af_settimana, $indirizzo['GIORNO_CONSEGNA']);
		if($indice_af >= 0) {
			$return['data_af'] = $this->printDate( date('Y-m-d', strtotime($weeks[$indice_af])+24*60*60*($giornoConsegna-1)) );
		}
		else {
			$return['data_af'] = '';
		}

		$indice_ml = $this->getIndiceSettimanaAcquistoML($ml_settimana, $indirizzo['GIORNO_CONSEGNA']);
		if($indice_ml >= 0) {
			$return['data_ml'] = $this->printDate( date('Y-m-d', strtotime($weeks[$indice_ml])+24*60*60*($giornoConsegna-1)) );
		}
		else {
			$return['data_ml'] = '';
		}

		return $return;
	}
}
