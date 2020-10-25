<?php

class ContrattiController extends AppController {

	var $name = "Contratti";
	
	public $uses = array(
		'Contratto', 
		'Cliente', 
		'ClienteZolla',
		'Recapito', 
		'MetodoPagamento', 
		'User',
		'Addebito',
		'CartaDiCredito',
		'AutorizzazioneRid',
		'Bonifico',
		'Contante',
		'ProceduraLegale'
	);

	public function index($tipo) {
		
		$this->set('tipo', $tipo);
	
	}

	/**
	 * metodo add() in modalità REST
	 */
	public function aggiungi($cliente_id, $cliente_fatturazione_id, $metodo_pagamento_id) {
		// TODO
	}

	/*
	 * crea un nuovo contratto (la relazione dei clienti a cui il contratto si riferisce è già stata creata su zolla quindi non me ne devo preoccupare)
	 * 
	 */
	public function add() {
		
		if( !empty($this->request->data) ) {
			
			// valida
			$this->Contratto->set($this->request->data['Contratto']);
			if ($this->Contratto->validates(array('fieldList' => array('cliente_id', 'cliente_fatturazione_id', 'metodo_pagamento')))) {
				// valid
				$d = $this->request->data['Contratto']; // shorten ...
				
				// inizia una transaction
				$transactionOk = true;
		
				$dbo1 = $this->Cliente->getDataSource();
				$dbo2 = $this->Recapito->getDataSource();
				$dbo3 = $this->User->getDataSource();
				$dbo4 = $this->Contratto->getDataSource();
				$dbo5 = $this->Addebito->getDataSource();
				$dbo6 = $this->CartaDiCredito->getDataSource();
				$dbo7 = $this->AutorizzazioneRid->getDataSource();
				$dbo8 = $this->Bonifico->getDataSource();
				$dbo9 = $this->Contante->getDataSource();
				$dbo10 = $this->ProceduraLegale->getDataSource();

				$dbo1->begin();
				$dbo2->begin();
				$dbo3->begin();
				$dbo4->begin();
				$dbo5->begin();
				$dbo6->begin();
				$dbo7->begin();
				$dbo8->begin();
				$dbo9->begin();
				$dbo10->begin();
				
				// verifica se i clienti sono già presenti, altrimenti leggili da zolla e salvali
				$cliente = $this->Cliente->findById($d['cliente_id']);
				if(empty($cliente)) {
					$cliente = $this->ClienteZolla->find('first', array('conditions' => array('ID_CLIENTE' => $d['cliente_id'])));
					if(empty($cliente)) {
						throw new NotFoundException('Il cliente '.$d['cliente_id'].' non esiste!');
					}
					// crea l'utente per accedere all'area riservata
					$cliente['User'][0] = array(
						'username' => $d['cliente_id'].uniqid(),
						'role' => CLIENTE_STANDARD,
						'cliente_id' => $d['cliente_id'],
						'active' => 1
					);
					$transactionOk &= $this->Cliente->saveAll($cliente); // saveAll per salvare anche i recapiti e utente
				}
				$cliente_fatt = $this->Cliente->findById($d['cliente_fatturazione_id']);
				if(empty($cliente_fatt)) {
					$cliente_fatt = $this->ClienteZolla->find('first', array('conditions' => array('ID_CLIENTE' => $d['cliente_fatturazione_id'])));
					if(empty($cliente_fatt)) {
						throw new NotFoundException('Il cliente '.$d['cliente_fatturazione_id'].' non esiste!');
					}
					// crea l'utente per accedere all'area riservata
					$cliente_fatt['User'][0] = array(
						'username' => $d['cliente_fatturazione_id'].uniqid(),
						'role' => CLIENTE_STANDARD,
						'cliente_id' => $d['cliente_fatturazione_id'],
						'active' => 1
					);
					$transactionOk &= $this->Cliente->saveAll($cliente_fatt); // saveAll per salvare anche i recapiti e utente
				}
				
				// se esiste già un contratto attivo per la coppia X-Y chiudilo in automatico
				// ogni coppia clienti X-Y può avere un solo contratto attivo
				$contrattoEsistente = $this->Contratto->find('first', array('conditions' => array(
					'data_chiusura' => null,
					'cliente_id' => $d['cliente_id'],
					'cliente_fatturazione_id' => $d['cliente_fatturazione_id'],
				)));
				if( !empty($contrattoEsistente) ) {
					// chiudi il contratto
					$contrattoEsistente['Contratto']['data_chiusura'] = date('Y-m-d H:i:s');
					$contrattoEsistente['Contratto']['note'] = 'Chiusura automatica per apertura nuovo contratto';
					// uso validate => false perchè nella validazione del modello ho un field extra
					$transactionOk &= $this->Contratto->save($contrattoEsistente['Contratto'], array('validate' => false));
				}
				
				// crea il contratto
				$salt = Configure::read('Security.salt');
				$cliente_access_token = $cliente['Cliente']['id'].'$x$'.sha1( rand(0,10000).$cliente['Cliente']['id'].$cliente['Cliente']['displayName'].$salt.date('Y-m-d H:i:s') );
				$cliente_fatturazione_access_token = $cliente_fatt['Cliente']['id'].'$y$'.sha1( rand(0,10000).$cliente_fatt['Cliente']['id'].$cliente_fatt['Cliente']['displayName'].$salt.date('Y-m-d H:i:s') );
			
				$this->Contratto->create(); // mandatory perchè se ho chiuso prima un contratto (in pratica) sto salvando in loop!
				$transactionOk &= $this->Contratto->save(array(
					'cliente_id' => $d['cliente_id'],
					'cliente_fatturazione_id' => $d['cliente_fatturazione_id'],
					'cliente_access_token' => $cliente_access_token, 
					'cliente_fatturazione_access_token' => $cliente_fatturazione_access_token,
				), array('validate' => false)); // uso validate => false perchè nella validazione del modello ho un field extra
				
				// invoca la creazione del metodo di pagamento scelto 
				// (che a sua volta invia anche la mail (eventuale) al cliente fatturazione)
				// se il metodo scelto non esiste ancora e rendilo attivo, 
				// altrimenti se esiste già forzalo semplicemente come attivo
				
				// ricarica cliente fatt per ottenere i metodi di pagamento
				$cliente_fatt = $this->Cliente->findById($d['cliente_fatturazione_id']);
				
				switch($d['metodo_pagamento']) {
					case CARTA:
						if(empty($cliente['CartaDiCredito'])) { // crea il metodo di pagamento
							$transactionOk &= $this->requestAction('/carte_di_credito/add/'.$d['cliente_fatturazione_id']);
						}
						else {
							// invia a cliente fatt unicamente mail di benvenuto
							$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente_fatt['Recapito'], 'I');
							foreach($recapiti as $recapito) {
								// invia mail di benvenuto
								$Email = new CakeEmail('mandrillapp');
								$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
								$Email->to($recapito);
								$Email->subject(__("Benvenuto in Le Zolle"));
								$Email->setHeaders(array(
									'X-MC-Metadata' => '{"id_cliente": "'.$d['cliente_id'].'", "id_cliente_fatturazione": "'.$d['cliente_fatturazione_id'].'"}'
								));
								$Email->emailFormat('html');
								$Email->template('benvenuto_cliente_fatt_carta', 'default');
								$Email->viewVars(array(
									'nomeCliente' => $cliente_fatt['Cliente']['displayName'],
								));
								//$Email->send();
							}
						}
						
						// forza l'aggiornamento del metodo di pagamento attivo (il più recente tra quelli disponibili per il tipo corrente)
						$cliente_fatt = $this->Cliente->findById($d['cliente_fatturazione_id']);
						$cliente_fatt['Cliente']['tipo_metodo_pagamento_attivo_id'] = CARTA;
						$cliente_fatt['Cliente']['metodo_pagamento_attivo_id'] = $cliente_fatt['CartaDiCredito'][ sizeof($cliente_fatt['CartaDiCredito'])-1 ]['id'];
						$transactionOk &= $this->Cliente->save($cliente_fatt['Cliente']);
						break;
						
					case RID:
						if(empty($cliente['AutorizzazioneRid'])) { // crea il metodo di pagamento
							$transactionOk &= $this->requestAction('/autorizzazioni_rid/add/'.$d['cliente_fatturazione_id']);
						}
						else {
							// invia a cliente fatt unicamente mail di benvenuto
							$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente_fatt['Recapito'], 'I');
							foreach($recapiti as $recapito) {
								// invia mail di benvenuto
								$Email = new CakeEmail('mandrillapp');
								$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
								$Email->to($recapito);
								$Email->subject(__("Benvenuto in Le Zolle"));
								$Email->emailFormat('html');
								$Email->setHeaders(array(
									'X-MC-Metadata' => '{"id_cliente": "'.$d['cliente_id'].'", "id_cliente_fatturazione": "'.$d['cliente_fatturazione_id'].'"}'
								));
								$Email->template('benvenuto_cliente_fatt_rid', 'default');
								$Email->viewVars(array(
									'nomeCliente' => $cliente_fatt['Cliente']['displayName'],
								));
								//$Email->send();
							}
						}
						
						// forza l'aggiornamento del metodo di pagamento attivo (il più recente tra quelli disponibili per il tipo corrente)
						$cliente_fatt = $this->Cliente->findById($d['cliente_fatturazione_id']);
						$cliente_fatt['Cliente']['tipo_metodo_pagamento_attivo_id'] = RID;
						$cliente_fatt['Cliente']['metodo_pagamento_attivo_id'] = $cliente_fatt['AutorizzazioneRid'][ sizeof($cliente_fatt['AutorizzazioneRid'])-1 ]['id'];
						$transactionOk &= $this->Cliente->save($cliente_fatt['Cliente']);
						break;
						
					case BOFINICO:
						if(empty($cliente['Bonifico'])) { // crea il metodo di pagamento
							$transactionOk &= $this->requestAction('/bonifici/add/'.$d['cliente_fatturazione_id']);
						}
						else {
							// invia a cliente fatt unicamente mail di benvenuto
							$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente_fatt['Recapito'], 'I');
							foreach($recapiti as $recapito) {
								// invia mail di benvenuto
								$Email = new CakeEmail('mandrillapp');
								$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
								$Email->to($recapito);
								$Email->subject(__("Benvenuto in Le Zolle"));
								$Email->emailFormat('html');
								$Email->setHeaders(array(
									'X-MC-Metadata' => '{"id_cliente": "'.$d['cliente_id'].'", "id_cliente_fatturazione": "'.$d['cliente_fatturazione_id'].'"}'
								));
								$Email->template('benvenuto_cliente_fatt_bonifico', 'default');
								$Email->viewVars(array(
									'nomeCliente' => $cliente_fatt['Cliente']['displayName'],
								));
								//$Email->send();
							}
						}
						
						// forza l'aggiornamento del metodo di pagamento attivo (il più recente tra quelli disponibili per il tipo corrente)
						$cliente_fatt = $this->Cliente->findById($d['cliente_fatturazione_id']);
						$cliente_fatt['Cliente']['tipo_metodo_pagamento_attivo_id'] = BONIFICO;
						$cliente_fatt['Cliente']['metodo_pagamento_attivo_id'] = $cliente_fatt['Bonifico'][ sizeof($cliente_fatt['Bonifico'])-1 ]['id'];
						$transactionOk &= $this->Cliente->save($cliente_fatt['Cliente']);
						break;
					
					case CONTANTI:
						if(empty($cliente['Contante'])) { // crea il metodo di pagamento
							$transactionOk &= $this->requestAction('/contanti/add/'.$d['cliente_fatturazione_id']);
						}
						else {
							// invia a cliente fatt unicamente mail di benvenuto
							$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente_fatt['Recapito'], 'I');
							foreach($recapiti as $recapito) {
								// invia mail di benvenuto
								$Email = new CakeEmail('mandrillapp');
								$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
								$Email->to($recapito);
								$Email->subject(__("Benvenuto in Le Zolle"));
								$Email->setHeaders(array(
									'X-MC-Metadata' => '{"id_cliente": "'.$d['cliente_id'].'", "id_cliente_fatturazione": "'.$d['cliente_fatturazione_id'].'"}'
								));
								$Email->emailFormat('html');
								$Email->template('benvenuto_cliente_fatt_contanti', 'default');
								$Email->viewVars(array(
									'nomeCliente' => $cliente_fatt['Cliente']['displayName'],
								));
								//$Email->send();
							}
						}
						
						// forza l'aggiornamento del metodo di pagamento attivo (il più recente tra quelli disponibili per il tipo corrente)
						$cliente_fatt = $this->Cliente->findById($d['cliente_fatturazione_id']);
						$cliente_fatt['Cliente']['tipo_metodo_pagamento_attivo_id'] = CONTANTI;
						$cliente_fatt['Cliente']['metodo_pagamento_attivo_id'] = $cliente_fatt['Contante'][ sizeof($cliente_fatt['Contante'])-1 ]['id'];
						$transactionOk &= $this->Cliente->save($cliente_fatt['Cliente']);
						break;
						
					default:
					
						$transactionOk = false; // metodo di pagamento non utilizzabile per attivare nuovo contratto!
					
				}
				
				if($transactionOk) {
					
					$dbo1->commit();
					$dbo2->commit();
					$dbo3->commit();
					$dbo4->commit();
					$dbo5->commit();
					$dbo6->commit();
					$dbo7->commit();
					$dbo8->commit();
					$dbo9->commit();
					$dbo10->commit();
					
					// invia anche la mail di benvenuto al cliente che riceve la spesa (se diverso da cliente fatturazione)
					if($d['cliente_id'] != $d['cliente_fatturazione_id']) {
						$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'I');
						foreach($recapiti as $recapito) {
							// invia mail di benvenuto
							$Email = new CakeEmail('mandrillapp');
							$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
							$Email->to($recapito);
							$Email->subject(__("Benvenuto in Le Zolle"));
							$Email->emailFormat('html');
							$Email->setHeaders(array(
									'X-MC-Metadata' => '{"id_cliente": "'.$d['cliente_id'].'", "id_cliente_fatturazione": "'.$d['cliente_fatturazione_id'].'"}'
								));
							$Email->template('benvenuto_cliente', 'default');
							$Email->viewVars(array(
								'nomeCliente' => $cliente['Cliente']['displayName'],
							));
							//$Email->send();
						}
					}
					
					$this->Session->setFlash('Contratto creato correttamente', 'flash_ok');
					$this->redirect( array('action' => 'view', $this->Contratto->id) );
				}
				else {
					$dbo1->rollback();
					$dbo2->rollback();
					$dbo3->rollback();
					$dbo4->rollback();
					$dbo5->rollback();
					$dbo6->rollback();
					$dbo7->rollback();
					$dbo8->rollback();
					$dbo9->rollback();
					$dbo10->rollback();
					
					$this->Session->setFlash('Si è verificato un errore', 'flash_error');
				}
			}
		}
		
		$this->set('metodi_pagamento', array('' => '--- Seleziona ---') + $this->MetodoPagamento->find('list', array('conditions' => array('attivabile' => 1))));
	}

	public function view($id) {
		
		$contratto = $this->Contratto->findById($id);
		if(empty($contratto)) {
			throw new NotFoundException('Il contratto '.$id.' non esiste!');
		}
		$this->set('contratto', $contratto);
	}
	
	public function chiudi($id) {
		
		$contratto = $this->Contratto->findById($id);
		if(empty($contratto)) {
			throw new NotFoundException('Il contratto '.$id.' non esiste!');
		}
		if(!empty($contratto['Contratto']['data_chiusura'])) {
			throw new CakeException('Il contratto '.$id.' è già chiuso!');
		}
		
		if(!empty($this->request->data)) {
			
			if( $this->Contratto->save($this->request->data, array('validate' => false)) ) { // importante non validare per via del metodo di pagamento come validation rule
				$this->Session->setFlash('Contratto chiuso correttamente', 'flash_ok');
				$this->redirect(array('action' => 'view', $id));
			}
			else {
				$this->Session->setFlash('Errore durante chiusura contratto', 'flash_error');
			}	
		}
		else {
			$this->request->data = $contratto;
		}
	}
	
	/**
	 * visualizza la lista dei contratti attivi per la coppia
	 */
	public function coppia($cliente_id, $cliente_fatturazione_id) {
		$cliente = $this->Cliente->findById($cliente_id);
		if(empty($cliente)) {
			throw new NotFoundException('Il cliente '.$cliente_id.' non esiste!');
		}
		$cliente_fatturazione = $this->Cliente->findById($cliente_fatturazione_id);
		if(empty($cliente_fatturazione)) {
			throw new NotFoundException('Il cliente '.$cliente_fatturazione_id.' non esiste!');
		}
		
		$contratti = $this->Contratto->find('all', array(
			'conditions' => array(
				'cliente_id' => $cliente_id,
				'cliente_fatturazione_id' => $cliente_fatturazione_id,
				'data_chiusura' => NULL
			),
			'order' => array('created')
		));
		$this->set(compact('cliente', 'cliente_fatturazione', 'contratti'));
	}

	public function datatables_processing($tipo) {
		
		$this->RequestHandler->setContent('json', 'application/json');

		/* Array of database columns which should be read and sent back to DataTables. Use a space where
	  	 * you want to insert a non-database field (for example a counter or static image)
	 	 */
	 	 
	 	// anche se ho creato un virtualField non posso usarlo perchè non viene impostato nelle clausole order by, quindi devo usare concat()
		$aColumns = array(
			'Contratto.id', 
			'Contratto.cliente_id', 
			'CONCAT(Cliente.COGNOME, " ", Cliente.NOME)',
			'Contratto.cliente_fatturazione_id',
			'CONCAT(ClienteFatturazione.COGNOME, " ", ClienteFatturazione.NOME)'
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
			'ClienteFatturazione' => array(
				'fields' => array('id', 'NOME', 'COGNOME') // i virtual fields non funzionano con contain ...
			),
		);

		if(!empty($sWhere)) $options['conditions'] = $sWhere;
		if(!empty($sOrder)) $options['order'] = $sOrder;

		$options['fields'] = $aColumns;

		// gestisco il tipo di contratto
		$options['conditions'][ ($tipo == 'attivi') ? 'Contratto.data_chiusura' : 'Contratto.data_chiusura <>' ] = NULL;
		
		// non mi serve questo join perchè viene già eseguito il join nella query in base a hasMany impostato sul modello
		/*$options['joins'] = array(
			array(
				'table' => 'clienti',
				'alias' => 'ClienteSpesa',
				'type' => 'INNER',
				'conditions' => array(
					'ClienteSpesa.id = Contratto.cliente_id'
				)
			),
			array(
				'table' => 'clienti',
				'alias' => 'ClienteFattura',
				'type' => 'INNER',
				'conditions' => array(
					'ClienteFattura.id = Contratto.cliente_fatturazione_id'
				)
			)
		);*/
		
		$res = $this->Contratto->find('all', $options);	
		
		unset($options['limit']);
		unset($options['offset']);
		$iFilteredTotal = $this->Contratto->find('count', $options);

		/*
		 * Output
		 */
		$totWhere = array();

		$output = array(
			"sEcho" => isset($this->request->query['sEcho']) ? intval($this->request->query['sEcho']) : 1,
			"iTotalRecords" => $this->Contratto->find('count'),
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
	
		foreach($res as $row) {
			
			$r["DT_RowId"] = $row['Contratto']["id"];
			$r["id"] = $row['Contratto']["id"];
			$r["cliente_id"] = $row['Contratto']["cliente_id"];
			$r["cliente_nome"] = $row['Cliente']["COGNOME"].' '.$row['Cliente']["NOME"];
			$r["cliente_fatturazione_id"] = $row['Contratto']["cliente_fatturazione_id"];
			$r["cliente_fatturazione_nome"] = $row['ClienteFatturazione']["COGNOME"].' '.$row['ClienteFatturazione']["NOME"];
			$r['actions'] = '<a title="visualizza contratto" href="'.Router::url(array('controller' => 'contratti', 'action' => 'view', $row['Contratto']["id"])).'" class="btn btn-xs btn-info">
				<i class="ace-icon fa fa-info-circle bigger-120"></i>
			</a>';

			$output['aaData'][] = $r;
		}

		
		$this->set('res', $output);
		$this->set('_serialize', 'res');
		
	}

} 
