<?php

class SyncDbLocaleConZollaShell extends AppShell {
    
    public $uses = array(
		'ClienteZolla', 
		'Cliente', 
		'User', 
		'Contratto', 
		'Recapito', 
		'RecapitoZolla',
		'Articolo',
		'ArticoloZolla',
		'Prodotto',
		'ProdottoZolla',
		'Fornitore',
		'FornitoreZolla',
		'ArticoloPrezzo',
		'ArticoloPrezzoZolla',
		'TagCategoriaWeb',
		'TagCategoriaWebZolla',
		'CategoriaWeb',
		'CategoriaWebZolla',
		'Sottocategoria',
		'SottocategoriaZolla',
		'Indirizzo',
		'IndirizzoZolla',
		'TipoSpesa',
		'TipoSpesaZolla',
		'ArticoloDisponibilita',
		'ArticoloDisponibilitaZolla',
		'ArticoloDisponibilitaMlp',
		'ArticoloDisponibilitaZollaMlp',
		
		// parte tmp per la creazione in massa dei metodi di pagamento
		'CustomerTmp',
		'RidAuthTmp',
		'CartaDiCredito',
		'AutorizzazioneRid',
		
		'Command'
		
		
	);
    
    public function main() {
		
		//$this->sync_articoli();
		
		// crea i metodi di pagamento in massa
		//$this->syncModel('Cliente', 'ClienteZolla', array('ID_CLIENTE' => 'id')); // farlo sempre in caso manchino clienti (ovvero in caso di errori sql in fase di creazione metodi pagamento)
		//$this->_tmpCreaMetodiPagamento();
    }
    
    public function sync_clienti() {
		
		CakeLog::write('info', 'Iniziata sincronizzazione clienti con zolla');
		/*$this->Command->save(array(
			'name' => 'Sincronizzazione clienti',
			'start' => date('Y-m-d H:i:s')
		));
		$commandID = $this->Command->id;*/
		
		//  sync clienti
		$this->syncModel('Cliente', 'ClienteZolla', array('ID_CLIENTE' => 'id'));
		$this->syncModel('Recapito', 'RecapitoZolla', array('ID_CLIENTE_RECAPITO' => 'id', 'ID_CLIENTE' => 'cliente_id'));
		$this->syncModel('Indirizzo', 'IndirizzoZolla', array('ID_INDIRIZZO' => 'id', 'ID_CLIENTE' => 'cliente_id'));
		// crea gli utenti per i nuovi clienti
		$this->_syncUsers();
		// crea i contratti in massa
		$this->_creaContratti();
		
		CakeLog::write('info', 'Conclusa sincronizzazione clienti con zolla');
		/*$this->Command->save(array(
			'id' => $commandID,
			'end' => date('Y-m-d H:i:s')
		));*/
	}
    
    public function sync_articoli() {
		
		CakeLog::write('info', 'Iniziata sincronizzazione articoli con zolla');
		/*$this->Command->save(array(
			'name' => 'Sincronizzazione articoli (con prezzo)',
			'start' => date('Y-m-d H:i:s')
		));
		$commandID = $this->Command->id;*/
		
		$this->syncModel(
			'Articolo', 
			'ArticoloZolla', 
			array('ID_ARTICOLI' => 'id', 'ID_PRODOTTO' => 'prodotto_id'),
			array('CONFEZIONE_QUANTITA')
		);
		$this->syncModel(
			'ArticoloPrezzo', 
			'ArticoloPrezzoZolla', 
			array('ID_ARTICOLO_PREZZO' => 'id', 'ID_ARTICOLI' => 'articolo_id'),
			array('PREZZO_ACQUISTO', 'PREZZO_ACQUISTO_UDM', 'PREZZO_VENDITA', 'PREZZO_CALCOLATO', 'PREZZO_ARTICOLO', 'PREZZO_TRASPORTO')
		);
		$this->syncModel('Prodotto', 'ProdottoZolla', array('ID_PRODOTTO' => 'id', 'ID_FORNITORE' => 'fornitore_id', 'ID_SOTTOCATEGORIA' => 'sottocategoria_id'));
		$this->syncModel('Fornitore', 'FornitoreZolla', array('ID_FORNITORE' => 'id'));
		$this->syncModel('TagCategoriaWeb', 'TagCategoriaWebZolla', array('TAG_ID' => 'id', 'CATEGORIA_WEB_ID' => 'categoria_web_id'));
		$this->syncModel('CategoriaWeb', 'CategoriaWebZolla', array('CATEGORIA_WEB_ID' => 'id'));
		$this->syncModel('Sottocategoria', 'SottocategoriaZolla', array('ID_SOTTOCATEGORIA' => 'id', 'ID_CATEGORIE' => 'categoria_id', 'CATEGORIA_WEB_ID' => 'categoria_web_id'));
		$this->syncModel('TipoSpesa', 'TipoSpesaZolla', array('ID_TIPOSPESA' => 'id'));
		
		CakeLog::write('info', 'Conclusa sincronizzazione articoli con zolla');
		/*$this->Command->save(array(
			'id' => $commandID,
			'end' => date('Y-m-d H:i:s')
		));*/
	}
	
	// identica a sync_articoli ma non si sincronizzano i prezzi (richiesta di zolle ...)
	public function sync_articoli_no_prezzi() {
		
		CakeLog::write('info', 'Iniziata sincronizzazione articoli (no prezzi) con zolla');
		/*$this->Command->save(array(
			'name' => 'Sincronizzazione articoli (senza prezzo)',
			'start' => date('Y-m-d H:i:s')
		));
		$commandID = $this->Command->id;*/
		
		$this->syncModel(
			'Articolo', 
			'ArticoloZolla', 
			array('ID_ARTICOLI' => 'id', 'ID_PRODOTTO' => 'prodotto_id'),
			array('CONFEZIONE_QUANTITA')
		);
		$this->syncModel('Prodotto', 'ProdottoZolla', array('ID_PRODOTTO' => 'id', 'ID_FORNITORE' => 'fornitore_id', 'ID_SOTTOCATEGORIA' => 'sottocategoria_id'));
		$this->syncModel('Fornitore', 'FornitoreZolla', array('ID_FORNITORE' => 'id'));
		$this->syncModel('TagCategoriaWeb', 'TagCategoriaWebZolla', array('TAG_ID' => 'id', 'CATEGORIA_WEB_ID' => 'categoria_web_id'));
		$this->syncModel('CategoriaWeb', 'CategoriaWebZolla', array('CATEGORIA_WEB_ID' => 'id'));
		$this->syncModel('Sottocategoria', 'SottocategoriaZolla', array('ID_SOTTOCATEGORIA' => 'id', 'ID_CATEGORIE' => 'categoria_id', 'CATEGORIA_WEB_ID' => 'categoria_web_id'));
		$this->syncModel('TipoSpesa', 'TipoSpesaZolla', array('ID_TIPOSPESA' => 'id'));
		
		CakeLog::write('info', 'Conclusa sincronizzazione articoli (no prezzi) con zolla');
		/*$this->Command->save(array(
			'id' => $commandID,
			'end' => date('Y-m-d H:i:s')
		));*/
	}
	
	/**
	 * 
	 */
	public function sync_articoli_disponibilita() {
		$lastUpdateTime = $this->ArticoloDisponibilita->getLastUpdateTime();
		$newOrUpdatedRecords = $this->ArticoloDisponibilitaZolla->getNewOrUpdated($lastUpdateTime);
		// fai lo stesso per mlp
		$lastUpdateTimeMlp = $this->ArticoloDisponibilitaMlp->getLastUpdateTime();
		$newOrUpdatedRecordsMlp = $this->ArticoloDisponibilitaZollaMlp->getNewOrUpdated($lastUpdateTimeMlp);
		
		// merge in locale
		// su zolla è una vista, i campi sono già tutti come in locale (devo solo aggiungere l'id)
		foreach($newOrUpdatedRecords as $i => $val) {
			$val = $val['articoli_ml_disponibilita'];
			$newOrUpdatedRecords[$i] = $val;
			// componi l'id per il record locale (cake 2.x non supporta le chiavi multiple)
			$newOrUpdatedRecords[$i]['id'] = $val['id_articolo'].'-'.$val['data_consegna'];
		}
		foreach($newOrUpdatedRecordsMlp as $i => $val) {
			$val = $val['articoli_mlp_disponibilita'];
			$newOrUpdatedRecordsMlp[$i] = $val;
			// componi l'id per il record locale (cake 2.x non supporta le chiavi multiple)
			$newOrUpdatedRecordsMlp[$i]['id'] = $val['id_articolo'].'-'.$val['data_consegna'];
		}
		
		$success = true;
		if(!empty($newOrUpdatedRecords)) { // controllo necessario
			$success = $this->ArticoloDisponibilita->saveAll($newOrUpdatedRecords);
		}
		if(!empty($newOrUpdatedRecordsMlp)) { // controllo necessario
			$success &= $this->ArticoloDisponibilitaMlp->saveAll($newOrUpdatedRecordsMlp);
		}
		
		if($success) {
			CakeLog::write('info', 'Sincronizzazione disponibilità articoli: OK');
		}
		else {
			CakeLog::write('info', 'Sincronizzazione disponibilità articoli: KO');
		}
	}
    
    /**
     * @param columnMap: per convertire nomi colonne (es. cake ha generalmente problemi con query complesse se la chiave primaria non è id ...)
     * @param decimalCols: alcune colonne con numeri con la virgola su zolla sono scritte su dei varchar e in alcuni casi hanno la virgola al posto del punto e devono essere convertite
     */
    function syncModel($localModel, $remoteModel, $columnMap, $decimalCols=array()) {
		
		$this->out('Sync of '.$localModel.' started');
		CakeLog::write('info', 'Sync of '.$localModel.' started');
		
		$numRows = $this->$remoteModel->find('count');
		$pageSize = 100; // per limitare il consumo di memoria usa un meccanismo di paginazione
		for($page=0; $page<intval(ceil($numRows/$pageSize)); $page++) {
			$rows = $this->$remoteModel->find('all', array('recursive' => -1, 'limit' => $pageSize, 'offset' => $page*$pageSize));
			for($i=0;$i<sizeof($rows);$i++) {
				// modifica il model (da remote a local) per il saveAll
				$rows[$i][$localModel] = $rows[$i][$remoteModel];
				unset($rows[$i][$remoteModel]); 
				// converti le chiavi
				foreach(array_keys($columnMap) as $remoteCol) {
					$rows[$i][$localModel][$columnMap[$remoteCol]] =  $rows[$i][$localModel][$remoteCol];
					unset($rows[$i][$localModel][$remoteCol]);
				}
				// replace delle virgole sulle colonne numeriche che lo richiedono
				foreach($decimalCols as $c) {
					$rows[$i][$localModel][$c] = str_replace(',', '.', $rows[$i][$localModel][$c]);
				}
			}
			$success = $this->$localModel->saveAll($rows);
			if(!$success) {
				$this->out('Save error during sync of '.$localModel.' Processed '.$pageSize*$page.' out of '.$numRows);
				CakeLog::write('info', 'Save error during sync of '.$localModel.' Processed '.$pageSize*$page.' out of '.$numRows);
				return;
			}
		}
		$this->out('Sync of '.$localModel.' succeded. Processed '.$numRows);
		CakeLog::write('info', 'Sync of '.$localModel.' succeded. Processed '.$numRows);
	}
	
	/**
	 * - crea gli utenti per i nuovi clienti ricevuti da zolla
	 * - aggiorna il ruolo di tutti gli utenti in base a quello indicato nella tabella clienti
	 */
	function _syncUsers() {
		
		CakeLog::write('info', '> Iniziata Sincronizzazione utenti');
		
		$success = true;
		
		$db = $this->Cliente->getDataSource();
		$q = "SELECT clienti.id, clienti.NOME, clienti.COGNOME, clienti.RUOLO_ML FROM clienti LEFT JOIN users ON clienti.id = users.cliente_id WHERE users.cliente_id IS NULL";
		$res = $db->fetchAll($q);
		$users = array();
		$count = 0;
		foreach($res as $r) {
			$users[] = array('User' => array(
				'username' => uniqid().'-'.$count++, // solo perchè deve essere unique ... al momento non serve
				'group_id' => $r['clienti']['RUOLO_ML'] ? $r['clienti']['RUOLO_ML'] : CLIENTE_SENZA_OPERATIVITA,
				'cliente_id' => $r['clienti']['id'],
				'active' => 1
			));
		}
		if(!empty($users)) $success = $this->User->saveAll($users);
		
		if($success) {
			CakeLog::write('info', '> Sincronizzazione utenti: OK');
		}
		else {
			CakeLog::write('info', '> Sincronizzazione utenti: KO');
		}
		
		CakeLog::write('info', '> Iniziata Sincronizzazione ruoli');
		
		// aggiorna il ruolo degli utenti collegati a dei clienti
		$q = "SELECT users.id, clienti.RUOLO_ML FROM users INNER JOIN clienti ON clienti.id = users.cliente_id";
		$res = $db->fetchAll($q);
		$users = array();
		$count = 0;
		foreach($res as $r) {
			$users[] = array('User' => array(
				'id' => $r['users']['id'],
				'group_id' => $r['clienti']['RUOLO_ML'] ? $r['clienti']['RUOLO_ML'] : CLIENTE_SENZA_OPERATIVITA
			));
		}
		if(!empty($users)) $success &= $this->User->saveAll($users);
		
		if($success) {
			CakeLog::write('info', '> Sincronizzazione ruoli: OK');
		}
		else {
			CakeLog::write('info', '> Sincronizzazione ruoli: KO');
		}
	}
	
	/**
	 * 
	 */
	function _creaContratti() {
		$salt = Configure::read('Security.salt');
		
		$db = $this->Cliente->getDataSource();
		// ottieni i nuovi clienti per cui deve essere creato contratto
		$q = "SELECT clienti.id, clienti.NOME, clienti.COGNOME, clienti.ID_CLIENTE_FATTURAZIONE FROM clienti LEFT JOIN contratti ON clienti.id = contratti.cliente_id WHERE contratti.cliente_id IS NULL";
		$res1 = $db->fetchAll($q);
		// ottieni i contratti che devono essere aggiornati (chiudo e apro uno nuovo) perchè è cambiato il cliente fatturazione del cliente e quindi il contratto non è più valido
		$q2 = "SELECT contratti.id, clienti.id, clienti.NOME, clienti.COGNOME, clienti.ID_CLIENTE_FATTURAZIONE FROM contratti INNER JOIN clienti ON clienti.id = contratti.cliente_id WHERE contratti.cliente_fatturazione_id != clienti.ID_CLIENTE_FATTURAZIONE AND contratti.data_chiusura IS NULL";
		$res2 = $db->fetchAll($q2);
		
		$contratti = array();
		foreach($res1 as $r) {
			if(empty($r['clienti']['ID_CLIENTE_FATTURAZIONE'])) continue; // clienti non più attivi. non genero nessun contratto
			
			// NOTA: i token di accesso possono essere NULL nel caso in cui si voglia disabilitare l'accesso per un determinato utente
			
			// nella generazione degli access token aggiungo un rand() per garantire l'univocità del token nel caso gli stessi identici clienti abbiano più contratti attivi generati nel medesimo momento (qui non può succedere ma in futuro ...)
			// per garantire l'effettiva univocità dei token, aggiungo come prima parte l'id del cliente.
			$cliente_access_token = $r['clienti']['id'].'$x$'.sha1( rand(0,10000).$r['clienti']['id'].$r['clienti']['NOME'].$r['clienti']['COGNOME'].$salt.date('Y-m-d H:i:s') );
			$cliente_fatturazione_access_token = $r['clienti']['ID_CLIENTE_FATTURAZIONE'].'$y$'.sha1( rand(0,10000).$r['clienti']['ID_CLIENTE_FATTURAZIONE'].$r['clienti']['NOME'].$r['clienti']['COGNOME'].$salt.date('Y-m-d H:i:s') );
			
			$contratti[] = array(
				'cliente_id' => $r['clienti']['id'],
				'cliente_fatturazione_id' => $r['clienti']['ID_CLIENTE_FATTURAZIONE'],
				'cliente_access_token' => $cliente_access_token, 
				'cliente_fatturazione_access_token' => $cliente_fatturazione_access_token,
			);
			
		}
		foreach($res2 as $r) {
			// chiudo il contratto esistente
			$contratti[] = array(
				'id' => $r['contratti']['id'],
				'data_chiusura' => date('Y-m-d H:i:s'),
			);
			// apro un nuovo contratto
			$cliente_access_token = $r['clienti']['id'].'$x$'.sha1( rand(0,10000).$r['clienti']['id'].$r['clienti']['NOME'].$r['clienti']['COGNOME'].$salt.date('Y-m-d H:i:s') );
			$cliente_fatturazione_access_token = $r['clienti']['ID_CLIENTE_FATTURAZIONE'].'$y$'.sha1( rand(0,10000).$r['clienti']['ID_CLIENTE_FATTURAZIONE'].$r['clienti']['NOME'].$r['clienti']['COGNOME'].$salt.date('Y-m-d H:i:s') );
			
			$contratti[] = array(
				'cliente_id' => $r['clienti']['id'],
				'cliente_fatturazione_id' => $r['clienti']['ID_CLIENTE_FATTURAZIONE'],
				'cliente_access_token' => $cliente_access_token, 
				'cliente_fatturazione_access_token' => $cliente_fatturazione_access_token,
			);
		}
		
		if(!empty($contratti)) $res = $this->Contratto->saveAll($contratti, array('validate' => false)); // non devo validare per via di un campo extra nella validazione!
	}
	
	/**
	 *  crea i metodi di pagamento iniziali a partire dal db di sqlite.
	 *  NOTA: nel db di partenza ci può essere al più una carta di credito e un rid per ogni cliente!
	 */
	function _tmpCreaMetodiPagamento() {
		
		$this->CartaDiCredito->deleteAll(array('CartaDiCredito.id >' => 0));
		$this->AutorizzazioneRid->deleteAll(array('AutorizzazioneRid.id >' => 0));
		
		// paginate to reduce the amount of ram used
		
		$currOffset = 0;
		
		$clienti = array();
		
		while(true) {
			$customers = $this->CustomerTmp->find('all', array('limit' => 100, 'offset' => $currOffset));
			if(empty($customers)) break;
			
			$carte_di_credito = array();
			$autorizzazioni_rid = array();
			
			foreach($customers as $customer) {
				$cliente_id = $customer['CustomerTmp']['id'];
				
				unset($customer['CustomerTmp']['id']);
				unset($customer['RidAuthTmp']['id']);
				
				unset($customer['CustomerTmp']['created']);
				$dates = array('sent', 'signed', 'rid_sent', 'rid_filled', 'rid_activated');
				foreach($dates as $d) {
					if(!empty($customer['CustomerTmp'][$d])) {
						$customer['CustomerTmp'][$d] = date('Y-m-d H:i:s', $customer['CustomerTmp'][$d]);
					}
					else {
						$customer['CustomerTmp'][$d] = '';
					}
				}
				
				if( !empty($customer['CustomerTmp']['id_contratto']) ) {
					$c = $customer['CustomerTmp'];
					$c['cliente_id'] = $cliente_id;
					$carte_di_credito[] = $c;
				}
					
				if( !empty($customer['CustomerTmp']['id_contratto_rid']) ) {
					$a = array_merge($customer['CustomerTmp'], $customer['RidAuthTmp']);
					$a['cliente_id'] = $cliente_id;
					$autorizzazioni_rid[] = $a;
				} 
				
				$clienti[$cliente_id] = array(
					'id' => $cliente_id,
					'tipo_metodo_pagamento_attivo_id' => $customer['CustomerTmp']['curr_payment_method']
				);
				
			}
			
			if(!empty($carte_di_credito)) $this->CartaDiCredito->saveAll($carte_di_credito);
			if(!empty($autorizzazioni_rid)) $this->AutorizzazioneRid->saveAll($autorizzazioni_rid, array('validate' => false)); // importante NON validare in questa fase
			
			$currOffset += 100;
		}
		
		// leggi i metodi di pagamento salvati per ottenere gli id dei metodi di pagamento attivi
		$carte_di_credito = $this->CartaDiCredito->find('all', array('fields' => array('id', 'cliente_id')));
		$autorizzazioni_rid = $this->AutorizzazioneRid->find('all', array('fields' => array('id', 'cliente_id')));
			
		foreach($carte_di_credito as $c) {				
			if( $clienti[ $c['CartaDiCredito']['cliente_id'] ]['tipo_metodo_pagamento_attivo_id'] == 1 ) { // carta
				$clienti[ $c['CartaDiCredito']['cliente_id'] ]['metodo_pagamento_attivo_id'] = $c['CartaDiCredito']['id'];
			}
		}
			
		foreach($autorizzazioni_rid as $a) {		
			if( $clienti[ $a['AutorizzazioneRid']['cliente_id'] ]['tipo_metodo_pagamento_attivo_id'] == 2 ) { // rid
				$clienti[ $a['AutorizzazioneRid']['cliente_id'] ]['metodo_pagamento_attivo_id'] = $a['AutorizzazioneRid']['id'];
			}
		}
			
		$this->Cliente->saveAll( array_values($clienti) );
	
	}
    
}
 
