<?php

class AutorizzazioniRidController extends AppController {

	var $name = "AutorizzazioniRid";

	public $uses = array('AutorizzazioneRid', 'Cliente', 'User');
	
	public function beforeFilter() {
		
		parent::beforeFilter();
		
		// metodi pubblici per i clienti
		$this->Auth->allow(
			'contratto', 
			'info_contratto', 
			'stampa',
			'add_rest'
		);
		
	}
	
	/**
	 * 
	 */
	public function registra_attivazione($id) {
		
		$rid = $this->AutorizzazioneRid->findById($id);
		
		if(empty($rid)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		if( !empty($rid['AutorizzazioneRid']['rid_activated']) ) {
			$this->Session->setFlash('Contratto già attivo', 'flash_ok');
			$this->redirect( $this->referer() );
		}
		
		if( $this->AutorizzazioneRid->save(array(
			'AutorizzazioneRid' => array(
				'id' => $id,
				'rid_activated' => date('Y-m-d H:i:s'),
			)
		)) ) {
			
			// rid registrato -> attiva automaticamente il metodo di pagamento
			$cliente_id = $rid['AutorizzazioneRid']['cliente_id'];
			$this->requestAction('/clienti/attiva_metodo_pagamento/'.$cliente_id.'/'.RID.'/'.$id);
			// rid abilitato -> disattiva ogni altro metodo di pagamento esistente
			$this->Cliente->disattivaAltriMetodiPagamento($cliente_id, [
				'rid_id' => $id,
			]);
			
			$this->Session->setFlash('Autorizzazione RID aggiornata correttamente');
		}
		else {
			$this->Session->setFlash('Errore salvataggio.', 'flash_error');
		}
	
		$this->redirect( $this->referer() );
		
	}
	
	/**
	 * 
	 */
	public function invia_mail_attivazione($id) {
		
		$rid = $this->AutorizzazioneRid->findById($id);
		
		if(empty($rid)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		if( !empty($rid['AutorizzazioneRid']['rid_activated']) ) {
			$this->Session->setFlash('Contratto già attivo', 'flash_ok');
			$this->redirect( $this->referer() );
		}
		
		// ottieni i recapiti a cui inviare il messaggio
		$cliente = $this->AutorizzazioneRid->Cliente->findById($rid['AutorizzazioneRid']['cliente_id']);
		$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'A');
		foreach($recapiti as $recapito) {
			// invia mail di benvenuto
			$Email = new CakeEmail('mandrillapp');
			$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
			$Email->to($recapito);
			$Email->subject(__("Le Zolle - pagamento con RID"));
			$Email->emailFormat('html');
			$Email->setHeaders(array(
				'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['id'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
			));
			$Email->template('contratto_rid', 'default');
			$Email->viewVars(array(
				'nomeCliente' => $cliente['Cliente']['displayName'],
				'urlContratto' => Router::url(array('controller' => 'autorizzazioni_rid', 'action' => 'contratto', $rid['AutorizzazioneRid']['id_contratto_rid']), true)
			));
			if(INVIA_MAIL) $Email->send();
		}
		
		$this->Session->setFlash('Email di attivazione inviata', 'flash_ok');
		$this->redirect( $this->referer() );
		
	}
	
	/**
	 * 
	 */
	public function invia_mail_sollecito($id) {
		
		$rid = $this->AutorizzazioneRid->findById($id);
		
		if(empty($rid)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		if( !empty($rid['AutorizzazioneRid']['rid_activated']) ) {
			$this->Session->setFlash('Contratto già attivo', 'flash_ok');
			$this->redirect( $this->referer() );
		}
		
		// ottieni i recapiti a cui inviare il messaggio
		$cliente = $this->AutorizzazioneRid->Cliente->findById($rid['AutorizzazioneRid']['cliente_id']);
		$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'A');
		foreach($recapiti as $recapito) {
			// invia mail di benvenuto
			$Email = new CakeEmail('mandrillapp');
			$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
			$Email->to($recapito);
			$Email->subject(__("Le Zolle - pagamento con RID"));
			$Email->emailFormat('html');
			$Email->setHeaders(array(
				'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['id'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
			));
			$Email->template('sollecito_attivazione_rid', 'default');
			$Email->viewVars(array(
				'nomeCliente' => $cliente['Cliente']['displayName'],
				'urlContratto' => Router::url(array('controller' => 'autorizzazioni_rid', 'action' => 'contratto', $id_contratto), true)
			));
			if(INVIA_MAIL) $Email->send();
		}
		
		$this->Session->setFlash('Email di attivazione inviata', 'flash_ok');
		$this->redirect( $this->referer() );
		
	}
	
	/**
	 * metodo pubblico ad autenticazione rest invocato via xml
	 */
	public function add_rest($cliente_id) {
		
		if( empty($this->request->query['pass']) || $this->request->query['pass'] != REST_PASS ) {
			$this->set('success', 'Not Authorized');
		}
		else {
			
			// auto login perchè faccio una requestAction
			$user = $this->User->findById(1);
			$this->Auth->login($user['User']);
			
			$res = $this->requestAction('/autorizzazioni_rid/add/'.$cliente_id);
			$this->set('success', $res ? 1 : 0);
		}
	}
	
	/**
	 * 
	 */
	public function add($cliente_id) {
		
		$requested = false;
		if (!empty($this->request->params['requested'])) {
            $requested = true;
        }
		
		// crea il contratto
		$cliente = array();
		$cliente['Cliente']['id'] = $cliente_id;
		
		$id_contratto = "";
		$id_already_used = true;
		do
		{
			$id_contratto = uniqid();
			$tmpContratto = $this->AutorizzazioneRid->find('all', array('conditions' => array('id_contratto_rid' => $id_contratto)));
			if(empty($tmpContratto)) $id_already_used = false;
		}
		while($id_already_used);

		$autorizzazione = [
			'AutorizzazioneRid' => [
				'cliente_id' => $cliente_id,
				'id_contratto_rid' => $id_contratto,
				'rid_sent' => date('Y-m-d H:i:s')
			]
		];

		if( $this->AutorizzazioneRid->save($autorizzazione) ) {
			
			// ottieni i recapiti a cui inviare il messaggio
			$cliente = $this->Cliente->findById($cliente_id);
			$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'A');
			foreach($recapiti as $recapito) {
				// invia mail di benvenuto
				$Email = new CakeEmail('mandrillapp');
				$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
				$Email->to($recapito);
				$Email->subject(__("Le Zolle - pagamento con RID"));
				$Email->emailFormat('html');
				$Email->setHeaders(array(
					'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['id'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
				));
				$Email->template('contratto_rid', 'default');
				// forza https sull'url (lo faccio in modo custom dato che non sembra prendere il protocollo in automatico)
				$urlContratto = Router::url(array('controller' => 'autorizzazioni_rid', 'action' => 'contratto', $id_contratto), true);
				if(strpos($urlContratto, 'https') === false) { // http
					$urlContratto = str_replace('http://', 'https://', $urlContratto);
				}
				$Email->viewVars(array(
					'nomeCliente' => $cliente['Cliente']['displayName'],
					'urlContratto' => $urlContratto
				));
				if(INVIA_MAIL) $Email->send();
			}
			
			if($requested) {
				return true;
			}
			else {
				$this->Session->setFlash('Rid aggiunto correttamente', 'flash_ok');
				$this->redirect( $this->referer() );
			}
			
		}
		else {
			if($requested) {
				return false;
			}
			else {
				$this->Session->setFlash('Si è verificato un errore', 'flash_error');
				$this->redirect( $this->referer() );
			}
		}
				
	}
	
	/**
	 * 
	 */
	public function index($tipo=null) {
		if(!isset($tipo)) $tipo = RID_TUTTI;
		$this->set('tipo', $tipo);
	}
	
	/**
	 * 
	 */
	public function datatables_processing() {
		
		$this->RequestHandler->setContent('json', 'application/json');

		/* Array of database columns which should be read and sent back to DataTables. Use a space where
	  	 * you want to insert a non-database field (for example a counter or static image)
	 	 */
	 	 
	 	// anche se ho creato un virtualField non posso usarlo perchè non viene impostato nelle clausole order by, quindi devo usare concat()
		$aColumns = array(
			'AutorizzazioneRid.id', 
			'CONCAT(Cliente.COGNOME, " ", Cliente.NOME)',
			'CONCAT(codice_paese,check_digit,cin,abi,cab,conto_corrente)', // senza AS altrimenti search e sorting non funzionano!
			'rid_sent',
			'rid_activated',
			'Cliente.id', 
		);

		/* 
	 	 * Paging
	 	 */
		$sLimit = "";
		$sOffset = "";
		if ( isset( $this->request->query['iDisplayStart'] ) && $this->request->query['iDisplayLength'] != '-1' )
		{
			$sLimit = $this->request->query['iDisplayLength'];
			$sOffset = $this->request->query['iDisplayStart'];
		}

		/*
	 	 * Ordering
		 */
		$sOrder = array();
		if ( isset( $this->request->query['iSortCol_0'] ) )
		{
			for ( $i=0 ; $i<intval( $this->request->query['iSortingCols'] ) ; $i++ )
			{
				if ( $this->request->query[ 'bSortable_'.intval($this->request->query['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder[] = $aColumns[ intval( $this->request->query['iSortCol_'.$i] ) ].' '.($this->request->query['sSortDir_'.$i]==='asc' ? 'ASC' : 'DESC');
				}
			}
		}
	
	
		/* 
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		$sWhere['OR'] = array();
		if ( isset($this->request->query['sSearch']) && $this->request->query['sSearch'] != "" )
		{
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere['OR'][$aColumns[$i].' LIKE'] = "%".( $this->request->query['sSearch'] )."%";
			}
		}
	
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($this->request->query['bSearchable_'.$i]) && $this->request->query['bSearchable_'.$i] == "true" && $this->request->query['sSearch_'.$i] != '' )
			{
				$sWhere[$aColumns[$i].' LIKE'] = "%".($this->request->query['sSearch_'.$i])."%";
			}
		}
		
		$options = array();
		
		if(!empty($sLimit)) $options['limit'] = $sLimit;
		if(!empty($sOffset)) $options['offset'] = $sOffset;

		$options['contain'] = array(
			'Cliente' => array(
				'fields' => array('id', 'NOME', 'COGNOME') // i virtual fields non funzionano con contain ...
			),
		);

		if(!empty($sWhere)) $options['conditions'] = $sWhere;
		if(!empty($sOrder)) $options['order'] = $sOrder;

		$options['fields'] = $aColumns;

		// condizioni aggiuntive
		$t = $this->request->query['tipo']; 
		if( $t == RID_TUTTI ) {
			// nessuna condizione
		}
		elseif( $t == RID_NON_ATTIVI ) {
			$options['conditions']['rid_activated'] = NULL;
		}
	
		$res = $this->AutorizzazioneRid->find('all', $options);	
		
		unset($options['limit']);
		unset($options['offset']);
		$iFilteredTotal = $this->AutorizzazioneRid->find('count', $options);

		/*
		 * Output
		 */
		$totWhere = array();

		$output = array(
			"sEcho" => isset($this->request->query['sEcho']) ? intval($this->request->query['sEcho']) : 1,
			"iTotalRecords" => $this->AutorizzazioneRid->find('count'),
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
	
		foreach($res as $row) {
			
			$rid = $row['AutorizzazioneRid'];
			
			$r["DT_RowId"] = $row['AutorizzazioneRid']["id"];
			$r["id"] = $row['AutorizzazioneRid']["id"];
			$r["iban"] = $row[0]['CONCAT(codice_paese,check_digit,cin,abi,cab,conto_corrente)'];
			$r["rid_sent"] = $row["AutorizzazioneRid"]['rid_sent'];
			$r["rid_activated"] = $row["AutorizzazioneRid"]['rid_activated'];
			$r["cliente_nome"] = '<a href="'.Router::url(array('controller' => 'clienti', 'action' => 'view', $row['Cliente']["id"])).'">'.$row['Cliente']["COGNOME"].' '.$row['Cliente']["NOME"].'</a>';
			
			$r['actions'] = '<a title="visualizza rid" href="'.Router::url(array('controller' => 'autorizzazioni_rid', 'action' => 'edit', $row['AutorizzazioneRid']["id"])).'" class="btn btn-xs btn-info">
				<i class="ace-icon fa fa-info-circle bigger-120"></i>
			</a>';

			$output['aaData'][] = $r;
		}

		
		$this->set('res', $output);
		$this->set('_serialize', 'res');
		
	}
	
	/**
	 * 
	 */
	public function contratto($id_contratto) {
	
		$this->layout = 'mangio';	
		
		// ottieni il contratto
		$rid = $this->AutorizzazioneRid->find('first', array(
			'conditions' => array(
				'AutorizzazioneRid.id_contratto_rid' => $id_contratto,
			),
			'contain' => array(
				'Cliente' => array(
					'Recapito'
				)
			)
		));
		
		if(empty($rid)) {
			throw new NotFoundException('Contratto non presente in archivio');
		}
		// se l'autorizzazione è stata disattivata non puoi procedere oltre
		if(!empty($rid['AutorizzazioneRid']['data_disattivazione'])) {
			throw new BadRequestException('Il contratto non è più disponibile');
		}

		//se il contratto e' gia' stato compilato non procedere oltre	
		if( !empty($rid['AutorizzazioneRid']['rid_activated']) ) {
			$this->redirect(array('action' => 'info_contratto', $id_contratto));
		}
		
		$this->set('rid', $rid);
		

		if( !empty($this->request->data) ) {
			
			$this->Session->write('contratto_rid_accettato', true);
			
			if( $this->AutorizzazioneRid->save($this->request->data) ) {
				$this->Session->setFlash('Autorizzazione RID memorizzata correttamente');
				$this->redirect(array('action' => 'info_contratto', $id_contratto));
			}
			else {
				$this->Session->setFlash('Errore salvataggio. Verificare i dati inseriti per procedere', 'flash_error');
			}
			
			return;
			
		}
		
		$this->request->data = $rid;

	}
	
	/**
	 * 
	 */
	public function info_contratto($id_contratto) {
		$this->layout = 'mangio';

		$rid = $this->AutorizzazioneRid->find('first', array(
			'conditions' => array(
				'id_contratto_rid' => $id_contratto
			),
			'contain' => array(
				'Cliente'
			)
		));
		if(empty($rid)) {
			throw new NotFoundException('Contratto non presente in archivio');
		}
		
		
		$this->set('rid', $rid);
	}
	
	/**
	 * 
	 */
	public function stampa($id_contratto) {
		$this->layout = 'stampa';

		$rid = $this->AutorizzazioneRid->find('first', array(
			'conditions' => array(
				'id_contratto_rid' => $id_contratto
			),
			'contain' => array(
				'Cliente'
			)
		));
		if(empty($rid)) {
			throw new NotFoundException('Contratto non presente in archivio');
		}
		
		
		$this->set('rid', $rid);
	}
	
	/*
	 * 
	 */
	public function edit($id) {
		
		$rid = $this->AutorizzazioneRid->findById($id);

		if(empty($rid)) throw new NotFoundException('Metodo di pagamento non trovato');

		$this->set('rid', $rid);
		
		if( !empty($this->request->data) ) {
			
			// se sto editando forza sempre rid_filled
			$this->request->data['AutorizzazioneRid']['rid_filled'] = date('Y-m-d H:i:s');
			
			if( $this->AutorizzazioneRid->save($this->request->data) ) {
				$this->Session->setFlash('Autorizzazione RID memorizzata correttamente', 'flash_ok');
				$this->redirect(array('action' => 'edit', $id));
			}
			else {
				$this->Session->setFlash('Errore salvataggio. Verificare i dati inseriti per procedere', 'flash_error');
			}
			
			return;
			
		}
		
		$this->request->data = $rid;
	}
	
	/**
	 * 
	 */
	public function confirm_delete($id) {
		
		$rid = $this->AutorizzazioneRid->findById($id);
		
		if(empty($rid)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		$this->set('rid', $rid);
		
	}
	
	/**
	 * 
	 */
	public function delete($id) {
		
		$rid = $this->AutorizzazioneRid->findById($id);
		if( empty($rid) ) throw new NotFoundException('Autorizzazione RID non trovata');
		if( $this->AutorizzazioneRid->delete($id) ) {
			$this->Session->setFlash('RID rimosso correttamente', 'flash_ok');
		}
		else {
			$this->Session->setFlash('Errore durante cancellazione', 'flash_error');
		}
		
		$this->redirect( array('controller' => 'clienti', 'action' => 'view', $rid['Cliente']['id']) );
	}
}
