<?php

App::import('Core', 'HttpSocket');

require_once APP . 'Vendor' . DS . 'PHPExcel.php'; // mandatory. This is the (suggested) cake way to include vendors

class AddebitiController extends AppController
{
	var $name = 'Addebiti';

	var $components = array('MyUtil', 'Email', 'PhpExcel');

	var $uses = array('Addebito', 'MetodoPagamento');
   
    
	/**
	 * datatables
	 */
	public function index($tipo=null) {
		
		if( empty($tipo) ) $tipo = ADDEBITI_ALL;
		
		$this->set('tipo_attivo', $tipo);
		
	}
	
	/**
	 * 
	 */
	public function toggle_excluded() {
		
		$this->RequestHandler->setContent('json', 'application/json');
		
		if( $this->request->is('post') ) {
			
			$id = $this->request->data['id'];
			$addebito = $this->Addebito->findById($id);
			if( !empty($addebito) ) {
				$addebito['Addebito']['excluded'] = $addebito['Addebito']['excluded'] == 1 ? NULL : 1;
				if( $this->Addebito->save($addebito) ) $res['success'] = true;
				else $res['success'] = false;
			}
			else $res['success'] = false;
		}
		else $res['success'] = false;
		
		$this->set('res', $res);
		$this->set('_serialize', 'res');
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
			'Addebito.id', 
			'Addebito.cliente_id', 
			'CONCAT(Cliente.COGNOME, " ", Cliente.NOME)',
			'Addebito.importo',
			'Addebito.mese',
			'Addebito.anno',
			'Addebito.last_payment_ok',
			'Addebito.excluded',
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
			'CartaDiCredito',
			'AutorizzazioneRid',
			'Bonifico',
			'Contante',
			'ProceduraLegale',
		);

		if(!empty($sWhere)) $options['conditions'] = $sWhere;
		if(!empty($sOrder)) $options['order'] = $sOrder;

		$options['fields'] = $aColumns;

		// condizioni
		$options['conditions']['active'] = 1;
		
		// condizioni aggiuntive
		$t = $this->request->query['tipo']; 
		if( $t == ADDEBITI_ALL ) {
			$options['conditions']['type'] = RICORRENTE;
		}
		elseif( $t == ADDEBITI_CARTE_OK ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['conditions']['last_payment_ok'] = 1;
			$options['joins'] = array(
				array(
					'table' => 'carte_di_credito',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.carta_id = c.id'
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_CARTE_KO ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['conditions']['last_payment_ok'] = 0;
			$options['joins'] = array(
				array(
					'table' => 'carte_di_credito',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.carta_id = c.id'
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_CARTE_PAGABILI ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['conditions']['last_payment_ok <>'] = 1;
			$options['conditions']['blocked'] = NULL;
			$options['joins'] = array(
				array(
					'table' => 'carte_di_credito',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.carta_id = c.id',
						'c.signed <>' => NULL
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_CARTE_NON_PAGABILI ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['conditions']['last_payment_ok'] = -1;
			$options['conditions']['blocked'] = NULL;
			$options['joins'] = array(
				array(
					'table' => 'carte_di_credito',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.carta_id = c.id',
						'c.signed' => NULL
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_CARTE_BLOCCATE ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['conditions']['last_payment_ok <>'] = 1;
			$options['conditions']['blocked'] = 1;
			$options['joins'] = array(
				array(
					'table' => 'carte_di_credito',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.carta_id = c.id',
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_RID_PAGABILI ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['joins'] = array(
				array(
					'table' => 'autorizzazioni_rid',
					'alias' => 'r',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.rid_id = r.id',
						'r.rid_activated <>' => NULL
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_RID_NON_PAGABILI ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['joins'] = array(
				array(
					'table' => 'autorizzazioni_rid',
					'alias' => 'r',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.rid_id = r.id',
						'r.rid_activated' => null
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_BONIFICO ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['joins'] = array(
				array(
					'table' => 'bonifici',
					'alias' => 'b',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.bonifico_id = b.id'
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_CONTANTE ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['joins'] = array(
				array(
					'table' => 'contanti',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.contante_id = c.id'
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_LEGALE ) {
			$options['conditions']['type'] = RICORRENTE;
			$options['joins'] = array(
				array(
					'table' => 'procedure_legali',
					'alias' => 'p',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.legale_id = p.id'
					)
                ),
			);
		}
		elseif( $t == ADDEBITI_CARICATI_DA_CONFERMARE ) {
			$options['conditions']['type'] = RICORRENTE_NON_CONFERMATO;
		}
		elseif( $t == ADDEBITI_NON_ATTIVI ) {
			$options['conditions']['active'] = 0;
		}
		
		$res = $this->Addebito->find('all', $options);	
		
		unset($options['limit']);
		unset($options['offset']);
		$iFilteredTotal = $this->Addebito->find('count', $options);

		/*
		 * Output
		 */
		$totWhere = array();

		$output = array(
			"sEcho" => isset($this->request->query['sEcho']) ? intval($this->request->query['sEcho']) : 1,
			"iTotalRecords" => $this->Addebito->find('count'),
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
	
		foreach($res as $row) {
			
			$r["DT_RowId"] = $row['Addebito']["id"];
			$r["id"] = $row['Addebito']["id"];
			$r["cliente_id"] = '<a href="'.Router::url(array('controller' => 'clienti', 'action' => 'view', $row['Addebito']["cliente_id"])).'">'.$row['Addebito']["cliente_id"].'</a>';
			$r["cliente_nome"] = '<a href="'.Router::url(array('controller' => 'clienti', 'action' => 'view', $row['Addebito']["cliente_id"])).'">'.$row['Cliente']["COGNOME"].' '.$row['Cliente']["NOME"].'</a>';
			$r['importo'] = number_format($row['Addebito']['importo'], 2, ',', '.');
			$r["mese"] = $row['Addebito']["mese"];
			$r["anno"] = $row['Addebito']["anno"];
			$r["excluded"] = $row['Addebito']["excluded"];
			
			if( !empty($row['CartaDiCredito']['id']) ) {
				$r['tipo_addebito'] = 'CARTA';
				switch($row['Addebito']['last_payment_ok']) {
					case -1:
						$r["stato"] = '<span class="yellow"><b>Nessun pagamento eseguito</b></span>';
						break;
					case 0:
						$r["stato"] = '<span class="red"><b>KO</b></span>';
						break;
					case 1:
						$r["stato"] = '<span class="green"><b>OK</b></span>';
						break;
					default:
						$r["stato"] = '';
				}
			}
			else {
				$r["stato"] = 'n.d.';
				
				if( !empty($row['AutorizzazioneRid']['id']) ) $r['tipo_addebito'] = 'RID';
				elseif( !empty($row['Bonifico']['id']) ) $r['tipo_addebito'] = 'BONIFICO';
				elseif( !empty($row['Contante']['id']) ) $r['tipo_addebito'] = 'CONTANTI';
				elseif( !empty($row['ProceduraLegale']['id']) ) $r['tipo_addebito'] = 'PROCEDURA LEGALE';
				
				
			}
			
			$r['actions'] = '<a title="visualizza spesa" href="'.Router::url(array('controller' => 'addebiti', 'action' => 'view', $row['Addebito']["id"])).'" class="btn btn-xs btn-info">
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
	public function carica_ricorrenti() {
		
		$upload_max_filesize = ini_get('upload_max_filesize');
		$upload_max_filesize = substr($upload_max_filesize, 0, strlen($upload_max_filesize)-1).' '.substr($upload_max_filesize, strlen($upload_max_filesize)-1).'B';
		$this->set('upload_max_filesize', $upload_max_filesize);
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$f = $this->request->data['Addebito']['file'];

			// validate upload
			switch($f['error']) {
				case 0: // UPLOAD_ERR_OK
					break;
				case 1: // UPLOAD_ERR_INI_SIZE
					$this->Session->setFlash('Il file caricato eccede la massima dimensione consentita dal server. Contattare l\'amministratore del sistema per aumentare il limite disponibile', 'flash_error');
					return;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->Session->setFlash('Il file caricato eccede la massima dimensione consentita dal form. Contattare l\'amministratore del sistema per aumentare il limite disponibile', 'flash_error');
					return;
				case 3: // UPLOAD_ERR_PARTIAL
					$this->Session->setFlash('Il file è stato caricato solo parzialmente. Si prega di riprovare. Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;
				case 4: // UPLOAD_ERR_NO_FILE
					$this->Session->setFlash('Nessun file caricato. Selezionare un file prima di procedere con il caricamento', 'flash_error');
					return;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$this->Session->setFlash('Errore interno (6). Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;
				case 7: // UPLOAD_ERR_CANT_WRITE 
					$this->Session->setFlash('Errore interno (7). Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;
				case 8: // UPLOAD_ERR_EXTENSION
					$this->Session->setFlash('Errore interno (8). Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;
				default:
					$this->Session->setFlash('Errore interno ('.$f['error'].'). Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;		
			}

			$inputFileType = PHPExcel_IOFactory::identify($f['tmp_name']);
			if(!in_array($inputFileType, array('Excel2007', 'Excel5', 'Excel2003XML', 'OOCalc'))) {
				// file not properly recognized
				$this->Session->setFlash('Formato file non supportato', 'flash_error');
				return;
			}
		
			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
			$cacheSettings = array( 'memoryCacheSize' => '512MB');
			$cacheEnabled = PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
		
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);  
			$objReader->setReadDataOnly(true); 
		
			$objPHPExcel = $objReader->load($f['tmp_name']); // potentially it can be extremely slow ...
			//$objPHPExcel = PHPExcel_IOFactory::load($file_path); 

			$objWorksheet = $objPHPExcel->setActiveSheetIndex($sheet_index=0); 
			$highestRow = $objWorksheet->getHighestRow();
			
			// read data
			$addebiti = array();
			$currRow = 1;
			$numEmptyCells = 0;
			$cols = array(0 => 'cliente_id', 2 => 'importo');
			$numCols = sizeof($cols);
			while(true) {
				$currRow++;
				$numEmptyCells = 0;
				$currRecord = array();
				
				foreach($cols as $k => $v) {
					
					$currRecord[ $v ] = $objWorksheet->getCellByColumnAndRow($k, $currRow)->getValue();
					if( empty($currRecord[ $v ]) ) $numEmptyCells++;
				}
				
				if( $numEmptyCells == $numCols ) {
					// empty row. stop
					break;
				}
				$addebiti[] = $currRecord;
			}
			
			// valida gli addebiti
			$errors = array();
			
			$res = $this->Addebito->Cliente->find('all', array(
				'recursive' => -1,
				'fields' => array('id', 'tipo_metodo_pagamento_attivo_id', 'metodo_pagamento_attivo_id'),
				'contain' => array(
					'Addebito' => array( // verifico se ci sono già addebiti (sia confermati, sia non) per lo stesso periodo
						'fields' => array('id'),
						'conditions' => array(
							'Addebito.mese' => $this->request->data['Addebito']['mese'],
							'Addebito.anno' => $this->request->data['Addebito']['anno'],
						)
					)				
				)
			));
			foreach($res as $r) $clienti[ $r['Cliente']['id'] ] = $r;
			
			for($i=0;$i<sizeof($addebiti);$i++) {
				
				if(!empty($addebiti[$i]['importo'])) {
					$addebiti[$i]['importo'] = (float) str_replace(",", ".", $addebiti[$i]['importo']);
				}
				
				if( empty($addebiti[$i]['cliente_id']) ) $errors[] = "A".($i+2).': cliente mancante';
				elseif( !isset($clienti[ $addebiti[$i]['cliente_id'] ]) ) $errors[] = "A".($i+2).': cliente sconosciuto';
				
				if( !empty($clienti[ $addebiti[$i]['cliente_id'] ]['Addebito']) ) {
					$errors[] = "A".($i+2).': cliente con saldo già caricato per il periodo scelto';
				}
				
				if( empty($addebiti[$i]['importo']) ) $errors[] = "C".($i+2).': importo mancante';
				elseif( is_nan($addebiti[$i]['importo']) ) $errors[] = "C".($i+2).": l'importo specificato non è un numero";	
			
				
			} 
			
			if( !empty($errors) ) {
				
				if( sizeof($errors) > 50 ) {
					array_splice($errors, 50);
					$errStr = implode("<br/>", $errors).'<br/>(continua ...)';
				}
				else $errStr = implode("<br/>", $errors);
				
				$this->Session->setFlash("<b>Errore durante l'eborazione</b><br/>".$errStr ,'flash_error');
				return;
			}
			
			// aggiungi i dati mancanti e salva a db
			for($i=0;$i<sizeof($addebiti);$i++) {
				$addebiti[$i]['type'] = RICORRENTE_NON_CONFERMATO;
				$addebiti[$i]['mese'] = $this->request->data['Addebito']['mese'];
				$addebiti[$i]['anno'] = $this->request->data['Addebito']['anno'];
				$addebiti[$i]['active'] = 1; // mi serve a 1 per visualizzarle
				$addebiti[$i]['last_payment_ok'] = -1;
				
				$tipo_metodo_pagamento = $clienti[ $addebiti[$i]['cliente_id'] ]['Cliente']['tipo_metodo_pagamento_attivo_id'];
				$metodo_pagamento = $clienti[ $addebiti[$i]['cliente_id'] ]['Cliente']['metodo_pagamento_attivo_id'];
				
				switch($tipo_metodo_pagamento) {
					
					case CARTA:
						$addebiti[$i]['carta_id'] = $metodo_pagamento;
						break;
					case RID:
						$addebiti[$i]['rid_id'] = $metodo_pagamento;
						break;
					case BONIFICO:
						$addebiti[$i]['bonifico_id'] = $metodo_pagamento;
						break;
					case CONTANTI:
						$addebiti[$i]['contante_id'] = $metodo_pagamento;
						break;
					case PROCEDURA_LEGALE:
						$addebiti[$i]['legale_id'] = $metodo_pagamento;
						break;
					
					// altrimenti non viene valorizzato (la spesa non sarà gestita fino a quanto non sarà attivato un metodo di pagamento
					
				} 
			}
			
			if( $this->Addebito->saveAll($addebiti) ) {
				$this->redirect(array('action' => 'conferma_caricamento'));
			}
			else {
				$this->Session->setFlash('Si è verificato un errore', 'flash_error');
			}
		}
	}

	/**
	 * 
	 */
	public function conferma_caricamento() {
		
		// NOTA: non posso caricarle direttamente perchè sono nell'ordine delle migliaia e va in
		// timeout sulla view. Uso datatables (DRY!)
		
		$n = $this->Addebito->find('count', array('conditions' => array('type' => RICORRENTE_NON_CONFERMATO)));
		if($n == 0) {
			$this->Session->setFlash('Non ci sono spese ricorrenti da confermare', 'flash_ok');
			$this->redirect('/');
		}
		
		if( $this->request->is('post') ) {
			
			$op = $this->request->data['Addebito']['op'];
			if( $op == CONFERMA) {
				
				// ottieni una delle spese da confermare per determinare mese e anno
				$addebito = $this->Addebito->find('first', array(
					'conditions' => array(
						'Addebito.type' => RICORRENTE_NON_CONFERMATO
					),
					'recursive' => -1
				));
				$mese = $addebito['Addebito']['mese'];
				$anno = $addebito['Addebito']['anno'];
				
				$transaction_ok = true;
				$dbo1 = $this->Addebito->getDataSource();
				$dbo1->begin();
				
				// 1. setta come inattive tutte le spese ricorrenti (in modo da disattivare quelle attive del precedente periodo di esazione)
				$transaction_ok &= $this->Addebito->updateAll(
					array(
						'Addebito.active' => 0
					), 
					array(
						'Addebito.type' => RICORRENTE
					)
				);
				// 2. setta come ricorrenti e attive tutte le spese ancora da confermare
				$transaction_ok &= $this->Addebito->updateAll(
					array(
						'Addebito.type' => RICORRENTE,
						'Addebito.active' => 1
					), 
					array(
						'Addebito.type' => RICORRENTE_NON_CONFERMATO,
						'Addebito.mese' => $mese,
						'Addebito.anno' => $anno
					)
				);
				
				if($transaction_ok) {
					$dbo1->commit();
					$this->Session->setFlash("Spese ricorrenti confermate correttamente", 'flash_ok');
				}
				else {
					$dbo1->rollback();
					$this->Session->setFlash("Errore durante conferma spese ricorrenti", 'flash_error');
				}
			}
			else {
				if( $this->Addebito->deleteAll(
					array(
						'Addebito.type' => RICORRENTE_NON_CONFERMATO
					)
				) ) {
					$this->Session->setFlash("Procedura caricamento annullata correttamente", 'flash_ok');
				}
				else {
					$this->Session->setFlash("Errore durante cancellazione procedura caricamento", 'flash_error');
				}
			}
			
			$this->redirect('/');
			
		}
	}
	
	/**
	 * 
	 */
	public function view($id) {
		
		$a = $this->Addebito->findById($id);
		if(empty($a)) throw new NotFoundException('Spesa non trovata');
		
		if( !empty($this->request->data) ) { // per aggiornare l'importo della spesa riciclo questo metodo per non dover ri-generare ACO/AROS che è una palla ...
			$importo = $this->request->data['Addebito']['importo'];
			// valida l'importo
			$importo = str_replace(',', '.', $importo);
			$tokens = explode('.', $importo);
			if( sizeof($tokens) != 2 || !ctype_digit($tokens[0]) || !ctype_digit($tokens[1]) || strlen($tokens[1]) != 2 ) {
				$this->Session->setFlash("Importo non valido (l'importo deve contenere due cifre decimali", 'flash_error');
			}
			else {
				$res = $this->Addebito->save(array(
					'id' => $id,
					'importo' => $importo
				));
				if($res) $this->Session->setFlash("Importo aggiornato correttamente", 'flash_ok');
				else $this->Session->setFlash("Errore, si prega di riprovare", 'flash_error');
			}
			$this->redirect( array('action' => 'view', $id) );
		}
		
		$this->set('a', $a);
	}

	/**
	 * 
	 */
	public function confirm_delete($id) {
		
		$a = $this->Addebito->findById($id);
		
		if(empty($a)) throw new NotFoundException('Spesa non trovata');
		
		$this->set('a', $a);
		
	}
	
	/**
	 * 
	 */
	public function delete($id) {
		
		$a = $this->Addebito->findById($id);
		if( empty($a) ) throw new NotFoundException('Spesa non trovata');
		if( $this->Addebito->delete($id) ) {
			$this->Session->setFlash('Spesa rimossa correttamente', 'flash_ok');
		}
		else {
			$this->Session->setFlash('Errore durante cancellazione', 'flash_error');
		}
		
		$this->redirect( array('controller' => 'clienti', 'action' => 'view', $a['Cliente']['id']) );
	}

	/**
	 * 
	 */
	public function report($target) {
		
		$addebiti = array();
		
		if($target == 'carte-ko') {
			$options['contain'] = array(
				'Cliente' => array(
					'fields' => array('id', 'NOME', 'COGNOME') // i virtual fields non funzionano con contain ...
				)
			);

			// condizioni
			$options['conditions']['active'] = 1;
			
			$options['conditions']['type'] = RICORRENTE;
			$options['conditions']['last_payment_ok'] = 0;
			$options['joins'] = array(
				array(
					'table' => 'carte_di_credito',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.carta_id = c.id'
					)
				),
			);
			$options['order'] = array('Addebito.cliente_id');
			
			$addebiti = $this->Addebito->find('all', $options);
		
		}
		
		$this->PhpExcel->createWorksheet();
		// define table cells
		$table = array(
			array('label' => __('ID cliente')/*, 'filter' => true*/),
			array('label' => __('Cliente')/*, 'filter' => true*/),
			array('label' => __('Importo')),
			array('label' => __('Mese')/*, 'width' => 50, 'wrap' => true*/),
			array('label' => __('Anno')),
			array('label' => __('Tipo')),
			array('label' => __('Stato'))
		);
		
		$status = array(
			'1' => 'OK',
			'0' => 'KO',
			'-1' => 'NUOVO'
		);

		// add heading with different font and bold text
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));
		// add data
		foreach ($addebiti as $a) {
			
			if( !empty($a['Addebito']['carta_id']) ) $metodo = 'CARTA';
			else if( !empty($a['Addebito']['rid_id']) ) $metodo = 'RID';
			else if( !empty($a['Addebito']['bonifico_id']) ) $metodo = 'BONIFICO';
			else if( !empty($a['Addebito']['contante_id']) ) $metodo = 'CONTANTI';
			else if( !empty($a['Addebito']['legale_id']) ) $metodo = 'LEGALE';
			else $metodo = '';
			
			$this->PhpExcel->addTableRow(array(
				$a['Cliente']['id'],
				$a['Cliente']['COGNOME'].' '.$a['Cliente']['NOME'],
				$a['Addebito']['importo'],
				$a['Addebito']['mese'],
				$a['Addebito']['anno'],
				$metodo,
				$status[ $a['Addebito']['last_payment_ok'] ]
			));
		}

		// close table and output
		$this->PhpExcel->addTableFooter()->output();
	}








	

	



	

	

	

	

	//genera report globale di tutti gli eventi intercorsi tra $date_start (DD-MM-YYY) e $date_stop (DD-MM-YYYY)
	function global_report($date_start, $date_stop)
	{
		$tokens_start = explode('-', $date_start);
		$tokens_stop = explode('-', $date_stop);

		$timestamp_start = mktime(0, 0, 0, $tokens_start[1], $tokens_start[0], $tokens_start[2], -1);
		$timestamp_stop = mktime(23, 59, 59, $tokens_stop[1], $tokens_stop[0], $tokens_stop[2], -1);

		$this->loadModel('Payment');
		$this->loadModel('Customer');

		//get users and build an associative array of them
		$this->loadModel('User');
		$u = $this->User->find('all');
		foreach($u as $user) $users[$user['User']['id']] = $user;

		//ottieni tutti i contratti che sono stati inseriti nel periodo
		$sent_customers = $this->Customer->find(
			'all', 
			array(
				'conditions' => array(
					'Customer.sent >=' => $timestamp_start,
					'Customer.sent <=' => $timestamp_stop
				),
				'order' => array(
					'Customer.sent',
					'Customer.nome'
				)
			)
		);

		//ottieni tutti i clienti che si sono attivati nel periodo
		$signed_customers = $this->Customer->find(
			'all', 
			array(
				'conditions' => array(
					'Customer.signed >=' => $timestamp_start,
					'Customer.signed <=' => $timestamp_stop
				),
				'order' => array(
					'Customer.sent',
					'Customer.nome'
				)
			)
		);

		//ottieni tutti i tentativi di attivazione del contratto non completati o non andati a buon fine nel periodo
		$activation_attempts = $this->Payment->find(
			'all',
			array(
				'conditions' => array(
					'Charge.type' => 'primo pagamento',
					'Payment.created >=' => $timestamp_start,
					'Payment.created <=' => $timestamp_stop,
					'Payment.esito_banca <>' => 'OK'
				),
				'order' => array(
					'Payment.charge_id', 'Charge.customer_id', 'Payment.created'
				)
			)
		);

		//ottieni tutte le spese ricorrenti caricate nel periodo
		$recurrent_charges = $this->Charge->find(
			'all',
			array(
				'conditions' => array(
					'Charge.type' => 'ricorrente',
					'Charge.curr_payment_method' => 1,
					'Charge.created >=' => $timestamp_start,
					'Charge.created <=' => $timestamp_stop
				)
			)
		);

		//ottieni tutti i pagamenti ricorrenti ok elaborati nel periodo
		$payments_ok = $this->Payment->find(
			'all',
			array(
				'conditions' => array(
					'Charge.type' => 'ricorrente',
					'Charge.curr_payment_method' => 1,
					'Payment.created >=' => $timestamp_start,
					'Payment.created <=' => $timestamp_stop,
					'Payment.esito_banca' => 'OK'
				)
			)
		);

		//ottieni tutti i pagamenti ricorrenti ko elaborati nel periodo
		$payments_ko = $this->Payment->find(
			'all',
			array(
				'conditions' => array(
					'Charge.type' => 'ricorrente',
					'Charge.curr_payment_method' => 1,
					'Payment.created >=' => $timestamp_start,
					'Payment.created <=' => $timestamp_stop,
					'Payment.esito_banca <>' => 'OK'
				),
				'order' => array(
					'Payment.charge_id', 'Payment.created'
				)
			)
		);

		/*formato report:
			CSV -> una riga per ogni record (i campi sono vuoti se l'evento non li richiede)
			data;tipo_evento;id_cliente;id_spesa;id_pagamento;id_transazione;operatore;
		*/ 

		$myFile = '/reports/global_report.csv';
		$fh = fopen(getcwd().$myFile, 'w');

		if($fh === FALSE) return;

		$file_content = "";

		$file_content .= "Data;";
		$file_content .= "Tipo evento;";
		$file_content .= "ID cliente;";
		$file_content .= "ID spesa;";
		$file_content .= "ID pagamento;";
		$file_content .= "ID transazione;";
		$file_content .= "Operatore;\r\n";
		
		foreach($sent_customers as $s)
		{
			$file_content .= 
				$s['Customer']['sent'].";".
				"c1;".
				$s['Customer']['id'].";".
				";".
				";".
				";".
				( (empty($s['Customer']['user'])) ? ";" : $users[$s['Customer']['user']]['User']['username'].";" ). //i clienti prima della modifica non hanno il campo user settato!
				"\r\n";		
		}
		foreach($signed_customers as $s)
		{
			$file_content .= 
				$s['Customer']['signed'].";".
				"c2;".
				$s['Customer']['id'].";".
				";".
				";".
				";".
				";".
				"\r\n";
		}
		foreach($activation_attempts as $s) //uncompleted or ko
		{
			$file_content .= 
				$s['Charge']['created'].";".
				"c3;".
				$s['Charge']['customer_id'].";".
				$s['Charge']['id'].";".
				$s['Payment']['id'].";".
				";".
				";".
				"\r\n";
		}
		foreach($recurrent_charges as $s) 
		{
			$file_content .= 
				$s['Charge']['created'].";".
				"s1;".
				$s['Charge']['customer_id'].";".
				$s['Charge']['id'].";".
				";".
				";".
				";".
				"\r\n";
		}
		foreach($payments_ok as $s) 
		{
			$file_content .= 
				$s['Payment']['created'].";".
				"p1;".
				$s['Charge']['customer_id'].";".
				$s['Payment']['charge_id'].";".
				$s['Payment']['id'].";".
				$s['Payment']['transaction_id'].";".
				";".
				"\r\n";
		}
		foreach($payments_ko as $s) 
		{
			$file_content .= 
				$s['Payment']['created'].";".
				"p2;".
				$s['Charge']['customer_id'].";".
				$s['Payment']['charge_id'].";".
				$s['Payment']['id'].";".
				$s['Payment']['transaction_id'].";".
				";".
				"\r\n";
		}

		fwrite($fh, $file_content);
		fclose($fh);

		$this->redirect( 'http://zolle.impronta48.it/app/webroot/reports/global_report.csv' );
	}


	

	

	



	

	

	


	

	//genera il risultato dei pagamenti andati a buon fine come file CSV (comma separated values)
	function _get_report_file($payments)
	{
		$myFile = '/reports/report.csv';
		$fh = fopen(getcwd().$myFile, 'w');

		$file_content = "";
		
		$file_content .= "Elenco pagamenti eseguiti con successo;\r\n";
		$file_content .= "ID spesa;";
		$file_content .= "ID transazione;";
		$file_content .= "ID cliente;";
		$file_content .= "Importo;";
		$file_content .= "Mese esazione;";
		$file_content .= "Anno esazione;\r\n";
		
		foreach($payments as $payment)
		{
			if($payment['Payment']['esito_banca'] == 'OK') //in output ci devono essere solo i pagamenti andati a buon fine
			{
				$file_content .= $payment['Payment']['charge_id'].";";
				$file_content .= $payment['Payment']['transaction_id'].";";
				$file_content .= $payment['Charge']['customer_id'].";";
				$file_content .= $payment['Charge']['importo'].";";
				$file_content .= $payment['Charge']['mese'].";";
				$file_content .= $payment['Charge']['anno'].";\r\n";		
			}
		}

		fwrite($fh, $file_content);
		fclose($fh);
	}
}

// NOTA: NON chiudere il tag php altrimenti l'upload di file > pochi byte fallisce !!!

