<?php

require_once APP . 'Vendor' . DS . 'PHPExcel.php'; // mandatory. This is the (suggested) cake way to include vendors

class ClientiController extends AppController {

	var $name = "Clienti";

	public $uses = array('Cliente', 'ClienteZolla', 'Contratto', 'MetodoPagamento');

	var $components = array('PhpExcel', 'Paginator');
	
	public function beforeFilter() {
		
		parent::beforeFilter();
		
		$user = $this->Session->read('Auth.User');
		if($user && $user['group_id'] == 1) {
			$this->Auth->allow('report_metodi_pagamento', 'sollecito_attivazione_metodi_pagamento', 'pulisci_metodi_pagamento');
		}
		
		$this->Auth->allow(
			'aggiorna_rest', 
			'attiva_metodo_pagamento', // viene attivato direttamente dal cliente in fase di autorizzazione su adyen
			'disattiva_metodo_pagamento' // viene attivato direttamente dal cliente in fase di autorizzazione su adyen
		);
		
	}

	public function pulisci_metodi_pagamento($id=null) {
		
		// blocco temporaneo
		if( strpos(Router::url('/', true), "staging") === false ) {
			throw new BadRequestException("Abilitato solo su staging");
		}
		
		$this->layout = 'default';
		
		shell_exec( APP. "Console/cake pulisci_clienti " . ($id ? $id : '') . " > /dev/null 2>/dev/null &");
	}

	public function sollecito_attivazione_metodi_pagamento() {
		$this->layout = 'default';
		
		shell_exec( APP. "Console/cake invia_solleciti_attivazione > /dev/null 2>/dev/null &");
	}

	public function report_metodi_pagamento() {
		$res = $this->Cliente->getMetodiPagamentoDaNotificare($report = true);
		
		$this->PhpExcel->createWorksheet();
		// define table cells
		$table = array(
			array('label' => __('ID cliente')/*, 'filter' => true*/),
			array('label' => __('Metodo')/*, 'filter' => true*/),
			array('label' => __('Id metodo pagamento')),
			array('label' => __('Id contratto')/*, 'width' => 50, 'wrap' => true*/),
			array('label' => __('Data creazione')),
			array('label' => __('Data ultimo sollecito')),
			array('label' => __('Num solleciti inviati'))
		);
		
		// add heading with different font and bold text
		$this->PhpExcel->addTableHeader($table, array('name' => 'Cambria', 'bold' => true));
		// add data
		foreach ($res as $id_cliente => $r) {
			
			if($r['tipo'] == 'carta') {
				$this->PhpExcel->addTableRow(array(
					$id_cliente,
					'CARTA',
					$r['metodo']['id'],
					$r['metodo']['id_contratto'],
					$r['metodo']['created'],
					$r['metodo']['data_ultimo_sollecito_attivazione'],
					$r['metodo']['num_solleciti_inviati'],
				));
			}
			else {
				$this->PhpExcel->addTableRow(array(
					$id_cliente,
					'SEPA',
					$r['metodo']['id'],
					$r['metodo']['id_contratto_rid'],
					$r['metodo']['created'],
					$r['metodo']['data_ultimo_sollecito_attivazione'],
					$r['metodo']['num_solleciti_inviati'],
				));
			}
			
		}

		// close table and output
		$this->PhpExcel->addTableFooter()->output();
	}
	
	/**
	 * in certe situazioni zolle ha necessità di aggiornare immediatamente gli articoli e le tabelle associate.
	 * Invoca il comando della shell di cake. Il risultato dell'operazione verrà scritto su file di log
	 */
	public function aggiorna_definizione() {
		$this->layout = 'default';
		shell_exec( APP. "Console/cake sync_db_locale_con_zolla sync_clienti > /dev/null 2>/dev/null &");
	}
	
	/**
	 * 
	 */
	public function dashboard() {
		
		// ottieni il numero di carte scadute
		$carte_scadute_num = $this->Cliente->CartaDiCredito->find('count', array(
			'conditions' => array(
				"DATE_FORMAT( CONCAT( SUBSTR(scadenza_pan,1,4), '-', SUBSTR(scadenza_pan,5,2), '-15'), '%Y-%m-%d %H:%i:%s') <" => date('Y-m-d H:i:s')
			)
		));
		
		// ottieni il numero di contratti carta non ancora completati
		$carte_non_attive_num = $this->Cliente->CartaDiCredito->find('count', array(
			'conditions' => array(
				'signed' => NULL
			)
		));
		
		// ottieni il numero di contratti rid non ancora completati
		$rid_non_attivi_num = $this->Cliente->AutorizzazioneRid->find('count', array(
			'conditions' => array(
				'rid_activated' => NULL
			)
		));
		
		// ottieni il numero di spese carta ko
		$options = array();
		$options['conditions']['active'] = 1;
		$options['conditions']['type'] = RICORRENTE;
		$options['conditions']['last_payment_ok'] = 0;
		$options['conditions']['blocked'] = NULL;
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
		$spese_carta_ko_num = $this->Cliente->Addebito->find('count', $options);
		
		// ottieni il numero di spese carta bloccate
		$options = array();
		$options['conditions']['active'] = 1;
		$options['conditions']['type'] = RICORRENTE;
		$options['conditions']['blocked'] = 1;
		$spese_carta_bloccate_num = $this->Cliente->Addebito->find('count', $options);
		
		// ottieni il numero di spese carta non pagabili
		$options = array();
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
					'c.signed' => NULL
				)
			),
		);
		$spese_carta_non_pagabili_num = $this->Cliente->Addebito->find('count', $options);
		
		// ottieni il numero di spese carta non ancora pagate (ma pagabili)
		$options = array();
		$options['conditions']['type'] = RICORRENTE;
		$options['conditions']['last_payment_ok'] = -1;
		$options['conditions']['blocked'] = NULL;
		//$options['conditions']['excluded'] = NULL;
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
		$spese_carta_non_ancora_pagate_num = $this->Cliente->Addebito->find('count', $options);
		
		// ottieni il numero di spese rid non pagabili
		$options = array();
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
		$spese_rid_non_pagabili_num = $this->Cliente->Addebito->find('count', $options);
		
		
		$this->set(compact(
			'carte_scadute_num',
			'carte_non_attive_num',
			'rid_non_attivi_num',
			'spese_carta_ko_num',
			'spese_carta_bloccate_num',
			'spese_carta_non_pagabili_num',
			'spese_carta_non_ancora_pagate_num',
			'spese_rid_non_pagabili_num'
		));
		
		
	}
	
	public function oops() {
		$this->layout = 'mangio';
	}
	
	/**
	 */
	public function profilo() {
		
		$this->logInfo("", 'Il cliente ha visitato il suo profilo', null);
		
		$this->layout = 'mangio';
		
		$this->set('subheaderBkg', 'mangio/bg-03.jpg');
		
		// TODO: aggiungere a contain tutti i metodi di pagamento attivi per cliente fatturazione
		$contratto = $this->Contratto->find('first', array(
			'conditions' => array(
				'Contratto.id' => $this->Session->read('contratto_id')
			),
			'contain' => array(
				'Cliente' => array('Indirizzo'),
				'ClienteFatturazione'
			)
		));
		
		$this->set('contratto', $contratto);
		
		$consegne = $this->_getConsegne($target='profilo');
		$this->set('consegne', $consegne);
	}
	
	/**
	 */
	public function fatture() {
		
		$this->logInfo("", 'Il cliente ha visitato la sezione fatture');
		
		$this->layout = 'mangio';
		
		$this->set('subheaderBkg', 'mangio/bg-03.jpg');
		
		$clienteLoggato = $this->Session->read('clienteLoggato');
		if( !$clienteLoggato['isClienteFatturazione'] ) {
			throw new UnauthorizedException(__('Non sei autorizzato a procedere'));
		}
		
		$folder_fatture = APP . 'fatture/';

		//leggi le fatture del cliente.  
		//Formato: <data>-<idcliente>-<idfattura>.pdf (ad esempio: 20130321-924-13466.pdf) 
		//Visibilità per il cliente: 3 mesi
		//Nuovo formato da implementare:
		// __2851_FTV_175766_2015_20001_10979_4032015.PDF
		// 2851: id cliente fatturazione
		// 4032015: data
		$fatture = array();
		
		// leggo le fatture degli ultimi 3 mesi
		$months = array(
			date('Ym'),
			date('Ym', strtotime('-1 month')),
			date('Ym', strtotime('-2 month'))
		);		
		$fatture = array();
		foreach($months as $month) {
			$fatture = array_merge($fatture, glob($folder_fatture . $month . DS . '__'.$clienteLoggato['id'].'_FTV*'));
		}
		$fatture_vis = array();	
		if(!empty($fatture)) {	
			foreach($fatture as $fattura) {
				$tokens = explode('/', $fattura);
				$tokens = explode('.', $tokens[sizeof($tokens)-1]);
				$tokens = explode('_', $tokens[0]);
				
				$dataFattura = $tokens[sizeof($tokens)-1];
				
				$year = substr($dataFattura, strlen($dataFattura)-4);
				$month = substr($dataFattura, strlen($dataFattura)-6, 2);
				$day = substr($dataFattura, 0, strlen($dataFattura)-6);
				
				$nome_fattura = substr( $fattura, strrpos($fattura, '/')+1 );
				$fatture_vis[] = array(
					'nome' => $nome_fattura,
					'giorno' => $day,
					'mese' => $month,
					'anno' => $year
				);	
			}
		}
		
		// aggiungi anche i riepiloghi mensili degli ultimi 3 mesi
		$riepiloghi = array();
		foreach($months as $month) {
			$riepiloghi[$month] = glob($folder_fatture . $month . DS . '__'.$clienteLoggato['id'].'_RIMEN1*');
		}
		$riepiloghi_vis = array();	
		if(!empty($riepiloghi)) {	
			foreach($riepiloghi as $month => $riepiloghi_mese) {
				foreach($riepiloghi_mese as $riepilogo) {
					$anno = substr($month, 0, 4);
					$mese = substr($month, 4);
					
					$nome_riepilogo = substr( $riepilogo, strrpos($riepilogo, '/')+1 );
					$riepiloghi_vis[] = array(
						'nome' => $nome_riepilogo,
						'mese' => $mese,
						'anno' => $anno
					);
				}					
			}
		}
		
		usort($fatture_vis, 'cmpFatture'); // ordina per data decrescente
		usort($riepiloghi_vis, 'cmpRiepiloghi'); // ordina per data decrescente
		$this->set('fatture', $fatture_vis);
		$this->set('riepiloghi', $riepiloghi_vis);
	}

	/**
	 * 
	 */
	function download_fattura($annomese, $filename) {
		
		$this->logInfo("" , 'Il cliente ha scaricato una fattura', [
			'anno_mese' => $annomese,
			'filename' => $filename
		]);
		
		// di default il nome file viene sempre passato da cake senza estensione ma per sicurezza lo tolgo sempre
		$filename = explode('.', $filename);
		$filename = $filename[0];
		
		$this->layout = 'mangio';
		
		$this->set('subheaderBkg', 'mangio/bg-03.jpg');
		
		$clienteLoggato = $this->Session->read('clienteLoggato');
		if( !$clienteLoggato['isClienteFatturazione'] ) {
			throw new UnauthorizedException(__('Non sei autorizzato a procedere'));
		}
		
		$folder_fatture = APP . 'fatture/';

		//controlla che il file esista
		$ext = '';
		if(file_exists($folder_fatture . $annomese . DS . $filename . '.pdf')) {
			$ext = 'pdf';
		}
		if(file_exists($folder_fatture . $annomese . DS . $filename . '.PDF')) {
			$ext = 'PDF';
		}
		
		if( empty($ext) ) {
			throw new NotFoundException(__('La fattura richiesta non esiste'));
		}
		
		//verifica che il cliente sia abilitato a visualizzare il file
		$tokens = explode('_', $filename);
		if( $tokens[2] != $clienteLoggato['id'] ) {
			throw new UnauthorizedException(__('Non sei autorizzato a procedere'));
		}

		//verifica che la fattura sia nell'arco di visibilità dei 3 mesi
		$year = substr($annomese, 0, 4);
		$month = substr($annomese, 4);
			
		if( strtotime("01-$month-$year") < strtotime("-3 month") ) {
			throw new NotFoundException(__('La fattura richiesta non è disponibile'));
		}
		
		$this->response->file($folder_fatture . $annomese . DS. $filename . '.' . $ext);
		// Return response object to prevent controller from trying to render
		// a view
		return $this->response;
	}

	/**
	 */
	public function index() {
		
		if( $this->request->is('post') || $this->request->is('put') ) {
			
			if( empty($this->request->data['Cliente']['id']) ) {
				$this->Session->setFlash('Nessun id cliente inserito', 'flash_error');
				return;
			}
			
			$this->redirect( array('action' => 'view', $this->request->data['Cliente']['id']) ); // ci penserà il metodo chiamato a verificare che il cliente esista 
			
		}
		
	}
	
	/**
	 */
	public function view($id) {
		$cliente = $this->Cliente->find('first', array( // devo tirare su anche il secondo cliente associato al contratto ...
			'conditions' => array(
				'id' => $id
			),
			'contain' => array(
				'Recapito',
				'ContrattoPerCuiRiceveSpesa' => array(
					'Cliente' => array(
						'fields' => array('id', 'displayName')
					),
					'ClienteFatturazione' => array(
						'fields' => array('id', 'displayName')
					)
				),
				'ContrattoPerCuiRiceveFattura' => array(
					'Cliente' => array(
						'fields' => array('id', 'displayName')
					),
					'ClienteFatturazione' => array(
						'fields' => array('id', 'displayName')
					)
				),
				'Addebito',
				'CartaDiCredito',
				'AutorizzazioneRid',
				'Bonifico',
				'Contante',
				'ProceduraLegale'
			)
		));
		if(empty($cliente)) {
			throw new NotFoundException('Il cliente '.$id.' non esiste!');
		}

		$contratti_attivi = array();
		$contratti_chiusi = array();
		
		$contratti_fatt_attivi = array();
		$contratti_fatt_chiusi = array();
		
		foreach($cliente['ContrattoPerCuiRiceveSpesa'] as $c) {
			if(empty($c['data_chiusura'])) $contratti_attivi[] = $c;
			else $contratti_chiusi[] = $c;
		}
		foreach($cliente['ContrattoPerCuiRiceveFattura'] as $c) {
			if(empty($c['data_chiusura'])) $contratti_fatt_attivi[] = $c;
			else $contratti_fatt_chiusi[] = $c;
		}
		
		$metodi_pagamento = $this->MetodoPagamento->find('list');
		
		$this->set(compact('cliente', 'contratti_attivi', 'contratti_chiusi', 'contratti_fatt_attivi', 'contratti_fatt_chiusi', 'metodi_pagamento'));
	}
	
	/**
	 * 
	 */
	public function attiva_metodo_pagamento($cliente_id, $tipo_metodo_pagamento_id, $metodo_pagamento_id) {
		$requested = false;
		if (!empty($this->request->params['requested'])) {
            $requested = true;
        }
        
        // verifica che il metodo di pagamento esista e sia associato al cliente scelto e non sia disattivato in modo permanente
        $metodoPagamento = null;
        switch($tipo_metodo_pagamento_id) {
			case CARTA:
				$metodoPagamento = $this->Cliente->CartaDiCredito->find('first', array(
					'conditions' => array(
						'CartaDiCredito.id' => $metodo_pagamento_id,
						'CartaDiCredito.cliente_id' => $cliente_id,
						'CartaDiCredito.data_disattivazione' => null
					)
				));
				break;
			case RID:
				$metodoPagamento = $this->Cliente->AutorizzazioneRid->find('first', array(
					'conditions' => array(
						'AutorizzazioneRid.id' => $metodo_pagamento_id,
						'AutorizzazioneRid.cliente_id' => $cliente_id,
						'AutorizzazioneRid.data_disattivazione' => null
					)
				));
				break;
			case BONIFICO:
				$metodoPagamento = $this->Cliente->Bonifico->find('first', array(
					'conditions' => array(
						'Bonifico.id' => $metodo_pagamento_id,
						'Bonifico.cliente_id' => $cliente_id,
						'Bonifico.data_disattivazione' => null
					)
				));
				break;
			case CONTANTI:
				$metodoPagamento = $this->Cliente->Contante->find('first', array(
					'conditions' => array(
						'Contante.id' => $metodo_pagamento_id,
						'Contante.cliente_id' => $cliente_id,
						'Contante.data_disattivazione' => null
					)
				));
				break;
			case PROCEDURA_LEGALE:
				$metodoPagamento = $this->Cliente->ProceduraLegale->find('first', array(
					'conditions' => array(
						'ProceduraLegale.id' => $metodo_pagamento_id,
						'ProceduraLegale.cliente_id' => $cliente_id,
						'ProceduraLegale.data_disattivazione' => null
					)
				));
				break;
		}
		
		if(empty($metodoPagamento)) {
			
			if($requested) {
				return false;
			}
			else {
				$this->Session->setFlash('Metodo di pagamento non trovato o già disattivato in modo permanente', 'flash_error');
				$this->redirect( $this->referer() );
			}
			
		}
		
		$transaction_ok = true;
		$dbo1 = $this->Cliente->getDataSource();
		$dbo2 = $this->Cliente->Addebito->getDataSource();
		$dbo1->begin();
		$dbo2->begin();
		
		$this->Cliente->id = $cliente_id;
		$transaction_ok &= $this->Cliente->save(array(
			'id' => $cliente_id,
			'tipo_metodo_pagamento_attivo_id' => $tipo_metodo_pagamento_id,
			'metodo_pagamento_attivo_id' => $metodo_pagamento_id,
		));
		// aggiorna il metodo di pagamento di tutte le spese attive che non hanno avuto successo
		$fields['carta_id'] = null;
		$fields['rid_id'] = null;
		$fields['bonifico_id'] = null;
		$fields['contante_id'] = null;
		$fields['legale_id'] = null;
		switch($tipo_metodo_pagamento_id) {
			case CARTA:
				$fields['carta_id'] = $metodo_pagamento_id;
				break;
			case RID:
				$fields['rid_id'] = $metodo_pagamento_id;
				break;
			case BONIFICO:
				$fields['bonifico_id'] = $metodo_pagamento_id;
				break;
			case CONTANTI:
				$fields['contante_id'] = $metodo_pagamento_id;
				break;
			case PROCEDURA_LEGALE:
				$fields['legale_id'] = $metodo_pagamento_id;
				break;
		}
		$transaction_ok &= $this->Cliente->Addebito->updateAll(
			$fields,
			array(
				'Addebito.cliente_id' => $cliente_id,
				'Addebito.active' => 1,
				'Addebito.last_payment_ok <>' => 1,
			)
		);
		
		if($transaction_ok) {
			$dbo1->commit();
			$dbo2->commit();
			$this->Session->setFlash('Metodo di pagamento attivato correttamente', 'flash_ok');
		}
		else {
			$dbo1->rollback();
			$dbo2->rollback();
			$this->Session->setFlash('Errore durante attivazione', 'flash_error');
		}
		
		if($requested) return $transaction_ok;
	
		$this->redirect( $this->referer() );
	}
	
	/**
	 * 
	 */
	public function disattiva_metodo_pagamento($cliente_id, $tipo_metodo_pagamento_id, $metodo_pagamento_id) {
		$requested = false;
		if (!empty($this->request->params['requested'])) {
            $requested = true;
        }
        
        // verifica che il metodo di pagamento esista e sia associato al cliente scelto e non sia già stato disattivato in modo permanente
        $metodoPagamento = null;
        switch($tipo_metodo_pagamento_id) {
			case CARTA:
				$metodoPagamento = $this->Cliente->CartaDiCredito->find('first', array(
					'conditions' => array(
						'CartaDiCredito.id' => $metodo_pagamento_id,
						'CartaDiCredito.cliente_id' => $cliente_id,
						'CartaDiCredito.data_disattivazione' => null
					)
				));
				break;
			case RID:
				$metodoPagamento = $this->Cliente->AutorizzazioneRid->find('first', array(
					'conditions' => array(
						'AutorizzazioneRid.id' => $metodo_pagamento_id,
						'AutorizzazioneRid.cliente_id' => $cliente_id,
						'AutorizzazioneRid.data_disattivazione' => null
					)
				));
				break;
			case BONIFICO:
				$metodoPagamento = $this->Cliente->Bonifico->find('first', array(
					'conditions' => array(
						'Bonifico.id' => $metodo_pagamento_id,
						'Bonifico.cliente_id' => $cliente_id,
						'Bonifico.data_disattivazione' => null
					)
				));
				break;
			case CONTANTI:
				$metodoPagamento = $this->Cliente->Contante->find('first', array(
					'conditions' => array(
						'Contante.id' => $metodo_pagamento_id,
						'Contante.cliente_id' => $cliente_id,
						'Contante.data_disattivazione' => null
					)
				));
				break;
			case PROCEDURA_LEGALE:
				$metodoPagamento = $this->Cliente->ProceduraLegale->find('first', array(
					'conditions' => array(
						'ProceduraLegale.id' => $metodo_pagamento_id,
						'ProceduraLegale.cliente_id' => $cliente_id,
						'ProceduraLegale.data_disattivazione' => null
					)
				));
				break;
		}
		
		if(empty($metodoPagamento)) {
			
			if($requested) {
				return false;
			}
			else {
				$this->Session->setFlash('Metodo di pagamento non trovato o già disattivato in modo permanente', 'flash_error');
				$this->redirect( $this->referer() );
			}
			
		}
		
		$transaction_ok = true;
		$dbo1 = $this->Cliente->getDataSource();
		$dbo2 = $this->Cliente->Addebito->getDataSource();
		$dbo3 = $this->Cliente->CartaDiCredito->getDataSource();
		$dbo4 = $this->Cliente->AutorizzazioneRid->getDataSource();
		$dbo5 = $this->Cliente->Bonifico->getDataSource();
		$dbo6 = $this->Cliente->Contante->getDataSource();
		$dbo7 = $this->Cliente->ProceduraLegale->getDataSource();
		
		$dbo1->begin();
		$dbo2->begin();
		$dbo3->begin();
		$dbo4->begin();
		$dbo5->begin();
		$dbo6->begin();
		$dbo7->begin();
		
		$cliente = $this->Cliente->findById($cliente_id);
		if(empty($cliente)) {
			throw new NotFoundException("Cliente non trovato");
		}
		
		if($cliente['Cliente']['tipo_metodo_pagamento_attivo_id'] == $tipo_metodo_pagamento_id &&
			$cliente['Cliente']['metodo_pagamento_attivo_id'] == $metodo_pagamento_id) {
			// resetta il metodo di pagamento attivo del cliente
			$transaction_ok &= $this->Cliente->save(array(
				'id' => $cliente_id,
				'tipo_metodo_pagamento_attivo_id' => NULL,
				'metodo_pagamento_attivo_id' => NULL,
			));
			// resetta il metodo di pagamento di tutte le spese attive che non hanno avuto successo
			$fields['carta_id'] = null;
			$fields['rid_id'] = null;
			$fields['bonifico_id'] = null;
			$fields['contante_id'] = null;
			$fields['legale_id'] = null;
			$transaction_ok &= $this->Cliente->Addebito->updateAll(
				$fields,
				array(
					'Addebito.cliente_id' => $cliente_id,
					'Addebito.active' => 1,
					'Addebito.last_payment_ok <>' => 1,
				)
			);
		}
		// disattiva in modo permanente il metodo di pagamento
		$rec = [
			'id' => $metodo_pagamento_id,
			'data_disattivazione' => date('Y-m-d H:i:s')
		];
		switch($tipo_metodo_pagamento_id) {
			case CARTA:
				$transaction_ok &= $this->Cliente->CartaDiCredito->save($rec);
				break;
			case RID:
				$transaction_ok &= $this->Cliente->AutorizzazioneRid->save($rec);
				break;
			case BONIFICO:
				$transaction_ok &= $this->Cliente->Bonifico->save($rec);
				break;
			case CONTANTI:
				$transaction_ok &= $this->Cliente->Contante->save($rec);
				break;
			case PROCEDURA_LEGALE:
				$transaction_ok &= $this->Cliente->ProceduraLegale->save($rec);
				break;
		}
		
		
		if($transaction_ok) {
			$dbo1->commit();
			$dbo2->commit();
			$dbo3->commit();
			$dbo4->commit();
			$dbo5->commit();
			$dbo6->commit();
			$dbo7->commit();
			$this->Session->setFlash('Metodo di pagamento disattivato in modo permanente', 'flash_ok');
		}
		else {
			$dbo1->rollback();
			$dbo2->rollback();
			$dbo3->rollback();
			$dbo4->rollback();
			$dbo5->rollback();
			$dbo6->rollback();
			$dbo7->rollback();
			$this->Session->setFlash('Errore durante disattivazione', 'flash_error');
		}
		
		if($requested) return $transaction_ok;
	
		$this->redirect( $this->referer() );
	}
	
	/**
	 *
	 */
	public function add_metodo_pagamento() {
		if($this->request->is('post')) {
			$d = $this->request->data['Cliente'];
			switch($d['tipo']) {
				case CARTA:
					$this->redirect( array('controller' => 'carte_di_credito', 'action' => 'add', $d['cliente_id']) );
					break;
				case RID:
					$this->redirect( array('controller' => 'autorizzazioni_rid', 'action' => 'add', $d['cliente_id']) );
					break;
				case BONIFICO:
					$this->redirect( array('controller' => 'bonifici', 'action' => 'add', $d['cliente_id']) );
					break;
				case CONTANTI:
					$this->redirect( array('controller' => 'contanti', 'action' => 'add', $d['cliente_id']) );
					break;
				case PROCEDURA_LEGALE:
					$this->redirect( array('controller' => 'procedure_legali', 'action' => 'add', $d['cliente_id']) );
					break;
				default:
					$this->Session->setFlash('Metodo di pagamento sconosciuto', 'flash_error');
					$this->redirect( $this->referer() );
			}
		}
		
	}
	
	/**
	 * aggiorna il cliente e i relativi dati (recapiti, ecc...) da zolla, 
	 * se non esiste ancora aggiungilo
	 */
	public function aggiorna($id) {
		
		// controllo esistenza commentato perchè il metodo può essere chiamato anche da fattoria per aggiungere
		// singoli clienti
		/*$cliente = $this->Cliente->findById($id);
		if(empty($cliente)) {
			throw new NotFoundException('Il cliente '.$id.' non esiste!');
		}*/
		
		if( $this->_aggiorna($id) ) {
			$this->Session->setFlash('Aggiornamento cliente eseguito correttamente', 'flash_ok');
		}
		else {
			$this->Session->setFlash('Errore durante aggiornamento cliente', 'flash_error');
		}
			
		$this->redirect(array('action' => 'view', $id));
	}
	
	/**
	 * come aggiorna(), ma pubblico con autenticazione rest invocato via xml
	 */
	public function aggiorna_rest($id) {
		
		if( empty($this->request->query['pass']) || $this->request->query['pass'] != REST_PASS ) {
			$this->set('success', 'Not Authorized');
		}
		else {
			$success = $this->_aggiorna($id);
			$this->set('success', $success ? 1 : 0);
		}
	}
	
	/**
	 * 
	 */
	function _aggiorna($id) {
		// verifica se il cliente esiste già
		$cliente_esistente = $this->Cliente->findById($id);
		
		if( $this->Cliente->syncConZolla($id) ) {
			
			$success = true;
			// verifica se il cliente ha aggiornato ID_CLIENTE_FATTURAZIONE. In quel caso devo chiudere il contratto
			// corrente e aprirne un altro
			// Invece se il cliente è nuovo devo aprire un contratto
			
			$cliente = $this->Cliente->findById($id);
			if( !empty($cliente_esistente) && $cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'] != $cliente_esistente['Cliente']['ID_CLIENTE_FATTURAZIONE']) {
				// chiudi i contratti esistenti (se ce ne sono)
				$this->loadModel('Contratto');
				$this->Contratto->updateAll(array(
					'data_chiusura' => date('Y-m-d H:i:s')
				), array(
					'Contratto.cliente_id' => $id,
					'Contratto.cliente_fatturazione_id' => $cliente_esistente['Cliente']['ID_CLIENTE_FATTURAZIONE'],
					'Contratto.data_chiusura' => null
				));
			}
			if( empty($cliente_esistente) || 
				$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'] != $cliente_esistente['Cliente']['ID_CLIENTE_FATTURAZIONE'] ||
				empty($cliente['ContrattoPerCuiRiceveSpesa'])
				
			) {
				// crea un nuovo contratto
				$salt = Configure::read('Security.salt');
				$cliente_access_token = $cliente['Cliente']['id'].'$x$'.sha1( rand(0,10000).$cliente['Cliente']['id'].$cliente['Cliente']['NOME'].$cliente['Cliente']['COGNOME'].$salt.date('Y-m-d H:i:s') );
				$cliente_fatturazione_access_token = $cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'$y$'.sha1( rand(0,10000).$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].$cliente['Cliente']['NOME'].$cliente['Cliente']['COGNOME'].$salt.date('Y-m-d H:i:s') );
				
				$success = $this->Contratto->save(array(
					'cliente_id' => $id,
					'cliente_fatturazione_id' => $cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'],
					'cliente_access_token' => $cliente_access_token, 
					'cliente_fatturazione_access_token' => $cliente_fatturazione_access_token
				), array('validate' => false));
			}
		}
		else {
			$success = false;
		}
		
		return $success;
	}
	
	
	public function datatables_processing() {
		
		$this->RequestHandler->setContent('json', 'application/json');

		/* Array of database columns which should be read and sent back to DataTables. Use a space where
	  	 * you want to insert a non-database field (for example a counter or static image)
	 	 */
	 	 
	 	$aColumns = array(
			'Cliente.id', 
			'Cliente.COGNOME',
			'Cliente.NOME',
			'Cliente.RAGIONE_SOCIALE',
			'Cliente.CODICE_FISCALE',
			'Cliente.PARTITA_IVA',
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

		if(!empty($sWhere)) $options['conditions'] = $sWhere;
		if(!empty($sOrder)) $options['order'] = $sOrder;

		$options['fields'] = $aColumns;

		$options['recursive'] = -1;

		$res = $this->Cliente->find('all', $options);	
		
		unset($options['limit']);
		unset($options['offset']);
		$iFilteredTotal = $this->Cliente->find('count', $options);

		/*
		 * Output
		 */
		$totWhere = array();

		$output = array(
			"sEcho" => isset($this->request->query['sEcho']) ? intval($this->request->query['sEcho']) : 1,
			"iTotalRecords" => $this->Cliente->find('count'),
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
	
		foreach($res as $row) {
			
			// leggi il numero di messaggi non letti
			$num_unread = $this->Cliente->query('SELECT COUNT(id) FROM messaging_messages WHERE is_read = 0 AND conversation_id = '.$row['Cliente']['id'].' AND is_received = 1 AND from_id <> -2 AND to_id=-1');
			$num_unread = $num_unread[0][0]['COUNT(id)'];
			
			$r["DT_RowId"] = $row['Cliente']["id"];
			$r["id"] = $row['Cliente']["id"];
			if($num_unread > 0) {
				$r["id"] .= '<div><a href="'.Router::url(['plugin' => 'messaging', 'controller' => 'messages', 'action' => 'chat', $row['Cliente']['id']]).'" class="label label-danger"><i class="fa fa-envelope"></i> '.$num_unread.'</a></div>';
			}
			$r["COGNOME"] = $row['Cliente']["COGNOME"];
			$r["NOME"] = $row['Cliente']["NOME"];
			$r["RAGIONE_SOCIALE"] = $row['Cliente']["RAGIONE_SOCIALE"];
			$r["CODICE_FISCALE"] = $row['Cliente']["CODICE_FISCALE"];
			$r["PARTITA_IVA"] = $row['Cliente']["PARTITA_IVA"];
			$r['actions'] = '<a title="visualizza cliente" href="'.Router::url(array('controller' => 'clienti', 'action' => 'view', $row['Cliente']["id"])).'" class="btn btn-xs btn-info">
				<i class="ace-icon fa fa-info-circle bigger-120"></i>
			</a> '.'<a title="chat cliente" href="'.Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'chat', $row['Cliente']["id"])).'" class="btn btn-xs btn-warning">
				<i class="ace-icon fa fa-envelope bigger-120"></i>
			</a>';

			$output['aaData'][] = $r;
		}

		
		$this->set('res', $output);
		$this->set('_serialize', 'res');
		
	}

} 


	/**
	 * global function per il sort fatture in ordine decrescente di data
	 */
	function cmpFatture($a, $b) {
		if($a['anno'] < $b['anno']) return 1;
		elseif($a['anno'] > $b['anno']) return -1;
		else {
			if( intval($a['mese']) < intval($b['mese']) ) return 1;
			elseif( intval($a['mese']) > intval($b['mese']) ) return -1;
			else {
				if( intval($a['giorno']) < intval($b['giorno']) ) return 1;
				elseif( intval($a['giorno']) > intval($b['giorno']) ) return -1;
			}
		}
		return 0;
	}
	
	/**
	 * global function per il sort fatture in ordine decrescente di data
	 */
	function cmpRiepiloghi($a, $b) {
		if($a['anno'] < $b['anno']) return 1;
		elseif($a['anno'] > $b['anno']) return -1;
		else {
			if( intval($a['mese']) < intval($b['mese']) ) return 1;
			elseif( intval($a['mese']) > intval($b['mese']) ) return -1;
		}
		return 0;
	}
