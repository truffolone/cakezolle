<?php

class ArticoliController extends AppController {

	var $name = "Articoli";

	public $uses = array(
		'Articolo', 
		'Prodotto', 
		'Sottocategoria', 
		'CategoriaWeb', 
		'TagCategoriaWeb', 
		'Fornitore', 
		'User',
		
		'ArticoloZolla',
		'ArticoloPrezzoZolla',
		'ProdottoZolla',
		'ArticoloVenduto'
	);
	
	public $helpers = array('Articolo');

	public $components = array('ImageUtil');


	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow(
			'aggiorna_definizione', // temporaneo, da rimuovere 
			'genera_immagini', // temporaneo, da rimuovere
			'aggiorna_rest'
		); 
		
		$this->layout = 'mangio';
	}
	
	/**
	 * metodo pubblico ad autenticazione rest da invocare via xml
	 */
	public function aggiorna_rest($id) {
		
		if( empty($this->request->query['pass']) || $this->request->query['pass'] != REST_PASS ) {
			$this->set('success', 'Not Authorized');
		}
		else {
		
			$articoloZolla = $this->ArticoloZolla->find('first', array(
				'conditions' => array('ArticoloZolla.ID_ARTICOLI' => $id),
				'contain' => array(
					'ArticoloPrezzoZolla',
					'ProdottoZolla'
				)
			));
			
			if( !empty($articoloZolla) ) {
				
				// crea l'articolo
				$articolo = array();
				// articolo
				$articolo['Articolo'] = $articoloZolla['ArticoloZolla'];
				$articolo['Articolo']['id'] = $articolo['Articolo']['ID_ARTICOLI'];
				$articolo['Articolo']['prodotto_id'] = $articolo['Articolo']['ID_PRODOTTO'];
				// prodotto
				$articolo['Prodotto'] = $articoloZolla['ProdottoZolla'];
				$articolo['Prodotto']['id'] = $articolo['Prodotto']['ID_PRODOTTO'];
				$articolo['Prodotto']['fornitore_id'] = $articolo['Prodotto']['ID_FORNITORE'];
				$articolo['Prodotto']['sottocategoria_id'] = $articolo['Prodotto']['ID_SOTTOCATEGORIA'];
				// prezzi
				$articolo['ArticoloPrezzo'] = $articoloZolla['ArticoloPrezzoZolla'];
				for($i=0;$i<sizeof($articolo['ArticoloPrezzo']);$i++) {
					$articolo['ArticoloPrezzo'][$i]['id'] = $articolo['ArticoloPrezzo'][$i]['ID_ARTICOLO_PREZZO'];
					$articolo['ArticoloPrezzo'][$i]['articolo_id'] = $articolo['ArticoloPrezzo'][$i]['ID_ARTICOLI'];
				
					$campi = array('PREZZO_ACQUISTO', 'PREZZO_ACQUISTO_UDM', 'PREZZO_VENDITA', 'PREZZO_CALCOLATO', 'PREZZO_ARTICOLO', 'PREZZO_TRASPORTO');
					foreach($campi as $c) {
						$articolo['ArticoloPrezzo'][$i][$c] = str_replace(',', '.', $articolo['ArticoloPrezzo'][$i][$c]);
					}
				}
				
				if( !empty($this->request->query['prezzo']) && $this->request->query['prezzo'] == 'no' ) {
					unset( $articolo['ArticoloPrezzo'] );
				}
				
				$success = $this->Articolo->saveAll($articolo);
				
			}
			else {
				$success = false;
			}
			
			$this->set('success', $success ? 1 : 0);
		}
		
	}
	
	
	/**
	 * in certe situazioni zolle ha necessità di aggiornare immediatamente gli articoli e le tabelle associate.
	 * Invoca il comando della shell di cake. Il risultato dell'operazione verrà scritto su file di log
	 */
	public function aggiorna_definizione() {
		$this->layout = 'default';
		
		if( !empty($this->request->query['prezzi']) && $this->request->query['prezzi'] == 'no' ) {
			shell_exec( APP. "Console/cake sync_db_locale_con_zolla sync_articoli_no_prezzi > /dev/null 2>/dev/null &");
		}
		else {
			shell_exec( APP. "Console/cake sync_db_locale_con_zolla sync_articoli > /dev/null 2>/dev/null &");
		}
		
	}
	
	/**
	 * per generare le immagini manualmente
	 */
	public function genera_immagini() {
		$this->layout = 'default';
		shell_exec( APP. "Console/cake genera_immagini > /dev/null 2>/dev/null &");
	}


	public function index() {		
		
		/*
		 * devo sempre filtrare qualunque query sugli articoli effettivamente disponibili (todo: farlo dentro
		 * a getAll() cosi' lo fa in automatico)
		 * */
		$articoliEffDisponibili = $this->Articolo->getArticoliEffDisponibili( $this->Session->read('env') );
		
		$searchKeyword = null;
		if(!empty($this->request->data)) {
			// eseguita ricerca
			$searchKeyword = $this->request->data['Prodotto']['DESCRIZIONE'];
			$this->Session->write('searchKeyword', $searchKeyword);
		}
		if($this->request->query('search')) {
			$searchKeyword = $this->request->query('search');
			$this->Session->write('searchKeyword', $searchKeyword);
		}
	
		$hide_if_sold_out = Configure::read('articoli.nascondi_esauriti');
		if(empty($hide_if_sold_out)) $hide_if_sold_out = false;
	
		$query = $this->request->params['named'];
		$this->loadModel('Cliente');
		$giornoConsegna = $this->Cliente->getGiornoConsegna();
		
		if($searchKeyword != null) {
			// ricerca globale
			$title = $searchKeyword;
			
			$articoli = $this->Articolo->find('all', array(
				'recursive' => -1,
				'fields' => ['Articolo.id'],
				'conditions' => array(
					'Articolo.id' => $articoliEffDisponibili,
					'Articolo.DISP_'.strtoupper($giornoConsegna) => 1,
					'Articolo.ABILITATO' => 1,
					'OR' => array( //per poter essere visualizzabile dev'essere disponibile come AF o ML (almeno uno dei due)
						'Articolo.DISP_ML' => 1,
						'Articolo.DISP_AF' => 1
					)
					// non ci sono altre condizioni da specificare, l'effettiva visualizzazione dipenderà dalla disponibilità
				),
				'joins' => array(
					array(
						'table' => 'prodotti',
						'alias' => 'p',
						'type' => 'INNER',
						'conditions' => array(
							'Articolo.prodotto_id = p.id',
							'p.NOME LIKE' => '%'.$searchKeyword.'%'
						)
					),
				)
			));
			$articoli = $this->Articolo->getAll(array_map(function($a){
				return $a['Articolo']['id'];
			}, $articoli));
		}
		else if( isset($query['categoria_web']) ) {
			
			// scrivi la categoria web selezionata in sessione (mi serve per visualizzare i tag di quella categoria)
			$this->Session->write('categoriaWebCorrente', $query['categoria_web']);
			
			// ottieni i dettagli della categoria web per il titolo
			$categoriaWeb = $this->CategoriaWeb->findById($query['categoria_web']);
			if(empty($categoriaWeb)) {
				throw new NotFoundException(__('Categoria non trovata'));
			}
			
			$this->set('subheaderBkg', 'areariservata/categorie/original/'.$categoriaWeb['CategoriaWeb']['id'].'.jpg');
			
			$title = $categoriaWeb['CategoriaWeb']['NOME'];
			
			// visualizza gli articoli appartenenti a questa categoria web
			$articoli = $this->Articolo->find('all', array(
				'recursive' => -1,
				'fields' => ['Articolo.id'],
				'conditions' => array(
					'Articolo.id' => $articoliEffDisponibili,
					'Articolo.DISP_'.strtoupper($giornoConsegna) => 1,
					'Articolo.ABILITATO' => 1,
					'OR' => array( //per poter essere visualizzabile dev'essere disponibile come AF o ML (almeno uno dei due)
						'Articolo.DISP_ML' => 1,
						'Articolo.DISP_AF' => 1
					)
					// non ci sono altre condizioni da specificare, l'effettiva visualizzazione dipenderà dalla disponibilità
				),
				'joins' => array(
					array(
						'table' => 'prodotti',
						'alias' => 'p',
						'type' => 'INNER',
						'conditions' => array(
							'Articolo.prodotto_id = p.id'
						)
					),
					array(
						'table' => 'sottocategorie',
						'alias' => 's',
						'type' => 'INNER',
						'conditions' => array(
							's.id = p.sottocategoria_id'
						)
					),
					array(
						'table' => 'categorie_web',
						'alias' => 'c',
						'type' => 'INNER',
						'conditions' => array(
							's.categoria_web_id = c.id',
							'c.id' => $query['categoria_web']
						)
					),
					array( // questo join serve unicamente per l'ordinamento richiesto da zolle
						'table' => 'fornitori',
						'alias' => 'f',
						'type' => 'INNER',
						'conditions' => array(
							'p.fornitore_id = f.id',
						)
					),
				),
				// da specifiche su Taiga (issue 16)
				'order' => array(
					'(s.PESO_WEB + Articolo.PESO_WEB) ASC',
					'f.FORNITORE ASC',
					'p.NOME ASC',
					'Articolo.CONFEZIONE_QUANTITA ASC',
					'Articolo.id ASC',
				),
				//'limit' => 50
			));
			
			$articoli = $this->Articolo->getAll(array_map(function($a){
				return $a['Articolo']['id'];
			}, $articoli));
		}
		elseif( isset($query['fornitore']) ) {
			
			// ottieni i dettagli del fornitore per il titolo
			$fornitore = $this->Fornitore->findById($query['fornitore']);
			if(empty($fornitore)) {
				throw new NotFoundException(__('Fornitore non trovato'));
			}
			$title = $fornitore['Fornitore']['FORNITORE'];
			
			// visualizza gli articoli appartenenti a questa categoria web
			$articoli = $this->Articolo->find('all', array(
				'recursive' => -1,
				'fields' => ['Articolo.id'],
				'conditions' => array(
					'Articolo.id' => $articoliEffDisponibili,
					'Articolo.DISP_'.strtoupper($giornoConsegna) => 1,
					'Articolo.ABILITATO' => 1,
					'OR' => array( //per poter essere visualizzabile dev'essere disponibile come AF o ML (almeno uno dei due)
						'Articolo.DISP_ML' => 1,
						'Articolo.DISP_AF' => 1
					)
					// non ci sono altre condizioni da specificare, l'effettiva visualizzazione dipenderà dalla disponibilità
				),
				'joins' => array(
					array(
						'table' => 'prodotti',
						'alias' => 'p',
						'type' => 'INNER',
						'conditions' => array(
							'Articolo.prodotto_id = p.id'
						)
					),
					array(
						'table' => 'fornitori',
						'alias' => 'f',
						'type' => 'INNER',
						'conditions' => array(
							'f.id = p.fornitore_id',
							'f.id' => $query['fornitore']
						)
					),
				),
				'order' => array(
					'p.sottocategoria_id', // TODO: migliorare è ordinare in ordine alfabetico di categoria web
					'p.NOME'
				),
				//'limit' => 50
			));
			$articoli = $this->Articolo->getAll(array_map(function($a){
				return $a['Articolo']['id'];
			}, $articoli));
			
		}
		elseif( isset($query['tag']) ) {
			
			$title = strtoupper($query['tag']);
			
			// usando un inner join sui prodotti va in max-execution time. Eseguo una prima query per trovare gli id degli
			// articoli
			
			// TODO: migliorare e ordinare in ordine alfabetico di categoria web
			$idsQueryRes = $this->Articolo->query(
				"SELECT Articolo.id AS id FROM articoli AS Articolo
				INNER JOIN prodotti AS Prodotto
				ON Prodotto.id = Articolo.prodotto_id
				WHERE Prodotto.TAGS LIKE '%;".$query['tag'].";%' AND
				Articolo.ABILITATO = 1 AND (Articolo.DISP_ML = 1 OR Articolo.DISP_AF = 1) AND 
				Articolo.DISP_".strtoupper($giornoConsegna)."=1
				ORDER BY Prodotto.sottocategoria_id, Prodotto.fornitore_id, Prodotto.NOME"
			);
			$ids = array();
			foreach($idsQueryRes as $r) $ids[] = $r['Articolo']['id'];
			$articoliEffDisponibili = $ids;
			
			// visualizza gli articoli appartenenti a questa categoria web
			$articoli = $this->Articolo->find('all', array(
				'recursive' => -1,
				'fields' => ['Articolo.id'],
				'conditions' => array(
					'Articolo.id' => $articoliEffDisponibili,
					'Articolo.DISP_'.strtoupper($giornoConsegna) => 1,
					'Articolo.ABILITATO' => 1,
					'OR' => array( //per poter essere visualizzabile dev'essere disponibile come AF o ML (almeno uno dei due)
						'Articolo.DISP_ML' => 1,
						'Articolo.DISP_AF' => 1
					)
					// non ci sono altre condizioni da specificare, l'effettiva visualizzazione dipenderà dalla disponibilità
				),
				'joins' => array( // usati solo per l'ordinamento
					array(
						'table' => 'prodotti',
						'alias' => 'p',
						'type' => 'INNER',
						'conditions' => array(
							'Articolo.prodotto_id = p.id'
						)
					)
				),
				'order' => array(
					'p.sottocategoria_id', // TODO: migliorare è ordinare in ordine alfabetico di categoria web
					'p.NOME'
				),
				//'limit' => 50
			));
			$articoli = $this->Articolo->getAll(array_map(function($a){
				return $a['Articolo']['id'];
			}, $articoli));
		}
		else {
			// carica tutti gli articoli (funziona in entrambi i casi ma si applica alla quick list)
			$title = __('Tutti gli articoli');
			
			$articoli = $this->Articolo->getAll($articoliEffDisponibili);
			// sort by prodotto.NOME
			usort($articoli, function ($a, $b) {
				if ($a['Prodotto']['NOME'] == $b['Prodotto']['NOME']) {
					return 0;
				}
				return ($a['Prodotto']['NOME'] < $b['Prodotto']['NOME']) ? -1 : 1;
			});
		}
		
		foreach($articoli as $index => $articolo) {
			if($articolo['Articolo']['showAs'] == null) { // non è possibile visualizzare l'articolo
				unset($articoli[$index]);
			}
		}
		// rimappa gli indici
		$articoli = array_values($articoli);
	
		$this->set('title', $title);
		$this->set('articoli', $articoli);
		$this->set('isMlAperto', $this->Session->read('MLV.data_consegna') != null);
		$this->set('isMlpAperto', $this->Session->read('MLP.data_consegna') != null);
	}
	
	/**
	 * 
	 */
	public function view($id) {

		$articolo = $this->Articolo->getOne($id);

		$showAs = $articolo['Articolo']['showAs'];
		if($showAs == null) {
			$this->Session->setFlash(__('Articolo non disponibile (può tornare disponibile in futuro)'), 'flash_error');
			$this->redirect(array('controller' => 'categorie_web', 'action' => 'index'));
		}
		
		if( $this->Session->read('env') == 'MLV' && strtolower($showAs) == 'mlp' ) {
			// in questo caso particolare (solo in questo) nascondo le categorie laterali per non generare confusione
			$this->Session->write('showCategorie', false);
		}
		// altrimenti è già settato a true via AppController::beforeFilter
		// scrivi in sessione la categoria web dell'articolo (mi serve per visualizzare i tag di quella categoria)
		$this->Session->write('categoriaWebCorrente', $articolo['Prodotto']['Sottocategoria']['categoria_web_id']);

		// ottieni gli articoli visti di recente
		$user = $this->Session->read('Auth.User');
		$user = $this->User->findById($user['id']);
		$visti_di_recente_ids = empty($user['User']['articoli_recenti']) ? array() : explode(';', $user['User']['articoli_recenti']);
		// se è presente anche l'articolo corrente rimuovilo dalla lista (verrà ri-aggiunto in ogni caso successivamente)
		$z = -1;
		for($j=0;$j<sizeof($visti_di_recente_ids);$j++) {
			if( $visti_di_recente_ids[$j] == $id ) {
				$z = $j;
				break;
			}
		}
		if($z != -1) {
			unset( $visti_di_recente_ids[$z] );
			$visti_di_recente_ids = array_values($visti_di_recente_ids);
		}
		// ottieni gli articoli visti di recente
		$visti_di_recente = $this->Articolo->getAll($visti_di_recente_ids);
		array_unshift($visti_di_recente_ids, $id); // aggiorna gli articoli visti di recente con quello corrente inserendolo in cima
		$visti_di_recente_ids = array_unique($visti_di_recente_ids); // rimuovi eventuali duplicati
		array_splice($visti_di_recente, 20); // limita il numero max di visti di recente a 20
		$this->User->save(array(
			'id' => $user['User']['id'],
			'articoli_recenti' => implode(';', $visti_di_recente_ids)
		)); 

		$this->set('articolo', $articolo);
		$this->set('visti_di_recente', $visti_di_recente);		
		// setta l'immagine di bkg per la categoria a cui l'articolo appartiene
		$this->set('subheaderBkg', 'areariservata/categorie/original/'.$articolo['Prodotto']['Sottocategoria']['categoria_web_id'].'.jpg');
	}
	
	/**
	 *  aggiungi un articolo come mercato libero
	 */
	public function aggiungi_ml($id, $qty=1) {
		
		$user = CakeSession::read('Auth.User');
		
		$isMlp = $this->request->query('mlp');	
		$MlEnv = ClassRegistry::init($isMlp ? 'Mlp' : 'Mlv');
		if($qty > 0) {
			$MlEnv->add($id, $qty);
		}
		else {
			$MlEnv->subtract($id, $qty);
		}
		
		$Ml = ClassRegistry::init('Ml');
		
		// aggiorna l'ordine provvisorio in sessione
		$this->Session->write('ordineProvvisorio', $Ml->getOperazioniDaConfermare());
		
		// scrivi in sessione l'ultima operazione
		$lastOp = $Ml->find('first', [
			'conditions' => [
				'ID_CLIENTE' => $user['cliente_id']
			],
			'order' => ['created DESC']
		]);
		$variazioneQta = $lastOp['Ml']['qty'];
		// leggi l'articolo aggiornato
		$articolo = $this->Articolo->getOneForMl($id);
		// (richiesta del 2020-07-03) se ho acquistato mlv e con l'ultima operazione ho esaurito completamente la quantita' disponibile
		// l'articolo verrebbe visualizzato immediatamente come mlp e acquistabile (prenotabile) come tale, ma cio'
		// potrebbe generare confusione: visualizzalo (solo ora) come sold out (al ri-caricamento della pagina sara'
		// invece normalmente prenotabile anche se in mlv)
		if(!$isMlp) {
			$articolo['Articolo']['showAs'] = 'MLV'; // forza sempre, in questo semplice modo ottengo l'effetto desiderato
		}
		
		if($variazioneQta > 1) {
			$ultimaOperazione = __($isMlp ? 
				'Hai %s confezioni di %s nella tua prenotazione' :
				'Hai %s confezioni di %s nel carrello',
				array($variazioneQta, $articolo['Articolo']['nomeCompleto']));
		}
		elseif($variazioneQta == 1) {
			$ultimaOperazione = __($isMlp ? 
				'Hai 1 confezione di %s nella tua prenotazione' :
				'Hai 1 confezione di %s nel carrello',
				array($articolo['Articolo']['nomeCompleto']));
		} 
		elseif($variazioneQta == -1) {
			$ultimaOperazione = __($isMlp ?
				'Hai rimosso 1 confezione di %s dalla tua prenotazione' :
				'Hai rimosso 1 confezione di %s dal tuo ordine',
				array($articolo['Articolo']['nomeCompleto']));
		}
		elseif($variazioneQta < -1) {
			$ultimaOperazione = __($isMlp ?
				'Hai rimosso %s confezioni di %s dalla tua prenotazione' :
				'Hai rimosso %s confezioni di %s dal tuo ordine',
				array(-$variazioneQta, $articolo['Articolo']['nomeCompleto']));
		}
		else {
			$ultimaOperazione = __($isMlp ? 
				'Non ci sono modifiche alla tua prenotazione' : 'Non ci sono modifiche al tuo ordine');
		}
		$this->Session->write('ultimaOperazione', $ultimaOperazione);
		
		// ======================================================================================
		// Gestione disponibilità
		// aggiorna la disponibilità per l'articolo (per aggiornarla in visualizzazione via ajax)
		$disp = $this->Articolo->getPorzioniDisponibili([$id], $isMlp ? 'MLP' : 'MLV');
		$disp = $disp[$id];
		$this->logInfo("", 'Nuova disponibilità articolo', [
			'id' => $id,
			'disp' => $disp
		]);
		$articolo['Articolo'][$isMlp ? 'mlp' : 'mlv']['porzioni_disponibili'] = $disp['porzioni_disponibili'];
		
		// ======================================================================================
		
		// aggiungi ai dettagli il risultato e un'info extra (che serve solo se aggiungo ml nella scheda articolo)
		$qtaArtCorr = $MlEnv->getCurrentQty($id);
		$strConfCorr = ($qtaArtCorr == 1) ? 'confezione' : 'confezioni';
		
		$view = new View($this, false);
	
		$this->set('res', array_merge( 
			array(
				'success' => true, 
				'confezioni_articolo_corrente' => $qtaArtCorr,
				'str_confezioni_articolo_corrente' => $view->element('mangio/confezioni_articolo_corrente', ['articolo' => $articolo]),
				// 2017-03-20: refresh della disponibilità dell'articolo scelto
				'contentHTML' => array(
					// refresh nel caso il chiamante sia box articolo
					/*'box-articolo-shopping-data-'.$id => $view->element('mangio/articolo/box_articolo_shopping_block', array(
						'articolo' => $articolo
					)),*/
					'box-articolo-'.$id => $view->element('mangio/box_articolo', array(
						'articolo' => $articolo
					)),
					// refresh nel caso il chiamante sia scheda articolo
					/*'scheda-articolo-shopping-data-'.$id => $view->element('mangio/articolo/scheda_articolo_shopping_block', array(
						'articolo' => $articolo
					))*/
					'scheda-articolo-'.$id => $view->element('mangio/scheda_articolo', array(
						'articolo' => $articolo
					)),
				)
			), 
			$this->_aggiornaDettagli()
		) );
        $this->set('_serialize', 'res');
		
	}
	
	/**
	 * rimuove l'articolo di mercato libero
	 */
	public function rimuovi_ml($id_articolo) {
		
		$isMlp = $this->request->query('mlp');	
		$MlEnv = ClassRegistry::init($isMlp ? 'Mlp' : 'Mlv');
		$MlEnv->remove($id_articolo);
		
		$qta_corrente = $MlEnv->getCurrentQty($id_articolo);

		// aggiorna l'ordine provvisorio in sessione
		$Ml = ClassRegistry::init('Ml');
		$this->Session->write('ordineProvvisorio', $Ml->getOperazioniDaConfermare());
		$articolo = $this->Articolo->getOneForMl($id_articolo);
		// salva l'ultima operazione
		if( $qta_corrente > 1 ) {
			$ultimaOperazione = __($isMlp ? 
				'Hai rimosso %s confezioni di %s dalla tua prenotazione' : 
				'Hai rimosso %s confezioni di %s dal tuo ordine', 
				array($qta_corrente, $articolo['Articolo']['nomeCompleto']));
		}
		else {
			$ultimaOperazione = __($isMlp ?
				'Hai rimosso 1 confezione di %s dalla tua prenotazione' :
				'Hai rimosso 1 confezione di %s dal tuo ordine', 
				array($articolo['Articolo']['nomeCompleto']));
		}
		$this->Session->write('ultimaOperazione', $ultimaOperazione);

		
		// operazione avvenuta correttamente. Ottieni i dettagli aggiornati
		// 2017-03-22: non c'è bisogno di ottenere la disponibilità aggiornata per la visualizzazione perchè cancellando
		// l'articolo non la vedo ...
		$this->set('res', array_merge( array('success' => true), $this->_aggiornaDettagli() ) );
        $this->set('_serialize', 'res');
	}

} 

