<?php

App::uses('AppModel', 'Model', 'CakeSession');

class Mlv extends AppModel {

	public $useTable = 'mercato_libero';
	
	function getCurrGiornoConsegna() {
		$Cliente = ClassRegistry::init('Cliente');
		$dataAcquisto = $this->getDataAcquistoOrNull();
		if(empty($dataAcquisto)) { // ml chiuso
			try {
				$giornoConsegna = $Cliente->getGiornoConsegna();
			}
			catch(Exception $e) {
				$giornoConsegna = null;
			}
		}
		else {
			$giornoConsegna = $this->weekDayEngToIta( date('D', strtotime($dataAcquisto)) );
		}
		return $giornoConsegna;
	}
		
	/**
	 * 
	 */
	function aggiornaDataAcquistoInSessione() {
		$date = $this->getDateAcquisto();
		// allo stato attuale il sistema consente di operare su di una sola data (sia per mlv sia per mlp)
		// quindi scelgo (di default) la prima data di acquisto disponibile, ma una volta capito come
		// dire al cliente "ora su MLV stai comprando per la data xxx piuttosto che yyy" il sistema 
		// e' gia' pronto per operare in tal senso
		$dataAcquisto = null;
		if(!empty($date)) {
			$dataAcquisto = $date[0];
		}
		CakeSession::write("{$this->getType()}.data_acquisto", $dataAcquisto);
	}
	
	/**
	 * restituisce, in base alla data corrente, alle tempistiche di apertura impostate e alle prossime
	 * consegne per il cliente le date per cui puo' acquistare in questo momento
	 * Puo' essercene piu' di una perche' potrebbe ricevere piu' spese (in giorni diversi) nell'arco
	 * della settimana
	 * Se vuoto il mercato libero e' chiuso
	 */
	function getDateAcquisto() {
		$Consegna = ClassRegistry::init('Consegna');
		$prossimeConsegne = $Consegna->getProssimePerNumSettimane(4); // mi tengo "largo" perche' a seconda della periodicita' e delle tempistiche di apertura le date potrebbero essere lontane
		$dateAcquisto = [];
		foreach($prossimeConsegne as $prossimaConsegna) {
			$timestampApertura = $this->getTimestampApertura($prossimaConsegna['date']);
			$timestampChiusura = $this->getTimestampChiusura($prossimaConsegna['date']);
			if($timestampApertura <= time() && time() <= $timestampChiusura ) {
				$dateAcquisto[] = [
					'date' => $prossimaConsegna['date'],
					'scatola_id' => $prossimaConsegna['scatola_id_for_ml']
				];
			}
		}
		usort($dateAcquisto, function($a, $b){
			if($a['date'] == $b['date']) return 0;
			return $a['date'] < $b['date'] ? -1 : 1;
		});
		return $dateAcquisto;
	}
	
	/**
	 * in caso di mercato libero chiuso mi dice a partire da quando potro' acquistare (info al cliente)
	 */
	function getProssimaDataApertura() {
		$Consegna = ClassRegistry::init('Consegna');
		$prossimeConsegne = $Consegna->getProssimePerNumSettimane(4); // mi tengo "largo" perche' a seconda della periodicita' e delle tempistiche di apertura le date potrebbero essere lontane
		if(empty($prossimeConsegne)) {
			return null;
		}
		foreach($prossimeConsegne as $prossimaConsegna) {
			$t = $this->getTimestampApertura($prossimaConsegna['date']);
			if($t < time()) continue;
			return date('Y-m-d H:i', $t); 
		}
		ClassRegistry::init('LogEntry')->logError("", $this->getType().": impossibile calcolare la prossima data di apertura");
		throw new InternalErrorException("Impossibile calcolare la prossima data di apertura");
	}
	
	/**
	 * 
	 */
	private function getTimestampApertura($date) {
		$weekDay = $this->weekDayEngToIta( date('D', strtotime($date)) );
		$giornoApertura = Configure::read( strtolower( "{$this->getType()}.{$weekDay}.ml.giorno_apertura" ) );
		$orarioApertura = Configure::read( strtolower( "{$this->getType()}.{$weekDay}.ml.orario_apertura" ) );
		$timestampApertura = strtotime( date('Y-m-d', strtotime($date) + 24*60*60*$giornoApertura).' '.$orarioApertura.':00' );
		return $timestampApertura;
	}
	
	/**
	 * 
	 */
	private function getTimestampChiusura($date) {
		$weekDay = $this->weekDayEngToIta( date('D', strtotime($date)) );
		$giornoChiusura = Configure::read( strtolower( "{$this->getType()}.{$weekDay}.ml.giorno_chiusura" ) );
		$orarioChiusura = Configure::read( strtolower( "{$this->getType()}.{$weekDay}.ml.orario_chiusura" ) );
		$timestampChiusura = strtotime( date('Y-m-d', strtotime($date) + 24*60*60*$giornoChiusura).' '.$orarioChiusura.':00' );
		return $timestampChiusura;
	}
	
	/**
	 * 
	 */
	function getType() {
		return 'MLV';
	}
	
	/**
	 * 
	 */
	function getAgent() {
		return 'AR';
	}
	
	/**
	 * 
	 */
	function add($id_articolo, $qty) {
		$Articolo = ClassRegistry::init('Articolo');
		$articolo = $Articolo->getOneForMl($id_articolo);
		
		// verifica se l'articolo è ancora disponibile (nel mentre altri clienti potrebbero già averlo acquistato
		// completamente anche se risulta ancora acquistabile)
		// IMPORTANTE! DEVO ESEGUIRE I CONTROLLI SOLO SU add (e non se delete o remove), ALTRIMENTI NELLA PAGINA DI RIEPILOGO ORDINE
		// NON POTREI CORREGGERE LE QUANTITÀ SE NEL MENTRE LA DISPONIBILITÀ SI È ESAURITA!
		if($qty > $articolo['Articolo'][strtolower($this->getType())]['porzioni_disponibili']) {
			ClassRegistry::init('LogEntry')->logWarning("", $this->getType().": la quantita' richiesta per l'articolo non e' piu' disponibile", [
				'env' => $this->getType(),
				'qty' => $qty,
				'porzioni' => $articolo['Articolo'][strtolower($this->getType())]
			]);
			throw new BadRequestException(__("Siamo spiacenti. La quantità richiesta per l'articolo non è disponibile"));
		}
		return $this->addRecord($articolo, $qty);
	}
	
	/**
	 * 
	 */
	function subtract($id_articolo, $qty) {
		$Articolo = ClassRegistry::init('Articolo');
		$articolo = $Articolo->getOneForMl($id_articolo);
		$qty = abs($qty);
		$currQty = $this->getCurrentQty($id_articolo);
		if(abs($qty) > $currQty) {
			ClassRegistry::init('LogEntry')->logWarning("", $this->getType().": ".__("Non puoi rimuovere $qty elementi per l'articolo scelto, il carrello ne contiene solo $currQty"), [
				'env' => $this->getType(),
				'qty' => $qty,
				'currQty' => $currQty,
				'articolo' => $id_articolo
			]);
			throw new BadRequestException(__("Non puoi rimuovere $qty elementi per l'articolo scelto, il carrello ne contiene solo $currQty"));
		}
		$this->addRecord($articolo, -$qty);
	}
	
	/**
	 * 
	 */
	function remove($id_articolo) {
		$Articolo = ClassRegistry::init('Articolo');
		$articolo = $Articolo->getOneForMl($id_articolo);
		$currQty = $this->getCurrentQty($id_articolo);
		if($currQty == 0) {
			ClassRegistry::init('LogEntry')->logWarning("", $this->getType().": Non puoi rimuovere un articolo che non è presente nel tuo carrello", [
				'env' => $this->getType(),
				'articolo' => $id_articolo
			]);
			throw new BadRequestException(__("Non puoi rimuovere un articolo che non è presente nel tuo carrello"));
		}
		$this->subtract($id_articolo, $currQty);
	}
	
	/**
	 * 
	 */
	function getAllArticoliInQualunqueSpesaFutura() {
		$Cliente = ClassRegistry::init('Cliente');
		$res = $this->find('all', [
			'fields' => [
				'ID_ARTICOLO',
				'SUM(qty) AS qty'
			],
			'conditions' => [
				'DATA >' => date('Y-m-d', strtotime('Monday this week')),
				'ID_CLIENTE' => $Cliente->getClienteId(),
				'record_type' => $this->getType()
			]
		]);
		$ids = [];
		foreach($res as $r) {
			$currentQty = empty($r) ? 0 : $r[0]['qty']; 
			if($currentQty > 0) {
				$ids[] = $r[ucfirst(strtolower($this->getType()))]['ID_ARTICOLO'];
			}
		}
		
		return $ids;
	}
	
	/**
	 * mi dice se in una qualunque spesa futura c'e' l'articolo indicato
	 */
	function isInUnaQualunqueSpesaFutura($id_articolo) {
		$Cliente = ClassRegistry::init('Cliente');
		$res = $this->find('first', [
			'fields' => [
				'SUM(qty) AS qty'
			],
			'conditions' => [
				'DATA >' => date('Y-m-d', strtotime('Monday this week')),
				'ID_ARTICOLO' => $id_articolo,
				'ID_CLIENTE' => $Cliente->getClienteId(),
				'record_type' => $this->getType(),
				'is_finalized' => 0,
				'is_active' => 1,
			],
			'group' => ['DATA', 'ID_ARTICOLO', 'record_type'],
		]);
		$currentQty = empty($res) ? 0 : $res[0]['qty']; 
		return $currentQty ? true : false;
	}
	
	/**
	 * restituisce la quantita' corrente di un dato articolo
	 * La chiave che raggruppa i record per un determinato articolo e' la tupla:
	 * (data, id_articolo, id_cliente, tipo_record)
	 */
	public function getCurrentQty($id_articolo) {
		$Cliente = ClassRegistry::init('Cliente');
		$data = $this->getDataAcquisto();
		$db = $this->getDataSource();
		$res = $this->find('first', [
			'fields' => [
				'SUM(qty) AS qty'
			],
			'conditions' => [
				'DATA' => $data['date'],
				'ID_ARTICOLO' => $id_articolo,
				'ID_CLIENTE' => $Cliente->getClienteId(),
				'record_type' => $this->getType(),
				'is_finalized' => 0,
				'is_active' => 1,
			],
			'group' => ['DATA', 'ID_ARTICOLO', 'record_type'],
		]);
		$currentQty = empty($res) ? 0 : $res[0]['qty']; 
		return $currentQty;
	}
	
	/**
	 * 
	 */
	private function addRecord($articolo, $qty) {
		$data = $this->getDataAcquisto();
		$Cliente = ClassRegistry::init('Cliente');
		$record = [
			'is_finalized' => 0,
			'is_active' => 1,
			'record_type' => $this->getType(),
			'ml_id' => null,
			'qty' => $qty,
			'ID_CLIENTE' => $Cliente->getClienteId(),
			'ID_SCATOLA' => $data['scatola_id'],
			'ID_ARTICOLO' => $articolo['Articolo']['id'],
			'DATA' => $data['date'],
			'PRODOTTO' => $articolo['Articolo']['nomeCompleto'],
			'AZIENDA' => $articolo['Prodotto']['Fornitore']['FORNITORE'],
			'TECNICA' => 'nd',
			'CONFEZIONE' => $articolo['Articolo']['CONFEZIONE_TIPO'],
			'UDM' => $articolo['Articolo']['CONFERZIONE_UDM'], 
			'QUANTITA' => $qty*$articolo['Articolo']['quantita_minima'],
			'PREZZO' => $qty*$articolo['Articolo']['prezzo_porzione'],
			'PREZZO_VENDITA' => round((float)$articolo['Articolo']['prezzoVendita'] / ((100 + str_replace(",", ".", $articolo['Prodotto']['IVA'])) / 100), 2),			
			'IVA' => $articolo['Prodotto']['IVA'],
			'AGENTE' => $this->getAgent(),
			'shopping_session_id' => CakeSession::read('shopping_session_id')
		];
		if(! $this->save($record) ) {
			throw new InternalErrorException('Errore durante creazione record');
		}
		$Log = ClassRegistry::init('LogEntry');
		$Log->logInfo($this->getType(), "Articolo: {$articolo['Articolo']['id']}, quantita': {$qty}", [
			'type' => $this->getType(),
			'articolo' => $articolo['Articolo']['id'],
			'qty' => $qty,
			'record' => $record,
			'dettagli' => $articolo['Articolo'][strtolower($this->getType())]
		]); 
	}
	
	/**
	 * 
	 */
	public function getDataAcquisto() {
		$data = CakeSession::read("{$this->getType()}.data_acquisto"); // dato che in previsione posso acquistare potenzilamente per piu' date, leggo quella per cui sto acquistando
		if(empty($data)) {
			throw new BadRequestException("Nessuna data di acquisto disponibile");
		}
		// verifica che la data sia ancora valida ( = che nel frattempo il mercato non sia chiuso)		
		if( !in_array($data['date'], array_map(function($d){
			return $d['date'];
		}, $this->getDateAcquisto())) ) {
			ClassRegistry::init('LogEntry')->logWarning("", $this->getType().": Non e' possibile acquistare per la data specificata, si prega di aggiornare la pagina", [
				'data' => $data['date']
			]);
			throw new BadRequestException("Non e' possibile acquistare per la data specificata, si prega di aggiornare la pagina");
		}
		if(empty($data['scatola_id'])) { // puo' essere vuota per clienti senza operativita'
			ClassRegistry::init('LogEntry')->logWarning("", $this->getType().": Nessuna scatola disponibile per acquistare");
			throw new BadRequestException("Nessuna scatola disponibile per acquistare");
		}
		return $data;
	}
	
	/**
	 * 
	 */
	public function getDataAcquistoOrNull() {
		try {
			return $this->getDataAcquisto()['date'];
		}
		catch(Exception $e) {
			return null;
		}
	}
	
	/**
	 * 
	 */
	public function weekDayEngToIta($weekDay) {
		$weekDay = strtolower($weekDay);
		switch($weekDay) {
			case "mon":
				return "lun";
			case "tue":
				return "mar";
			case "wed":
				return "mer";
			case "thu":
				return "gio";
			case "fri":
				return "ven";
			case "sat":
				return "sab";
			case "sun":
				return "dom";
		}
		throw new InternalErroException("Unknown week day $weekDay");
	}
	
	/**
	 * 
	 */
	public function isTabVisible() {
		return true;
	}
	
}

 
