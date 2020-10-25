<?php

App::uses('CakeSession', 'Model/Datasource');

class Articolo extends AppModel {

	public $actsAs = array('Containable');


	public $hasMany = array(
		'ArticoloPrezzo' => array(
			'className' => 'ArticoloPrezzo',
			'foreignKey' => 'articolo_id',
			'dependent' => true, //se cancello l'articolo cancelli i suoi prezzi
			'conditions' => array(
				'DATA_AL' => null //solo il prezzo corrente (eventualmente potrebbe essercene più di uno)
			),
			'order' => 'ArticoloPrezzo.id DESC' //così leggo sempre l'ultimo come prezzo valido in caso più di uno sia attivo (DATA_AL = null)
		),
		// NON gestisco come model associati disponibilità e venduto in quanto:
		// - sia disponibilità sia venduto dipendono dalla data di consegna (che devo ricavare)
		// - il venduto ha una chiave primaria multipla (che cake non è in grado di gestire)
		// per tali motivi uso un metodo ad hoc nel model che in modo semplice mi da la disponibilità via query diretta
	);
	
	public $belongsTo = array(
		'Prodotto' => array(
			'className' => 'Prodotto',
			'foreignKey' => 'prodotto_id'
		),
		/*'CategoriaWeb' => array(
			'className' => 'CategoriaWeb',
			'foreignKey' => 'CATEGORIA_WEB'
		)*/
	);
	
	/**
	 * Restituisce gli articoli effettivamente disponibili
	 * TODO: farlo direttamente in getAll() in modo che venga eseguito per ogni query in automatico anziche' invocarlo esplicitamente
	 */
	public function getArticoliEffDisponibili($env) {
		$Mlv = ClassRegistry::init('Mlv');
		$Mlp = ClassRegistry::init('Mlp');
		$dataAcquistoMlv = $Mlv->getDataAcquistoOrNull();
		$dataAcquistoMlp = $Mlp->getDataAcquistoOrNull();
		$articoliEffDisponibili = array();
		if( ($env == 'MLV' && empty($dataAcquistoMlv)) || ($env == 'MLP' && empty($dataAcquistoMlp)) ) { // ml chiuso
			$table = $env == 'MLV' ? 'articoli_disponibilita' : 'articoli_disponibilita_mlp';
			// ml chiuso, faccio vedere gli articoli che hanno una disponibilita' recente ( = che erano disponibili nelle scorse settimane)
			$min_date = date('Y-m-d', time() - 2*7*24*60*60); // uso come vincolo almeno 2 settimane fa per sicurezza
			$res = $this->query("SELECT DISTINCT id_articolo AS id FROM ".$table." WHERE data_consegna >= '".$min_date."'");
			foreach($res as $r) {
				$articoliEffDisponibili[] = $r[$table]['id'];
			}
		}
		else { // aperto
			// devo sempre calcolare gli articoli disponibili per mlp
			$resMlp = $this->query("SELECT 
						ad.id_articolo, 
						if(
							ad.max_porzioni_per_cliente is null, 
							ad.numero_porzioni, 
							least(ad.max_porzioni_per_cliente, ad.numero_porzioni)
						) as porzioni_disponibili, 
						SUM(av.numero_porzioni) as porzioni_vendute 
						FROM articoli_disponibilita_mlp AS ad 
						LEFT join articoli_venduto_mlp AS av 
						ON ad.data_consegna = av.data_consegna 
						AND ad.id_articolo = av.id_articolo 
						WHERE ad.data_consegna = '".$dataAcquistoMlp."' 
						AND ad.numero_porzioni> 0 
						GROUP BY ad.id_articolo 
						HAVING 
							porzioni_vendute IS NULL OR 
							porzioni_vendute < porzioni_disponibili");
		
			foreach($resMlp as $r) {
				$articoliEffDisponibili[] = $r['ad']['id_articolo'];
			}
	
			if($env == 'MLV') {
				
				// gli articoli di mlp visualizzabili solo sono quelli che, oltre alla query per il caso mlp sono stati
				// allocati anche per mlv
				$allocatiMlv = $this->query("SELECT 
					ad.id_articolo, 
					if(
						ad.max_porzioni_per_cliente is null, 
						ad.numero_porzioni, 
						least(ad.max_porzioni_per_cliente, ad.numero_porzioni)
					) as porzioni_disponibili
					FROM articoli_disponibilita AS ad 
					WHERE ad.data_consegna = '".$dataAcquistoMlv."'  
					GROUP BY ad.id_articolo");
				$allocatiMlv = array_map(function($r){
					return $r['ad']['id_articolo'];
				}, $allocatiMlv);
				$articoliEffDisponibili = array_intersect($articoliEffDisponibili, $allocatiMlv);
				// a questo punto ho tutti gli articoli MLP visualizzabili per l'ambiente corrente
				
				// in questo caso gli articoli disponibili sono sia quelli di Mlv sia quelli di Mlp
				// Questo perche' se un articolo non e' acquistabile come mlv ma lo e' come mlp viene
				// visualizzato in mlv per poter prenotare
				$resMlv = $this->query("SELECT 
					ad.id_articolo, 
					if(
						ad.max_porzioni_per_cliente is null, 
						ad.numero_porzioni, 
						least(ad.max_porzioni_per_cliente, ad.numero_porzioni)
					) as porzioni_disponibili, 
					SUM(av.numero_porzioni) as porzioni_vendute 
					FROM articoli_disponibilita AS ad 
					LEFT join articoli_venduto AS av 
					ON ad.data_consegna = av.data_consegna 
					AND ad.id_articolo = av.id_articolo 
					WHERE ad.data_consegna = '".$dataAcquistoMlv."' 
					AND ad.numero_porzioni> 0 
					GROUP BY ad.id_articolo 
					HAVING 
						porzioni_vendute IS NULL OR 
						porzioni_vendute <= porzioni_disponibili");
				// aggiungo a quelli gia' ottenuti da mlp
				foreach($resMlv as $r) {
					$articoliEffDisponibili[] = $r['ad']['id_articolo'];
				}
				
			}
		}
		return array_unique($articoliEffDisponibili);
	}
	
	/**
	 * 
	 */
	public function getOneForMl($id) {
		return $this->getOne($id, ['mustBeAvailableForMl' => true]);
	}
	
	/**
	 * 
	 */
	public function getOne($id, $opts=[]) {
		$res = $this->getAll([$id], $opts=[]);
		if(empty($res)) {
			ClassRegistry::init('LogEntry')->logError("", 'Articolo non trovato o non disponibile', [
				'id' => $id
			]);
			throw new NotFoundException(__('Articolo non trovato o non disponibile'));
		}
		$articolo = is_array($res) ? $res[0] : $res;
		if( $articolo['Articolo']['prezzoVendita'] === null ) {
			ClassRegistry::init('LogEntry')->logError("", 'Articolo non disponibile (senza prezzo)', [
				'id' => $id
			]);
			throw new NotFoundException(__('Articolo non disponibile (senza prezzo)'));
		}
		return $articolo;
	}

	/**
	 * restituisce un array associativo id_articolo -> articolo
	 */
	public function getMap($ids, $doNotCheckAvailability=false) {
		$res = $this->getAll($ids, $doNotCheckAvailability ? ['doNotCheckAvailability' => $doNotCheckAvailability] : []);
		$map = [];
		foreach($res as $r) {
			$map[ $r['Articolo']['id'] ] = $r;
		}
		return $map;
	}
	
	/**
	 * 
	 */
	public function getAll($ids, $opts=[]) {
		$env = CakeSession::read('env');
		$Ml = ClassRegistry::init($env == 'MLV' ? 'Mlv' : 'Mlp');
		try {
			$data = $Ml->getDataAcquisto();
		}
		catch(Exception $e) { // mercato libero chiuso o cliente senza operativita'
			$data = null;
		}
		$Cliente = ClassRegistry::init('Cliente');
		$giornoConsegna = $data ? $Ml->weekDayEngToIta(date('D', strtotime($data['date']))) : $Cliente->getGiornoConsegna();
		
		$extraCond = [];
		if(!empty($opts)) {
			if(isset($opts['doNotCheckAvailability']) && $opts['doNotCheckAvailability'] === true) {
				// lo uso quando devo tirare su gli articoli associati a tutti i record ml (che possono essere creati da fattoria x articoli non disponibili come ml)
				$extraCond = [];
			}
			elseif( isset($opts['mustBeAvailableForMl']) && $opts['mustBeAvailableForMl'] ) {
				$extraCond = [
					'OR' => array( //per poter essere visualizzabile dev'essere disponibile come AF o ML (almeno uno dei due)
						'Articolo.DISP_ML' => 1,
						'Articolo.DISP_AF' => 1
					)
				];
			}
		}
		else {
			$extraCond = []; // devo poter visualizzare l'articolo in ogni caso
		}
		
		$res = $this->find('all', [
			'conditions' => [
				'Articolo.id' => $ids,
				'Articolo.ABILITATO' => 1,
				'Articolo.DISP_'.strtoupper($giornoConsegna) => 1,
				
			] + $extraCond,
			'contain' => [
				'Prodotto' => [
					'Fornitore' => [
						'fields' => ['id', 'FORNITORE'],
					],
					'Sottocategoria' => [
						'fields' => ['id', 'SOTTOCATEGORIA', 'ABILITATO'],
						'CategoriaWeb'
					]
				], 
				'ArticoloPrezzo' => [
					'fields' => ['PREZZO_VENDITA']
				]
			]
		]);
		foreach($res as $key => $val) {
			$this->addDettagli($res[$key]);
		}	
		
		// aggiungi ad ogni articolo la disponibilita'
		$porzioniMlv = $this->getPorzioniDisponibili($ids, 'MLV');
		$porzioniMlp = $this->getPorzioniDisponibili($ids, 'MLP');
		
		foreach($res as $key => $val) {
			$id = $res[$key]['Articolo']['id'];
			
			$res[$key]['Articolo']['mlv']['porzioni_disponibili'] = $porzioniMlv[$id]['porzioni_disponibili'];
			$res[$key]['Articolo']['mlv']['porzioni_eff_disponibili'] = $porzioniMlv[$id]['porzioni_eff_disponibili'];
			$res[$key]['Articolo']['mlv']['porzioni_allocate'] = $porzioniMlv[$id]['porzioni_allocate'];
			$res[$key]['Articolo']['mlv']['porzioni_allocate_globali'] = $porzioniMlv[$id]['porzioni_allocate_globali'];
			
			$res[$key]['Articolo']['mlp']['porzioni_disponibili'] = $porzioniMlp[$id]['porzioni_disponibili'];
			$res[$key]['Articolo']['mlp']['porzioni_eff_disponibili'] = $porzioniMlp[$id]['porzioni_eff_disponibili'];
			$res[$key]['Articolo']['mlp']['porzioni_allocate'] = $porzioniMlp[$id]['porzioni_allocate'];
			$res[$key]['Articolo']['mlp']['porzioni_allocate_globali'] = $porzioniMlp[$id]['porzioni_allocate_globali'];
		}
		
		// determina la visualizzazione per ogni articolo
		$Mlv = ClassRegistry::init('Mlv');
		$Mlp = ClassRegistry::init('Mlp');
		$articoliInQualunqueMlv = $Mlv->getAllArticoliInQualunqueSpesaFutura();
		$articoliInQualunqueMlp = $Mlp->getAllArticoliInQualunqueSpesaFutura();
		foreach($res as $key => $val) {
			$res[$key]['Articolo']['mlv']['canShow'] = $this->canShow($res[$key], 'MLV', $articoliInQualunqueMlv);
			$res[$key]['Articolo']['mlv']['canShop'] = $this->canShop($res[$key], 'MLV', $res[$key]['Articolo']['mlv']['canShow']);
			$res[$key]['Articolo']['mlp']['canShow'] = $this->canShow($res[$key], 'MLP', $articoliInQualunqueMlp);
			$res[$key]['Articolo']['mlp']['canShop'] = $this->canShop($res[$key], 'MLP', $res[$key]['Articolo']['mlp']['canShow']);
			
			$showAs = null; // init, vuol dire che l'articolo non è visibile
			if($env == 'MLV') {
				if($res[$key]['Articolo']['mlv']['canShow']) {
					if($res[$key]['Articolo']['mlv']['porzioni_disponibili'] > 0) $showAs = 'MLV';
					else {
						// vai in fallback su MLP
						if($res[$key]['Articolo']['mlp']['porzioni_disponibili'] > 0) $showAs = 'MLP';
						else $showAs = 'MLV';
					}
				}
				else { // se (e solo se) era allocato per MLV vai in fallback su MLP (se possible)
					if( $res[$key]['Articolo']['mlv']['porzioni_allocate_globali'] > 0) {
						if($res[$key]['Articolo']['mlp']['canShow']) $showAs = 'MLP';
					}
				}
			}
			else { // MLP
				if($res[$key]['Articolo']['mlp']['canShow']) $showAs = 'MLP';
			}
			$res[$key]['Articolo']['showAs'] = $showAs;
		}
		
		// preserve original sorting of given ids
		$resMap = [];
		foreach($res as $r) {
			$resMap[ $r['Articolo']['id'] ] = $r;
		}
		$res = [];
		foreach($ids as $id) {
			$res[] = $resMap[$id];
		}
		
		return $res;
	}
	
	
	/**
	 * 
	 */
	private function canShow($articolo, $env, $articoliInQualunqueSpesaFutura) {
		$hide_if_sold_out = Configure::read('articoli.nascondi_esauriti');
		if(empty($hide_if_sold_out)) $hide_if_sold_out = false;
		
		$MlEnv = ClassRegistry::init(ucfirst(strtolower($env)));
		$isInSpesa = in_array($articolo['Articolo']['id'], $articoliInQualunqueSpesaFutura);
		$isMlEnvAperto = $MlEnv->getDataAcquistoOrNull() != null;
		$porzioni_allocate_globali = $articolo['Articolo'][strtolower($env)]['porzioni_allocate_globali'];
		$porzioni_disponibili = $articolo['Articolo'][strtolower($env)]['porzioni_disponibili'];
				
		$canShow = false;
		if($isMlEnvAperto) {
			if( $isInSpesa ) {
				// ho l'articolo nella mia spesa, devo cmq poterlo vedere in ogni caso
				$canShow = true;
			}
			else { // verifica se è disponibile
				if( $porzioni_allocate_globali > 0 ) { // allocato
					if($hide_if_sold_out) {
						$canShow = $porzioni_disponibili > 0;
					}
					else { // anche se esaurito lo faccio vedere
						$canShow = true;
					}
				}
				else { // non allocato, non posso vederlo
					$canShow = false;
				}
			}
		}
		else { // chiuso
			if( $isInSpesa ) {
				// ho l'articolo nella mia spesa, devo cmq poterlo vedere in ogni caso
				$canShow = true;
			}
			else {
				// posso vederlo solo se allocato per la data di acquisto o se MLP (per sbirciare)
				$canShow = true;//$porzioni_allocate_globali > 0 || $env == 'MLP';
			}
		}
		return $canShow;
	}
	
	/**
	 * 
	 */
	private function canShop($articolo, $env, $canShow) {
		$MlEnv = ClassRegistry::init(ucfirst(strtolower($env)));
		$isMlEnvAperto = $MlEnv->getDataAcquistoOrNull() != null;
		$porzioni_disponibili = $articolo['Articolo'][strtolower($env)]['porzioni_disponibili'];
		
		if( empty($articolo['Articolo']['quantita_minima']) ) return false;
		
		$canShop = false;
		if($isMlEnvAperto) {
			$canShop = $canShow && $porzioni_disponibili > 0;
		}
		else { // chiuso
			$canShop = false;
		}
		return $canShop;
	}

	/**
	 * 
	 */
	private function addDettagli(&$articolo) {
		// aggiungi il nome completo
		$articolo['Articolo']['nomeCompleto'] = $articolo['Prodotto']['NOME'];
		$articolo['Articolo']['prezzoVendita'] = $this->getArticoloPrezzoVendita($articolo); 
		
		// aggiungi i dettagli relativi al prezzo
		$prezzo_unitario = $this->getPrezzoUnitario($articolo);

		$articolo['Articolo']['udm'] = $prezzo_unitario['udm']; //setta la corretta unità di misura da visualizzare

		//CONFEZIONE_UDM (unità di misura principale UDM1): assume i valori 'pz' o 'kg'

		//prezzo porzione: corrisponde a prezzo articolo se CONFEZIONE_UDM = pz, frutto di un calcolo se CONFEZIONE_UDM = kg
		$confezione_udm = strtolower(trim($articolo['Articolo']['CONFERZIONE_UDM']));
		if($confezione_udm == 'pz') {
			// il prezzo dell'articolo è già il prezzo della confezione/porzione e si calcola prezzo/udm
			$articolo['Articolo']['prezzo_porzione'] = $this->getArticoloPrezzoVendita($articolo);
		}		
		else { // == 'kg'
			// il prezzo di vendita è riferito al Kg, ma l'articolo è venduto con una quantità non necessariamente
			// pari a 1, quindi il prezzo della porzione/confezione venduta deve essere calcolato
			$articolo['Articolo']['prezzo_porzione'] = $prezzo_unitario['prezzo'];
		}

		//prezzo per udm: è il prezzo dell'articolo se CONFEZIONE_UDM = 'kg', frutto di un calcolo se CONFEZIONE_UDM = 'pz'
		if($confezione_udm == 'pz') {
			$articolo['Articolo']['prezzo_udm'] = $prezzo_unitario['prezzo'];
		}		
		else { // == 'kg'
			$articolo['Articolo']['prezzo_udm'] = $this->getArticoloPrezzoVendita($articolo);
		}
		
		// porzione indivisibile
		if( strtolower(trim($articolo['Articolo']['CONFERZIONE_UDM'])) == 'kg' && strtolower(trim($articolo['Articolo']['UDM2'])) == 'pz' && !empty($articolo['Articolo']['RATIO_UDM1_UDM2']) ) {
			$articolo['Articolo']['porzioneIndivisibile'] = true;
		}	
		else {
			$articolo['Articolo']['porzioneIndivisibile'] = false;
		}
		
		$articolo['Articolo']['prefissoPrezzo'] = '';
		$articolo['Articolo']['suffissoPrezzo'] = '';
		if( $articolo['Articolo']['porzioneIndivisibile'] ) {
			$articolo['Articolo']['prefissoPrezzo'] = 'Importo indicativo: ';
			if($articolo['Articolo']['QUANTITA_PRECISIONE'] == 1) {
				$articolo['Articolo']['suffissoPrezzo'] = ' circa';
			}
		}
		
		// label articolo
		$articolo['Articolo']['labelArticolo'] = '';
		if($articolo['Articolo']['QUANTITA_PRECISIONE'] == 1) {
			$articolo['Articolo']['labelArticolo'] = 'Circa';
		}
		else if($articolo['Articolo']['QUANTITA_PRECISIONE'] == 2) {
			$articolo['Articolo']['labelArticolo'] = 'Minimo';
		}
		
		// confezione
		$articolo['Articolo']['confezione'] = '';
		if(!empty($articolo['Articolo']['CONFEZIONE_QUANTITA_UDM'])) {
			if($articolo['Articolo']['QUANTITA_PRECISIONE'] == 2) {
				$articolo['Articolo']['confezione'] .= 'minimo ';
			}
			$articolo['Articolo']['confezione'] = $articolo['Articolo']['CONFEZIONE_QUANTITA'].' '.$articolo['Articolo']['CONFEZIONE_QUANTITA_UDM'];
			if($articolo['Articolo']['QUANTITA_PRECISIONE'] == 1) {
				$articolo['Articolo']['confezione'] .= ' circa';
			}
		}
		
		// prezzo unitario
		$articolo['Articolo']['labelPrezzoUnitario'] = 'Prezzo al '.$articolo['Articolo']['udm'];
		if($articolo['Articolo']['QUANTITA_PRECISIONE'] == 2) {
			$articolo['Articolo']['prefissoPrezzoUnitario'] = 'meno di ';
		}
		else {
			$articolo['Articolo']['prefissoPrezzoUnitario'] = '';
		}
		$articolo['Articolo']['prezzoUnitario'] = $articolo['Articolo']['prezzo_udm'];
		
		// aggiungi la quantità minima (lo scatto) da usare per valorizzare i record ML e AF nelle operazioni di acquisto dell'articolo
		if( $articolo['Articolo']['CONFERZIONE_UDM'] == 'pz' ) {
			$articolo['Articolo']['quantita_minima'] = 1; 
		}
		else { // kg
			if( $articolo['Articolo']['CONFEZIONE_QUANTITA_UDM'] == 'g' ) {
				$articolo['Articolo']['quantita_minima'] = (float)$articolo['Articolo']['CONFEZIONE_QUANTITA']/1000;
			}
			else { // kg
				$articolo['Articolo']['quantita_minima'] = (float)$articolo['Articolo']['CONFEZIONE_QUANTITA'];
			}
		}
		
		// aggiungi gli url dell'immagine
		$imageName = $this->getSchedaImageArticoloOriginalName($articolo['Articolo']);
		if($imageName != 'no-image.jpg') {
			//$dettagli['croppedBoxImage'] = $this->_getCroppedBoxArticoloImageURL($articolo['Articolo']);
			//$dettagli['croppedSchedaImage'] = $this->_getCroppedSchedaArticoloImageURL($articolo['Articolo']);
			$articolo['Articolo']['resizedBoxImage'] = $this->getResizedBoxArticoloImageURL($articolo['Articolo']);
			$articolo['Articolo']['resizedSchedaImage'] = $this->getResizedSchedaArticoloImageURL($articolo['Articolo']);
		}
		else {
			//$dettagli['croppedBoxImage'] = 'areariservata/articoli/cropped/box/'.$imageName;
			//$dettagli['croppedSchedaImage'] = 'areariservata/articoli/cropped/scheda/'.$imageName;
			$articolo['Articolo']['resizedBoxImage'] = 'areariservata/articoli/resized/no-image-box.jpg';
			$articolo['Articolo']['resizedSchedaImage'] = 'areariservata/articoli/resized/no-image-scheda.jpg';
		}
		
	}
	
	/**
	 * 
	 */
	private function getPrezzoUnitario($articolo) {

		$confezione_udm = strtolower(trim($articolo['Articolo']['CONFERZIONE_UDM']));
		$confezione_quantita_udm = strtolower(trim($articolo['Articolo']['CONFEZIONE_QUANTITA_UDM']));
		$prezzoVendita = $this->getArticoloPrezzoVendita($articolo);
		$quantita = str_replace(",",".",trim($articolo['Articolo']['CONFEZIONE_QUANTITA'])); //espresso con separatore decimale ","
		$udm2 = strtolower(trim($articolo['Articolo']['UDM2']));
		$ratio_udm1_udm2 = 	strtolower(trim($articolo['Articolo']['RATIO_UDM1_UDM2']));	

		if(empty($confezione_udm)) {
			return array('prezzo' => '', 'udm' => $confezione_udm);
		}

		if( $confezione_udm == 'pz' ) {
			if( empty($quantita) && empty($confezione_quantita_udm) ) {
				//verdura a pezzi
				return array('prezzo' => $prezzoVendita, 'udm' => 'pz');
			}
			else if( empty($confezione_quantita_udm) || empty($quantita) ) {
				return array('prezzo' => '', 'udm' => $confezione_udm); //unknown
			}
			else {
				//occorre calcolare ed esprimere prezzo/udm
				if( $confezione_quantita_udm == 'g' ) {
					//esprimo in kg (fattore 1000)
					return array('prezzo' => (1000)*$prezzoVendita/$quantita, 'udm' => 'kg');
				}	
				else if( $confezione_quantita_udm == 'kg' ) {
					//esprimo in kg
					return array('prezzo' => $prezzoVendita/$quantita, 'udm' => 'kg');
				}
				else if( $confezione_quantita_udm == 'cl' ) {
					//esprimo in l (fattore 100)
					return array('prezzo' => (100)*$prezzoVendita/$quantita, 'udm' => 'l');
				}
				else if( $confezione_quantita_udm == 'ml' ) {
					//esprimo in ml (fattore 1000)
					return array('prezzo' => (1000)*$prezzoVendita/$quantita, 'udm' => 'l');
				}
				else if( $confezione_quantita_udm == 'l' ) {
					//esprimo in l
					return array('prezzo' => $prezzoVendita/$quantita, 'udm' => 'l');
				}
				else if( $confezione_quantita_udm == 'uova' ) {
					//esprimo in l
					return array('prezzo' => $prezzoVendita/$quantita, 'udm' => 'uovo');
				}
				else {
					//ignora (pz - da sistemare)
					return array('prezzo' => '', 'udm' => $confezione_udm);
				}
			}
		}
		else if( $confezione_udm == 'kg' ) {
			
			if( empty($confezione_quantita_udm) ) {
				if(empty($udm2)) return array('prezzo' => '', 'udm' => $confezione_udm); //unknown
				else {
					//porzione indivisibile
					if($ratio_udm1_udm2 == 0 || empty($ratio_udm1_udm2)) return array('prezzo' => '', 'udm' => $confezione_udm); //unknown
					else return array('prezzo' => $prezzoVendita/$ratio_udm1_udm2, 'udm' => $confezione_udm);
				}
			}

			if( $confezione_quantita_udm == 'g' ) {
				return array('prezzo' => $prezzoVendita*$quantita/1000, 'udm' => 'kg');
			}
			else if( $confezione_quantita_udm == 'kg' ) {
				return array('prezzo' => $prezzoVendita*$quantita, 'udm' => 'kg'); //mandatory!!! (1/quantita) !!! 
			}
			return array('prezzo' => '', 'udm' => $confezione_udm);
		}
		return array('prezzo' => '', 'udm' => $confezione_udm);
	}

	/**
	 * 
	 */
	private function getArticoloPrezzoVendita($articolo) {
		
		//vedi l'associazione nel model Articolo per i dettagli
		if(empty($articolo['ArticoloPrezzo'])) return null;
		if( $articolo['ArticoloPrezzo'][0]['PREZZO_VENDITA'] == null ) return null;
		//in alcuni casi su zolla il prezzo è valorizzato con la virgola invece che con il punto
		return str_replace(',', '.', $articolo['ArticoloPrezzo'][0]['PREZZO_VENDITA']);
		
	}
	
	private function getSchedaImageArticoloOriginalName($articolo)
	{
		$images = array();
		//verifica se esiste un'immagine principale per l'articolo
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/original/'.$articolo['id'].".*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		//verifica se esistono altre immagini per l'articolo con nome $id_articolo-n (es. 3432-0, 3432-1, ecc...)
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/original/'.$articolo['id']."-*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/original/'.$articolo['id']."_*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);

		if(sizeof($images) > 0) $image = basename($images[0]);
		else $image = 'no-image.jpg';

		return $image;
	}
	
	private function getCroppedBoxArticoloImageURL($articolo)
	{
		$images = array();
		//verifica se esiste un'immagine principale per l'articolo
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/cropped/box/'.$articolo['id'].".*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		//verifica se esistono altre immagini per l'articolo con nome $id_articolo-n (es. 3432-0, 3432-1, ecc...)
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/cropped/box/'.$articolo['id']."-*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/cropped/box/'.$articolo['id']."_*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);

		if(sizeof($images) > 0) $image = basename($images[0]);
		else $image = 'no-image.jpg';

		return 'areariservata/articoli/cropped/box/'.$image;
		
	}
	
	private function getCroppedSchedaArticoloImageURL($articolo)
	{
		$images = array();
		//verifica se esiste un'immagine principale per l'articolo
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/cropped/scheda/'.$articolo['id'].".*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		//verifica se esistono altre immagini per l'articolo con nome $id_articolo-n (es. 3432-0, 3432-1, ecc...)
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/cropped/scheda/'.$articolo['id']."-*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/cropped/scheda/'.$articolo['id']."_*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);

		if(sizeof($images) > 0) $image = basename($images[0]);
		else $image = 'no-image.jpg';

		return 'areariservata/articoli/cropped/scheda/'.$image;
	}
	
	private function getResizedBoxArticoloImageURL($articolo)
	{
		$images = array();
		//verifica se esiste un'immagine principale per l'articolo
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/resized/box/'.$articolo['id'].".*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		//verifica se esistono altre immagini per l'articolo con nome $id_articolo-n (es. 3432-0, 3432-1, ecc...)
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/resized/box/'.$articolo['id']."-*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/resized/box/'.$articolo['id']."_*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);

		if(sizeof($images) > 0) $image = basename($images[0]);
		else $image = 'no-image.jpg';

		return 'areariservata/articoli/resized/box/'.$image;
		
	}
	
	private function getResizedSchedaArticoloImageURL($articolo)
	{
		$images = array();
		//verifica se esiste un'immagine principale per l'articolo
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/resized/scheda/'.$articolo['id'].".*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		//verifica se esistono altre immagini per l'articolo con nome $id_articolo-n (es. 3432-0, 3432-1, ecc...)
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/resized/scheda/'.$articolo['id']."-*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);
		$imagesFound = glob(APP.'webroot/img/areariservata/articoli/resized/scheda/'.$articolo['id']."_*.*");
		if(!empty($imagesFound)) $images = array_merge($images, $imagesFound);

		if(sizeof($images) > 0) $image = basename($images[0]);
		else $image = 'no-image.jpg';

		return 'areariservata/articoli/resized/scheda/'.$image;
	}

	
	/**
	 * NOTA IMPORTANTE:  NON C'È VERSO DI LAVORARE CON TABELLE A CHIAVE MULTIPLA + QUERY DIRETTE (SE AGGIORNO IL VENDUTO E
	 * SUCCESSIVAMENTE CERCO DI RI-LEGGERE LA DISPONIBILITÀ AGGIORNATA LEGGO SEMPRE IL VENDUTO PRECEDENTE NON AGGIORNATO!
	 * ANCHE PROVANDO A FORZARE COMMIT, LOCK SULLE TABELLE, NON C'È NIENTE DA FARE!)
	 * 
	 * PERTANTO LAVORO SEMPRE ALLA CAKE CON TABELLE A CHIAVE SINGOLA.
	 * 
	 * NELLO SPECIFICO:
	 * 
	 * - articoli_disponibilita HA CHIAVE id_articolo-data_consegna
	 * - articoli_venduto HA CHIAVE id_articolo-data_consegna-id_cliente
	 * 
	 * Restituisce array associativo con la disponibilita' per ogni articolo specificato
	 */
	public function getPorzioniDisponibili($ids, $env) {
		
		$MlEnv = ClassRegistry::init($env == 'MLV' ? 'Mlv' : 'Mlp');
		$dataAcquisto = $MlEnv->getDataAcquistoOrNull();
	
		/* la disponibilità è data da:
		 * 
		 * MIN(porzioni_disponibili_globali, porzioni_disponibili_cliente) 
		 * 
		 */	
		// se il numero di porzioni allocate per singolo cliente è NULL il singolo cliente non ha limiti (quindi metto come disponibilità cliente quella globale)
		
		$dispModelName = $env == 'MLV' ? 'ArticoloDisponibilita' : 'ArticoloDisponibilitaMlp';
		$vendutoModelName = $env == 'MLV' ? 'ArticoloVenduto' : 'ArticoloVendutoMlp';
		$ArticoloDisponibilita = ClassRegistry::init($dispModelName);
		$ArticoloVenduto = ClassRegistry::init($vendutoModelName);
		$Cliente = ClassRegistry::init('Cliente');
		
		if(empty($dataAcquisto)) { // ML CHIUSO, MI INTERESSA SOLO L'ALLOCATO PER IL GIORNO A CUI IL CLIENTE ACQUISTAVA/PRENOTAVA DURANTE L'ULTIMO ML	
			$giornoConsegna = strtolower( $Cliente->getGiornoConsegna() );
			if($giornoConsegna == 'lun') {
				$engDay = 'Monday';
			}
			elseif($giornoConsegna == 'mar') {
				$engDay = 'Tuesday';
			}
			elseif($giornoConsegna == 'mer') {
				$engDay = 'Wednesday';
			}
			elseif($giornoConsegna == 'gio') {
				$engDay = 'Thursday';
			}
			elseif($giornoConsegna == 'ven') {
				$engDay = 'Friday';
			}
			elseif($giornoConsegna == 'sab') {
				$engDay = 'Saturday';
			}
			elseif($giornoConsegna == 'dom') {
				$engDay = 'Sunday';
			}
			
			$data = date('Y-m-d', strtotime("last $engDay"));

			// venduto
			$venduto = [];
			$venduto_cliente = [];
		}
		else { // ML aperto
			$data = $dataAcquisto;
			
			// venduto
			$res = $ArticoloVenduto->find('all', array(
				'conditions' => array(
					'id_articolo' => $ids,
					'data_consegna' => $data,
				)
			));
			// group by id_articolo
			$venduto = [];
			foreach($res as $r) {
				$r = $r[$vendutoModelName]; // shorten
				if(!isset($venduto[$r['id_articolo']])) {
					$venduto[ $r['id_articolo'] ] = 0;
				}
				// considera il venduto finalizzato di qualunque altro utente
				$venduto[ $r['id_articolo'] ] += (int) $r['numero_porzioni'];
			}
			
			// ottieni l'ordine provvisorio del cliente (si intende dove effettivamente ha acquistato qualcosa di nuovo,
			// cioe' dove la somma delle quantita'  > 0)
			$Ml = ClassRegistry::init('Ml');
			$res = $Ml->find('all', [
				'fields' => [
					'ID_ARTICOLO', 'SUM(qty) AS venduto'
				],
				'conditions' => [
					'DATA' => $data,
					'ID_CLIENTE' => $Cliente->getClienteId(),
					'record_type' => $env,
					'is_finalized' => 0,
					'is_active' => 1,
					'ml_id' => null
				],
				'group' => ['ID_ARTICOLO'],
				'having' => ['SUM(qty) > 0']
			]);
			$venduto_cliente = [];
			foreach($res as $r) {
				$venduto_cliente[ $r['Ml']['ID_ARTICOLO'] ] = (int) $r[0]['venduto'];
			}
		}
		
		$res = $ArticoloDisponibilita->find('all', [
			'conditions' => [
				'id_articolo' => $ids,
				'data_consegna' => $data
			]
		]);
		// genera array associativo (c'e' al piu' un record per ogni articolo)
		$disponibilita = [];
		foreach($res as $r) {
			$r = $r[$dispModelName]; // shorten
			$disponibilita[ $r['id_articolo'] ] = $r;
		}
		$porzioni = [];
		foreach($ids as $id) {
			
			if(isset($disponibilita[$id])) {
				$porzioni_allocate = $disponibilita[$id]['numero_porzioni'];
				$porzioni_allocate_cliente = empty($disponibilita[$id]['max_porzioni_per_cliente']) ? (int) $disponibilita[$id]['numero_porzioni'] : (int) $disponibilita[$id]['max_porzioni_per_cliente'];
			}
			else {
				$porzioni_allocate = 0;
				$porzioni_allocate_cliente = 0;
			}
			
			$porzioni_vendute = isset($venduto[$id]) ? (int) $venduto[$id] : 0; // ocnsidera gia' anche il finalizzato del cliente corrente
			// considera il venduto (non finalizzato) del cliente corrente.  Per convenzione NON considero il
			// carrello degli altri clienti (questo perchè se un altro cliente nel mentre aggiorna un record già finalizzato ma
			// poi non finalizza la modifica, quel venduto rimarrebbe "appeso" e nessuno potrebbe acquistare quella
			// disponibilità appesa)
			// Tale venduto è ottenibile in modo semplice dall'ordine provvisorio
			$porzioni_vendute_cliente = isset($venduto_cliente[$id]) ? (int) $venduto_cliente[$id] : 0;
			
			$porzioni_disponibili_globali = $porzioni_allocate - ($porzioni_vendute + $porzioni_vendute_cliente);
			$porzioni_disponibili_cliente = $porzioni_allocate_cliente - ($porzioni_vendute + $porzioni_vendute_cliente);
			$porzioni_disponibili = (int) min( array($porzioni_disponibili_globali, $porzioni_disponibili_cliente) );
			$porzioni_allocate = (int) min( array($porzioni_allocate, $porzioni_allocate_cliente) );
			
			$porzioni[$id] = [
				'porzioni_disponibili' => $porzioni_disponibili,
				// mi serve solo per info grafica al cliente se porzioni_disponibili e' negativo (puo' succedere se la disponibilita' viene ridotta per qualche motivo quando l'articolo e' gia' stato venduto, puo' succedere)
				'porzioni_eff_disponibili' => $porzioni_allocate - $porzioni_vendute,
				// 2018-02-19 restituisco anche le porzioni allocate (mi servono per la visualizzazione articoli a ML chiuso)
				'porzioni_allocate' => $porzioni_allocate,
				'porzioni_allocate_globali' => (int) $porzioni_allocate
			];
			
		}

		return $porzioni;
	
	}
	
}

 
