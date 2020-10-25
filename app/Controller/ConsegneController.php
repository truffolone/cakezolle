<?php

class ConsegneController extends AppController 
{
	public $name = 'Consegne';

	public $components = array('BikesquareMessaging.MessageFactory');

	public $uses = array(
		'Articolo', 
		'TipoSpesa', 
		'SpeseClienteZolla', 
		'ArticoloClienteZolla', 
		'SpesaZolla', 
		'AcquistoMercatoLiberoZolla', 
		'AcquistoArticoloFissoZolla', 
		'Contratto',
		'Recapito',
		'ArticoloVenduto',
		'ArticoloVendutoZolla',
		'ArticoloVendutoMlp',
		'ArticoloVendutoZollaMlp'
	);
	
	public $helpers = array('PrezzoArticoloHelper', 'ArticoloHelper');
	
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->layout = 'mangio';
		
		$this->Auth->allow('sospendi_tmp', 'dev');

	}
	
	public function afterFilter() {
		parent::afterFilter();
		
		$this->Session->write('extraScript', null); // reset dell'extra script per gestire il toast per le operazioni sulle consegne / spese
	}
	
	public function dev() {
		$this->layout = 'vue-test';
	}
	
	/**
	 */
	public function sidemenu_visible() {
		$this->Session->write('showSideMenu', true);
		$this->set('res', '');
        $this->set('_serialize', 'res');
	}
	
	/**
	 */
	public function sidemenu_hidden() {
		$this->Session->write('showSideMenu', false);
		$this->set('res', '');
        $this->set('_serialize', 'res');
	}
	
	/*
		visualizza le consegne delle prossime settimane modificabili
	*/
	public function index() {
		
		$this->redirect( array('action' => 'ordine') ); // redireziono per non modificare dovunque i link ...
	}
	
	/**
	 * 
	 */
	public function ordine() {
		$consegne = $this->_getConsegne($target='ordine');
		$this->set('consegne', $consegne);
		$this->set('subheaderBkg', 'mangio/zolle-bkg.jpg');
		
	}
	
	
	/**
	 * abbandona le modifiche provvisorie
	 */
	public function reset() {
		
		$cliente = $this->Session->read('cliente');
		$this->_updateClienteCheRiceveSpesaDaZolla( $cliente['Cliente']['ID_CLIENTE'] );
		$this->redirect( array('action' => 'index') );
		
	}
	
	/*
		apporta su zolla le modifiche necessarie
	*/
	function finalizza()
	{
		$this->loadModel('Ml');
		$records = $this->Ml->getOperazioniDaConfermare();
		if( sizeof($records) == 0 ) {
			$this->logWarning("", 'Finalizza - non ci sono modifiche da confermare');
			$this->Session->setFlash(__('Non ci sono modifiche da confermare'), 'flash_error');
			$this->redirect(array('action' => 'index'));
		}
		$this->logInfo("", 'Il cliente sta per finalizzare la spesa');
		$this->loadModel('Consegna');
		$this->loadModel('SpeseClienteZolla');
		$this->loadModel('Articolo');
		try {
			// questi sono gli effettivi record da scrivere su zolla 
			// (getOperazioniDaConfermare() non terrebbe conto delle quantita' gia' esistenti provenienti 
			// dai record gia' esistenti su zolla, quindi finirebbe con lo scrivere nuovi record con quantita' 
			// anche negative su zolla anziche' aggiornare i record corretti)
			$records = $this->Ml->getRecordAttivi($onlyMlvAndMlp=true); 
			
			$this->Consegna->checkValidataOperazioniDaConfermare();
			// verifica che le modifiche siano ancora eseguibili 
			//(se le spese sono già in lavorazione, cioè moltiplicate non si può procedere)
			$this->SpeseClienteZolla->checkSpeseNonAncoraInLavorazione();
			// verifica che la disponibilità degli articoli di mercato libero da finalizzare
			// sia sufficiente (nel mentre altri clienti potrebbero aver finalizzato il loro ordine)
			// NOTA: ai fini della disponibilita' mi interessano solo situazioni in cui ho aggiunto quantita' 
			// (se ho tolto NON DEVO controllare) e solo la quantita' che ho aggiunto (perche' se avevo gia' 
			// quell'articolo nel carrello con una certa quantita' questa e' gia' presa in considerazione nella 
			// disponibilita' perche' ho finalizzato e quindi salvato il venduto ad essa associato)
			$addedRecords = array_filter($records, function($r) {
				return $r['Ml']['qty'] > 0;
			});
			$articoliMap = $this->Articolo->getMap(
				array_unique(
					array_map(function($r) {
						return $r['Ml']['ID_ARTICOLO'];
					}, $addedRecords)
				)
			);
			foreach($addedRecords as $r) {
				$articoloId = $r['Ml']['ID_ARTICOLO'];
				$e = strtolower($r['Ml']['record_type']);
				if( $articoliMap[$articoloId]['Articolo'][$e]['porzioni_disponibili'] < 0 ) {
					$this->logWarning("AVAILABILITY", "La disponibilità di uno o più articoli non è più sufficiente", [
						'articolo' => $articoloId,
						'porzioni' => $articoliMap[$articoloId]['Articolo'][$e]
					]);
					throw new Exception(__("La disponibilità di uno o più articoli non è più sufficiente. Aggiorna il tuo ordine."));
				}
			}
		}
		catch(Exception $e) {
			$errorMsg = $e->getMessage();
			$this->logWarning("", "Finalizza: ". $errorMsg);
			$this->Session->setFlash($errorMsg, 'flash_error');
			$this->redirect(array('action' => 'index'));		
		}
		
		// tutto ok, procedi con le modifiche
		
		// prepara i record da salvare
		$dbRecords = [
			'ml' => [
				'save' => [],
				'delete' => []
			],
			'venduto' => [
				// per semplicita' scrivo (senza mai cancellare) record, mettendo quantita' a 0 se necessario. In questo
				// modo posso aggiornare in modo semplice il venduto nel caso di piu' finalizzazioni successive
				'mlv' => [], 
				'mlp' => [],
			]
		];
		
		$activeRecordsMap = $this->Ml->getRecordMap( $this->Ml->getRecordAttivi($onlyMlvAndMlp=true) ); // mi serve x determinare gli articoli da rimuovere su zolla
		$recordsMap = $this->Ml->getRecordMap($records);
		foreach($recordsMap as $key => $r) {
			$currQty = $activeRecordsMap[$key]['qty'];
			if($currQty == 0) { // record gia' presente su zolla ora completamente rimosso
				$dbRecords['ml']['delete'][] = $activeRecordsMap[$key]['ID']; // per definizione i record provvisori non hanno id remoto, lo leggo dal record attivo collegato
				$dbRecords['venduto'][ strtolower($r['record_type']) ][] = [
					'id' => "{$r['ID_ARTICOLO']}-{$r['DATA']}-{$r['ID_CLIENTE']}",
					'id_articolo' => $r['ID_ARTICOLO'],
					'data_consegna' => $r['DATA'],
					'id_cliente' => $r['ID_CLIENTE'],
					'numero_porzioni' => 0 // come gia' osservato non cancello mai record per semplicita'
				];
			}
			else {
				$dbRecords['ml']['save'][] = $r;
				$dbRecords['venduto'][ strtolower($r['record_type']) ][] = [
					'id' => "{$r['ID_ARTICOLO']}-{$r['DATA']}-{$r['ID_CLIENTE']}",
					'id_articolo' => $r['ID_ARTICOLO'],
					'data_consegna' => $r['DATA'],
					'id_cliente' => $r['ID_CLIENTE'],
					'numero_porzioni' => $currQty
				];
			}
		}
		
		$this->logInfo("", 'Il cliente sta per salvare la spesa', $dbRecords);
		
		$this->loadModel('Cliente');
		$this->loadModel('AcquistoMercatoLiberoZolla');
		
		// salva le modifiche
		$transactionOk = true;
		
		$dbo1 = $this->AcquistoMercatoLiberoZolla->getDataSource();
		$dbo2 = $this->Ml->getDataSource();
		$dbo3 = $this->ArticoloVenduto->getDataSource();
		$dbo4 = $this->ArticoloVendutoZolla->getDataSource();
		$dbo5 = $this->ArticoloVendutoMlp->getDataSource();
		$dbo6 = $this->ArticoloVendutoZollaMlp->getDataSource();

		$dbo1->begin();
		$dbo2->begin();
		$dbo3->begin();
		$dbo4->begin();
		$dbo5->begin();
		$dbo6->begin();
		
		try {
			// setta i record attivi come finalizzati (come info di log sul fatto che abbiamo finalizzato)
			$transactionOk &= $this->Ml->updateAll([
				'is_finalized' => 1,
				'is_active' => 0 // resetto gia' lo stato active al prossimo refresh dopo la finalizzazione ma per sicurezza lo faccio anche qui
			], [
				'is_active' => 1,
				'ID_CLIENTE' => $this->Cliente->getClienteId()
			]);
			// crea / aggiorna i record di ml
			if( !empty($dbRecords['ml']['save']) ) {
				$transactionOk &= $this->AcquistoMercatoLiberoZolla->saveAll($dbRecords['ml']['save']);
			}
			// cancella i record di ml
			if( !empty($dbRecords['ml']['delete']) ) {
				$transactionOk &= $this->AcquistoMercatoLiberoZolla->deleteAll(['id' => $dbRecords['ml']['delete']]);
			}
			// salva i venduti
			if( !empty($dbRecords['venduto']['mlv']) ) {
				$transactionOk &= $this->ArticoloVenduto->saveAll($dbRecords['venduto']['mlv']);
				$transactionOk &= $this->ArticoloVendutoZolla->saveAll($dbRecords['venduto']['mlv']);
			}
 			if( !empty($dbRecords['venduto']['mlp']) ) {
				$transactionOk &= $this->ArticoloVendutoMlp->saveAll($dbRecords['venduto']['mlp']);
				$transactionOk &= $this->ArticoloVendutoZollaMlp->saveAll($dbRecords['venduto']['mlp']);
			}
		}
		catch(Exception $e) {
			$transactionOk = false;
			CakeLog::write('error', $e->getMessage());
		}
		
		if($transactionOk) {
			$dbo1->commit();
			$dbo2->commit();
			$dbo3->commit();
			$dbo4->commit();
			$dbo5->commit();
			$dbo6->commit();
		}
		else {
			$dbo1->rollback();
			$dbo2->rollback();
			$dbo3->rollback();
			$dbo4->rollback();
			$dbo5->rollback();
			$dbo6->rollback();
		}
		
		if($transactionOk) {
			$this->logInfo("", 'Il cliente ha finalizzato correttamente la spesa');
			// genera la notifica mail da inviare con l'elenco delle modifiche eseguite
			$operazioniProvvisorie = $this->Session->read('operazioniProvvisorie');
			$view = new View($this, false);
			$dettagliModificheEseguite = $view->element('mangio/modale_ordine_provvisorio', array(
				'operazioniProvvisorie' => $operazioniProvvisorie, 
				'tipiSpese' => $this->Session->read('tipiSpese') ));
			
			// invia notifica mail ai clienti con l'elenco delle modifiche eseguite
			$id_contratto = $this->Session->read('contratto_id');
			$contratto = $this->Contratto->findById($id_contratto);
			
			$recapitiCliente = $this->Recapito->find('all', array(
				'conditions' => array(
					'TIPO' => 'email',
					'cliente_id' => $contratto['Cliente']['id']
				)
			));
			$recapitiClienteFatturazione = $this->Recapito->find('all', array(
				'conditions' => array(
					'TIPO' => 'email',
					'cliente_id' => $contratto['ClienteFatturazione']['id']
				)
			));
			
			// filtra i recapiti da usare in base al campo COMUNICAZIONI:
			// possibili vaolrizzazioni: A: Amministrativo - C: Commerciale - I: Informazioni o combinazioni di queste
			// Cerco tutti i recapiti di tipo A.  Se non ne trovo invio a tutti i recapiti disponibili
			
			$recapitiClientePerInvio = $this->ClientiUtil->getRecapitiPerTipo($recapitiCliente, 'A');
			$recapitiClienteFatturazionePerInvio = $this->ClientiUtil->getRecapitiPerTipo($recapitiClienteFatturazione, 'A');
			
			// invia le notifiche
			foreach($recapitiClientePerInvio as $r) {
				$this->_inviaNotificaModifiche($r, $contratto['Cliente']['displayName'], $dettagliModificheEseguite);
			}
			if( $contratto['Cliente']['id'] != $contratto['ClienteFatturazione']['id'] ) {
				foreach($recapitiClienteFatturazionePerInvio as $r) {
					$this->_inviaNotificaModifiche($r, $contratto['ClienteFatturazione']['displayName'], $dettagliModificheEseguite);
				}
			}
			
			// finalizzazione eseguita correttamente.  Ricarica il cliente che riceve la spesa da zolla
			$this->_updateClienteCheRiceveSpesaDaZolla($this->Cliente->getClienteId());
			
			$this->Session->setFlash(__("Modifiche eseguite correttamente"), 'flash_ok');	
		}
		else {
			$this->logError("", 'Si è verificato un errore in fase di finalizzazione');
			$this->Session->setFlash(__("Si è verificato un errore. Ripetere l'operazione"), 'flash_ok');	
		}
		
		$this->redirect( array('action' => 'index') );
	
	}
	
	/**
	 * 
	 */
	function _inviaNotificaModifiche($to, $nomeCliente, $dettagliModificheEseguite) {
		
		try {
			$cliente = $this->Session->read('cliente');
			$Email = new CakeEmail('mandrillapp');
			$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
			$Email->to($to);
			$Email->subject(__("Aggiornamento acquisti Mercato Libero"));
			$Email->setHeaders(array( // qui è 'ID_CLIENTE' e non 'id' perchè il cliente è letto da zolla
				'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['ID_CLIENTE'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
			));
			$Email->emailFormat('html');
			$Email->template('sommario_modifiche_area_riservata', 'default');
			$Email->viewVars(array(
				'nomeCliente' => $nomeCliente,
				'dettagliModificheEseguite' => $dettagliModificheEseguite
			));
			$Email->send();
		}
		catch(Exception $e) {
			ClassRegistry::init('LogEntry')->logWarning("", $e->getMessage());
		}
	}
	

	// funzionalita' temporanea legata alla chat
	function sospendi_tmp($zolla, $settimana) {
		$user = $this->Session->read('Auth.User');
		$cliente = $this->Cliente->findById($user['cliente_id']);
		
		$this->MessageFactory
			->forDefaultConversation($user['cliente_id'])
			->subject(__('Richiesta sospensione Zolla'))
			->tplBody('richiesta_sospensione_zolla', [
				'zolla' => $zolla,
				'settimana' => $settimana
			])
			->fromUser($user['id'])
			->toZolle()
			->sendWithEmail(true);
			
		$this->loadModel('Cliente');
		
		$this->MessageFactory
			->forDefaultConversation($user['cliente_id'])
			->subject(__('Conferma richiesta sospensione Zolla'))
			->tplBody('conferma_richiesta_sospensione_zolla', [
				'cliente' => $cliente['Cliente']['NOME'],
				'zolla' => $zolla,
				'settimana' => $settimana
			])
			->fromZolle()
			->toUserId($user['id'])
			->sendWithEmail(true);
			
		$this->redirect(['plugin' => 'messaging', 'controller' => 'messages', 'action' => 'chat', $user['cliente_id']]);
	}
}  
