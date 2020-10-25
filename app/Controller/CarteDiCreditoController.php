<?php

// vendor autoload non funge, li carico a mano
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Client.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'ConfigInterface.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Config.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Environment.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'Payment.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'AbstractResource.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'ResourceModel' . DS . 'Payment' . DS . 'Authorise.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'ResourceModel' . DS . 'Payment' . DS . 'Authorise3D.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'ResourceModel' . DS . 'Payment' . DS . 'Authorise3DS2.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'HttpClient' . DS . 'ClientInterface.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'HttpClient' . DS . 'CurlClient.php');
require_once(ROOT . DS . 'vendor' . DS  . 'psr' . DS . 'log' . DS . 'Psr' . DS . 'Log' . DS . 'LoggerInterface.php');
require_once(ROOT . DS . 'vendor' . DS  . 'monolog' . DS . 'monolog' . DS . 'src' . DS . 'Monolog' . DS . 'ResettableInterface.php');
require_once(ROOT . DS . 'vendor' . DS  . 'monolog' . DS . 'monolog' . DS . 'src' . DS . 'Monolog' . DS . 'Logger.php');
require_once(ROOT . DS . 'vendor' . DS  . 'monolog' . DS . 'monolog' . DS . 'src' . DS . 'Monolog' . DS . 'Handler' . DS . 'HandlerInterface.php');
require_once(ROOT . DS . 'vendor' . DS  . 'monolog' . DS . 'monolog' . DS . 'src' . DS . 'Monolog' . DS . 'Handler' . DS . 'AbstractHandler.php');
require_once(ROOT . DS . 'vendor' . DS  . 'monolog' . DS . 'monolog' . DS . 'src' . DS . 'Monolog' . DS . 'Handler' . DS . 'AbstractProcessingHandler.php');
require_once(ROOT . DS . 'vendor' . DS  . 'monolog' . DS . 'monolog' . DS . 'src' . DS . 'Monolog' . DS . 'Handler' . DS . 'StreamHandler.php');
require_once(ROOT . DS . 'vendor' . DS  . 'monolog' . DS . 'monolog' . DS . 'src' . DS . 'Monolog' . DS . 'Formatter' . DS . 'FormatterInterface.php');
require_once(ROOT . DS . 'vendor' . DS  . 'monolog' . DS . 'monolog' . DS . 'src' . DS . 'Monolog' . DS . 'Formatter' . DS . 'NormalizerFormatter.php');
require_once(ROOT . DS . 'vendor' . DS  . 'monolog' . DS . 'monolog' . DS . 'src' . DS . 'Monolog' . DS . 'Formatter' . DS . 'LineFormatter.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'ConnectionException.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'AdyenException.php');

class CarteDiCreditoController extends AppController {

	var $name = "CarteDiCredito";

	public $uses = array(
		'CartaDiCredito', 
		'Cliente', 
		'ContractActivationTicket', 
		'Recapito', 
		'Addebito', 
		'PagamentoCarta',
		'User');
		
	public $components = array(
		'MyUtil'
	);
	
	public function beforeFilter() {
		
		parent::beforeFilter();
		
		// metodi pubblici per i clienti
		$this->Auth->allow(
			'contratto', 
			'authorise',
			'contract_activation', 
			'activation_success', 
			'activation_error', 
			'info_contratto',
			'add_rest',
			'invia_mail_passaggio_adyen'
		);
		
	}
	
	/**
	 * 
	 */
	public function invia_mail_attivazione($id) {
		
		$carta = $this->CartaDiCredito->findById($id);
		
		if(empty($carta)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		if( !empty($carta['CartaDiCredito']['signed']) ) {
			$this->Session->setFlash('Contratto già attivo', 'flash_ok');
			$this->redirect( $this->referer() );
		}
		
		// ottieni i recapiti a cui inviare il messaggio
		$cliente = $this->CartaDiCredito->Cliente->findById($carta['CartaDiCredito']['cliente_id']);
		$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'A');
		foreach($recapiti as $recapito) {
			// invia mail di benvenuto
			$Email = new CakeEmail('mandrillapp');
			$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
			$Email->to($recapito);
			$Email->subject(__("Le Zolle - pagamento con carta di credito"));
			$Email->setHeaders(array(
				'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['id'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
			));
			$Email->emailFormat('html');
			$Email->template('contratto_carta_credito', 'default');
			$urlContratto = Router::url(array('controller' => 'carte_di_credito', 'action' => 'contratto', $carta['CartaDiCredito']['id_contratto']), true);
			if(strpos($urlContratto, 'https') === false) { // http
				$urlContratto = str_replace('http://', 'https://', $urlContratto);
			}
			$Email->viewVars(array(
				'nomeCliente' => $cliente['Cliente']['displayName'],
				// forza https nell'url di contratto
				'urlContratto' => $urlContratto
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
		
		if( $this->CartaDiCredito->invia_mail_sollecito($id) ) {
			$this->Session->setFlash('Email di sollecito inviata', 'flash_ok');
			$this->redirect( $this->referer() );
		}
		// altrimenti si genera eccezione
		
	}
	
	/**
	 * 
	 */
	public function report_non_attive() {
		
		
		
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
	
	/**
	 * 
	 */
	public function invia_mail_passaggio_adyen($id) {
		
		$carta = $this->CartaDiCredito->findById($id);
		
		if(empty($carta)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		if( !empty($carta['CartaDiCredito']['signed']) && !empty($carta['CartaDiCredito']['adyen_psp_reference']) ) {
			$this->Session->setFlash('Contratto già attivo', 'flash_ok');
			$this->redirect( $this->referer() );
		}
		
		// ottieni i recapiti a cui inviare il messaggio
		$cliente = $this->CartaDiCredito->Cliente->findById($carta['CartaDiCredito']['cliente_id']);
		$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'A');
		foreach($recapiti as $recapito) {
			// invia mail di benvenuto
			$Email = new CakeEmail('mandrillapp');
			$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
			$Email->to($recapito);
			$Email->subject(__("Le Zolle - riattivazione servizio pagamento con carta di credito"));
			$Email->setHeaders(array(
				'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['id'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
			));
			$Email->emailFormat('html');
			$Email->template('richiesta_riattivazione_su_adyen', 'default');
			$Email->viewVars(array(
				'nomeCliente' => $cliente['Cliente']['displayName'],
				// forza https nell'url
				'urlContratto' => str_replace("http", "https", Router::url(array('controller' => 'carte_di_credito', 'action' => 'contratto', $carta['CartaDiCredito']['id_contratto']), true))
			));
			if(INVIA_MAIL) $Email->send();
		}
		
		$this->Session->setFlash('Email di ri-attivazione su Adyen inviata', 'flash_ok');
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
			
			$res = $this->requestAction('/carte_di_credito/add/'.$cliente_id);
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
			$tmpContratto = $this->CartaDiCredito->find('all', array('conditions' => array('id_contratto' => $id_contratto)));
			if(empty($tmpContratto)) $id_already_used = false;
		}
		while($id_already_used);

		$carta = [
			'CartaDiCredito' => [
				'cliente_id' => $cliente_id,
				'id_contratto' => $id_contratto,
				'contractAmountAlreadyCredited' => 0, //PER LA NUOVA GESTIONE NON ME NE DEVO PIU' PREOCCUPARE
				'sent' => date('Y-m-d H:i:s')
			]
		];

		//inserisci la prima spesa per l'attivazione
		if(TEST) $importo = 0.01;
		else $importo = 1.00;
		
		$carta['Addebito'] = [];
		$carta['Addebito'][] = array(
			'cliente_id' => $cliente_id,
			'type' => PRIMO_PAGAMENTO,
			'importo' => $importo,
			'mese' => 0,
			'anno' => 0,
			'active' => 1,
			'last_payment_ok' => -1 // - no payment done yet
		);	
		
		// 2019-12-03: incredibilmente non riesce più a salvare le associazioni via saveAll (neanche con deep => true),
		// uso una transaction
		$dbo1 = $this->CartaDiCredito->getDataSource();
		$dbo2 = $this->Addebito->getDataSource();
		$dbo1->begin();
		$dbo2->begin();
		
		$res = true;
		$res &= $this->CartaDiCredito->save($carta['CartaDiCredito']);
		$carta['Addebito'][0]['carta_id'] = $this->CartaDiCredito->getLastInsertId();
		$res &= $this->Addebito->save($carta['Addebito'][0]);
		
		if($res) {
			$dbo1->commit();
			$dbo2->commit();
		}
		else {
			$dbo1->rollback();
			$dbo2->rollback();
		}
		
		if( $res ) {
			
			// ottieni i recapiti a cui inviare il messaggio
			$cliente = $this->Cliente->findById($cliente_id);
			$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'A');
			foreach($recapiti as $recapito) {
				// invia mail di benvenuto
				$Email = new CakeEmail('mandrillapp');
				$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
				$Email->to($recapito);
				$Email->subject(__("Le Zolle - pagamento con carta di credito"));
				$Email->setHeaders(array(
					'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['id'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
				));
				$Email->emailFormat('html');
				$Email->template('contratto_carta_credito', 'default');
				// forza https sull'url (lo faccio in modo custom dato che non sembra prendere il protocollo in automatico)
				$urlContratto = Router::url(array('controller' => 'carte_di_credito', 'action' => 'contratto', $id_contratto), true);
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
				$this->Session->setFlash('Carta di credito aggiunta correttamente', 'flash_ok');
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
	 * in realtà è solo un view ...
	 */
	public function edit($id) {
		
		$carta = $this->CartaDiCredito->findById($id);
		
		if(empty($carta)) throw new NotFoundException('Metodo di pagamento non trovato');

		$this->set('carta', $carta);
	}
	
	/**
	 * 
	 */
	public function confirm_delete($id) {
		
		$carta = $this->CartaDiCredito->findById($id);
		
		if(empty($carta)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		$this->set('carta', $carta);
		
	}
	
	/**
	 * 
	 */
	public function delete($id) {
		
		$carta = $this->CartaDiCredito->findById($id);
		if( empty($carta) ) throw new NotFoundException('Carta di credito non trovata');
		if( $this->CartaDiCredito->delete($id) ) {
			$this->Session->setFlash('Carta di credito rimossa correttamente', 'flash_ok');
		}
		else {
			$this->Session->setFlash('Errore durante cancellazione', 'flash_error');
		}
		
		$this->redirect( array('controller' => 'clienti', 'action' => 'view', $carta['Cliente']['id']) );
	}
	
	/**
	 * Attivazione contratto carta su Adyen
	 */
	public function authorise($id_contratto) {
		if(!empty($this->request->data)) {
			$carta = $this->CartaDiCredito->find('first', array(
				'conditions' => array(
					'CartaDiCredito.id_contratto' => $id_contratto,
				),
				'contain' => array(
					'Cliente' => array('Recapito')
				)
			));
			if(empty($carta)) {
				$this->Session->setFlash("Contratto non trovato", 'flash_error');
				$this->redirect($this->referer());
			}
			//se il contratto e' gia' stato sottoscritto non procedere oltre	
			if( !empty($carta['CartaDiCredito']['signed']) && !empty($carta['CartaDiCredito']['adyen_psp_reference']) ) { // devo controllare anche che la psp reference non sia vuota perchè potrebbe essere la ri-attivazione di un contratto già attivo
				$this->redirect(array('action' => 'info_contratto', $id_contratto));
			}
			// tira su l'addebito associato (l'ultimo in ordine di ID perchè se è una ri-attivazione ne esiste più di uno)
			$addebito = $this->Addebito->find('first', [
				'conditions' => [
					'cliente_id' => $carta['CartaDiCredito']['cliente_id'],
					'type' => PRIMO_PAGAMENTO,
					'carta_id' => $carta['CartaDiCredito']['id'],
					'last_payment_ok' => -1,
				],
				'order' => array('id DESC'),
				'contain' => ['PagamentoCarta']
			]);
			$pagamento = $addebito['PagamentoCarta'][ sizeof($addebito['PagamentoCarta'])-1 ];
			
			$data = $this->request->data;
			if( !isset($data['adyen-encrypted-data']) || empty($data['adyen-encrypted-data']) ) {
				$this->Session->setFlash("Si è verificato un errore", 'flash_error');
				$this->redirect($this->referer());
			}
			
			$client = new \Adyen\Client();
			$client->setApplicationName(ADYEN_APPLICATION_NAME);
			$client->setUsername(ADYEN_USERNAME);
			$client->setXApiKey(ADYEN_API_KEY);
			$client->setPassword(ADYEN_PASSWORD);
			$client->setEnvironment(ADYEN_ENVIRONMENT);

			$service = new \Adyen\Service\Payment($client);
			
			// prepara i dati per il pagamento
			$params = [
				'additionalData' => [
					'card.encrypted.json' => $data['adyen-encrypted-data']
				],
				'amount' => [
					'value' => 100,
					'currency' => 'EUR'
				],
				'merchantAccount' => ADYEN_MERCHANT,
				'reference' => 'Contratto '.$id_contratto, // uniquely identifies the payment
				// aggiungi i dettagli necessari per 'attivare' i pagamenti ricorrenti
				'recurring' => [
					'contract' => 'RECURRING,ONECLICK' //'RECURRING,ONECLICK' // nel caso di zolle qui basta il valore "RECURRING"
				],
				'recurringProcessingModel' => 'CardOnFile', // Any subscription not following a fixed schedule is also considered as a card-on-file transaction.
				'shopperReference' => $carta['CartaDiCredito']['cliente_id'], // This field is required for recurring payments.
			];
			
			$recapiti = $this->ClientiUtil->getRecapitiPerTipo($carta['Cliente']['Recapito'], 'A');
			if(!empty($recapiti) && !empty($recapiti[0]) && $recapiti[0] != 'cliente.senza.mail@zolle.it' ) {
				$params['shopperEmail'] = $recapiti[0]; // The shopper's email address. We recommend you provide this data, as it is used in velocity fraud checks.
			}
			
			try {
				$result = $service->authorise($params);
				
				// aggiorna l'addebito e il relativo tentativo di pagamento
				$this->Addebito->save([
					'id' => $addebito['Addebito']['id'],
					'last_payment_ok' => $result['resultCode'] == 'Authorised' ? 1 : 0
				]);
				$pagamento = $addebito['PagamentoCarta'][ sizeof($addebito['PagamentoCarta'])-1 ];
				$this->Addebito->PagamentoCarta->save([
					'id' => $pagamento['id'],
					'response_details' => json_encode($result),
					'esito_banca' => $result['resultCode'] == 'Authorised' ? 'OK' : 'KO'
				]);
				
				if($result['resultCode'] == 'Authorised') {
					// aggiorna il contratto
					$this->CartaDiCredito->save([
						'id' => $carta['CartaDiCredito']['id'],
						'signed' => date('Y-m-d H:i:s'),
						'adyen_psp_reference' => $result['pspReference'],
						'adyen_auth_code' => $result['authCode'] 
					]);
					
					$cliente_id = $carta['CartaDiCredito']['cliente_id'];
					$carta_id = $carta['CartaDiCredito']['id'];
					// carta abilitata -> attiva automaticamente il metodo di pagamento
					$this->requestAction('/clienti/attiva_metodo_pagamento/'.$cliente_id.'/'.CARTA.'/'.$carta_id);
					// carta abilitata -> disattiva ogni altro metodo di pagamento esistente
					$this->Cliente->disattivaAltriMetodiPagamento($cliente_id, [
						'carta_id' => $carta_id,
					]);
					
					// redireziona il cliente alla pagina di conferma
					$this->redirect(['action' => 'activation_success']);
				}
				else{
					$this->Session->setFlash("Siamo spiacenti, la tua carta non è stata autorizzata. Ti invitiamo ad utilizzarne un'altra. Grazie", 'flash_error');
					$this->redirect($this->referer());
				}
			}
			catch(Exception $e) {
				// TODO: LOG dell'eccezione
				$this->Session->setFlash("Si è verificato un errore", 'flash_error');
				$this->redirect($this->referer());
			}
			
			// aggiorna variabile in sessione per l'area riservata
			$this->Cliente->contrattoDaRiattivareSuAdyenToSession();
		}
	}
	
	/**
	 * 
	 */
	public function contratto($id_contratto) {

		$this->layout = 'mangio';
		
		// ottieni il contratto
		$carta = $this->CartaDiCredito->find('first', array(
			'conditions' => array(
				'CartaDiCredito.id_contratto' => $id_contratto,
			),
			'contain' => array(
				'Cliente' => array(
					'Addebito' => array(
						'PagamentoCarta',
						'conditions' => array(
							'type' => PRIMO_PAGAMENTO,
							'last_payment_ok' => -1
						)
					),
					'Recapito'
				)
			)
		));
		
		if(empty($carta)) {
			throw new NotFoundException('Contratto non presente in archivio');
		}
		// se la carta è stata disattivata non puoi procedere oltre
		if(!empty($carta['CartaDiCredito']['data_disattivazione'])) {
			throw new BadRequestException('Il contratto non è più disponibile');
		}

		//inserisci il tentativo di pagamento che il cliente sta per eseguire
		//NOTA: ogni transazione sul circuito della banca puo' essere eseguito una sola volta, quindi se il pagamento non andasse a buon fine, un nuovo
		//tentativo di pagamento deve essere creato ogni volta che si ri-visualizza il contratto
		
		//se il contratto e' gia' stato sottoscritto e correttamente attivo su Adyen non procedere oltre	
		if( !empty($carta['CartaDiCredito']['signed']) && !empty($carta['CartaDiCredito']['adyen_psp_reference']) ) { // se la psp reference è vuota è un contratto che deve essere ri-attivato su adyen
			$this->redirect(array('action' => 'info_contratto', $id_contratto));
		}
		
		// se il contratto deve essere ri-attivato su adyen genera un nuovo addebito 'PRIMO_PAGAMENTO', altrimenti
		// per i contratti nuovi è già stato creato in fase di creazione del contratto
		if( !empty($carta['CartaDiCredito']['signed']) && empty($carta['CartaDiCredito']['adyen_psp_reference']) ) {
			// crea l'addebito
			$addebito = array(
				'cliente_id' => $carta['CartaDiCredito']['cliente_id'],
				'type' => PRIMO_PAGAMENTO,
				'importo' => 1,
				'mese' => 0,
				'anno' => 0,
				'active' => 0,
				'carta_id' => $carta['CartaDiCredito']['id'],
				'last_payment_ok' => -1,
			);
			$this->Addebito->save($addebito);
			$addebito['id'] = $this->Addebito->id; // used later
			$addebito['PagamentoCarta'] = array(); // used later
		}
		else { // nuovo contratto, esiste già (il primo PRIMO_PAGAMENTO che trovo in ordine)
			$addebito = null;
			foreach($carta['Cliente']['Addebito'] as $a) {
				if($a['type'] == PRIMO_PAGAMENTO) {
					$addebito = $a;
					break;
				}
			}
			if(empty($addebito)) { // NON DEVE SUCCEDERE, MA PER SICUREZZA
				$addebito = array(
					'cliente_id' => $carta['CartaDiCredito']['cliente_id'],
					'type' => PRIMO_PAGAMENTO,
					'importo' => 1,
					'mese' => 0,
					'anno' => 0,
					'active' => 0,
					'carta_id' => $carta['CartaDiCredito']['id'],
					'last_payment_ok' => -1,
				);
				$this->Addebito->save($addebito);
				$addebito['id'] = $this->Addebito->id; // used later
				$addebito['PagamentoCarta'] = array(); // used later
			}
		}
		
		// genera il tentativo di pagamento
		$transaction_id = $carta['Cliente']['id']."-".$addebito['id']."-".(sizeof($addebito['PagamentoCarta'])+1);

		$pag_prima_spesa = array('PagamentoCarta' => array(
			'saldo_id' => $addebito['id'],
			'transaction_id' => $transaction_id, // solo per compatibilità ma su Adyen non serve più a nulla questo campo
			'esito_banca' => '',
			'request_details' => 'nd (Adyen)',
			'request_errors' => '',
			'response_details' => '',
			'response_errors' => ''
		));	

		//get the payment url
		$recapiti = isset($carta['Cliente']['Recapito']) ? $this->ClientiUtil->getRecapitiPerTipo($carta['Cliente']['Recapito'], 'A') : [];
		
		if(!$this->PagamentoCarta->save($pag_prima_spesa))
		{			
			$this->Session->setFlash("Errore imprevisto: si prega di visualizzare nuovamente il contratto e riprovare", 'flash_error');
			$this->redirect(array('action' => 'errore'));
		}
		
		//export data to the view
		$this->set('id_contratto', $id_contratto);
		$this->set('carta', $carta['CartaDiCredito']);
		$this->set('cliente', $carta['Cliente']);
		
	}
	
	/**
	 * 
	 */
	function invio_procedura_attivazione_manuale($carta_id)
	{
		die; // metodo non più usabile con Adyen
		$carta = $this->CartaDiCredito->findById($carta_id);
		if(empty($carta)) throw new NotFoundException('Carta non trovata');

		//genera un ticket casuale da associare a questo cliente - usato per evitare che il cliente tenti piu' volte la procedura!
		$ticket = sha1(uniqid());

		//memorizza il ticket creato e azzera pan e scadenza pan (se esistono)
		if(!$this->ContractActivationTicket->saveAll(array(
			'ContractActivationTicket' => array(
				'id' => $carta_id,
				'ticket' => $ticket
			),
			'CartaDiCredito' => array(
				'id' => $carta_id,
				'pan' => '',
				'scadenza_pan' => '',
				'signed' => NULL
			)
		))) //se ne esistono già associati allo stesso cliente vengono automaticamente sovrascritti
		{
			$this->Session->setFlash("Errore di memorizzazione. Ripetere l'operazione", 'flash_error');
			$this->redirect($this->referer());
		}
		
		//tutto ok a livello di db, invia l'email al cliente
		$urlAttivazione = Router::url(array('controller' => 'carte_di_credito', 'action' => 'contract_activation', $ticket), true);
	
		// invia mail di benvenuto
		$recapiti = $this->ClientiUtil->getRecapitiPerTipo($this->Recapito->find('all', array('conditions' => array(
			'cliente_id' => $carta['Cliente']['id']
		))), 'A');
		if(empty($recapiti)) throw new BadRequestException('Impossibile inviare mail, nessun recapito disponibile');
		
		$Email = new CakeEmail('mandrillapp');
		$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
		$Email->to($recapiti[0]);
		$Email->subject(__("Le Zolle - pagamento con carta di credito"));
		$Email->setHeaders(array(
			'X-MC-Metadata' => '{"id_cliente": "'.$carta['Cliente']['id'].'", "id_cliente_fatturazione": "'.$carta['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
		));
		$Email->emailFormat('html');
		$Email->template('attivazione_manuale_carta', 'default');
		$Email->viewVars(array(
			'nomeCliente' => $carta['Cliente']['displayName'],
			'urlAttivazione' => $urlAttivazione
		));
		if(INVIA_MAIL) $Email->send();
	
		$this->Session->setFlash('Richiesta di attivazione manuale del contratto carta inviata correttamente', 'flash_ok');
		$this->redirect( $this->referer() );
	}
	
	/**
	 * 
	 */
	public function contract_activation($ticket_id)
	{
		// QUESTO METODO NON HA PIÙ RAGIONE DI ESISTERE CON ADYEN!
		die; 
		
		$this->layout = 'mangio';

		// verifica che il ticket sia valido
		$ticket = $this->ContractActivationTicket->find('first', array('conditions' => array(
			'ticket' => $ticket_id
		)));
		if( empty($ticket) ) throw new NotFoundException('Ticket di attivazione non valido');
		// verifica che la carta a cui il ticket si riferisce esista e sia ancora da attivare
		$carta = $this->CartaDiCredito->findById($ticket['ContractActivationTicket']['id']);
		if( empty($carta) ) throw new NotFoundException('Il ticket non è associato ad alcuna carta di credito');
		if( !empty($carta['CartaDiCredito']['signed']) ) throw new NotFoundException('Il ticket non è associato ad alcuna carta di credito da attivare');
		
		if(!empty($this->request->data)) {
			//start transaction
			$transaction_ok = true;

			$dbo1 = $this->CartaDiCredito->getDataSource();
			$dbo2 = $this->ContractActivationTicket->getDataSource();
			$dbo3 = $this->Addebito->getDataSource();
			$dbo4 = $this->PagamentoCarta->getDataSource();
			$dbo1->begin();
			$dbo2->begin();
			$dbo3->begin();
			$dbo4->begin();

			//build pan
			$token1 = $this->request->data['CartaDiCredito']['token_1'];
			$token2 = $this->request->data['CartaDiCredito']['token_2'];
			$pan = $token1.'XXXXXX'.$token2;
			$scadenza_pan = $this->data['CartaDiCredito']['anno_scadenza']['year'].$this->data['CartaDiCredito']['mese_scadenza']['month'];
			
			// valida il pan
			if( !ctype_digit($token1.$token2) || strlen($token1.$token2) != 10 ) {
				$this->Session->setFlash('Il PAN inserito non è valido', 'flash_error');
				$this->redirect( array('action' => 'contract_activation', $ticket_id) );
			}
					
			$transaction_ok = $this->CartaDiCredito->save(array(
				'id' => $carta['CartaDiCredito']['id'],
				'pan' => $pan,
				'scadenza_pan' => $scadenza_pan,
				'signed' => date('Y-m-d H:i:s', time())
			));

			if($transaction_ok) {
				//rimuovi il ticket in modo che il cliente non ritenti di modificare il pan e la scadenza pan
				$transaction_ok = $this->ContractActivationTicket->delete($carta['CartaDiCredito']['id']);
			}

			if($transaction_ok) {//crea il primo pagamento ok per questo cliente senza dettagli
				
				if(!$this->Addebito->save(
					array(
						'cliente_id' => $carta['Cliente']['id'],
						'type' => PRIMO_PAGAMENTO,
						'importo' => 1.00,
						'mese' => 0,
						'anno' => 0,
						'active' => 0,
						'last_payment_ok' => 1 //payment OK,
						)
				)) $transaction_ok = false;
			}
			if($transaction_ok) //crea un pagamento OK per la spesa OK precedentemente creata
			{
				$payment_id = $carta['Cliente']['id']."-".$this->Addebito->id."-0";

				$pag_prima_spesa = array('PagamentoCarta' => array(
						'saldo_id' => $this->Addebito->id,
						'transaction_id' => $payment_id,
						'esito_banca' => 'OK',
						'request_details' => 'Pagamento creato in automatico dal sistema per attivazione manuale del contratto da parte del cliente',
						'request_errors' => '',
						'response_details' => 'Pagamento creato in automatico dal sistema per attivazione manuale del contratto da parte del cliente',
						'response_errors' => ''
					));

				$transaction_ok = $this->PagamentoCarta->save($pag_prima_spesa);
			}	

			if($transaction_ok) {
				$dbo1->commit();
				$dbo2->commit();	
				$dbo3->commit();	
				$dbo4->commit();	
			}
			else {
				$dbo1->rollback();
				$dbo2->rollback();
				$dbo3->rollback();	
				$dbo4->rollback();
			}
			
			if($transaction_ok) {
				
				if(INVIA_MAIL) {
					//invia email a zolle per notificare che il cliente ha attivato il contratto
					Configure::load('zolle');
					$emails = Configure::read('Zolle');	
					foreach($emails as $email) {
						
						$Email = new CakeEmail('mandrillapp');
						$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
						$Email->to($email);
						$Email->subject(__("Le Zolle - Attivazione manuale carta di credito"));
						$Email->emailFormat('html');
						$Email->setHeaders(array(
							'X-MC-Metadata' => '{"id_cliente": "'.$carta['Cliente']['id'].'", "id_cliente_fatturazione": "'.$carta['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
						));
						$Email->template('conferma_attivazione_manuale_carta', 'default');
						$Email->viewVars(array(
							'cliente' => $carta['Cliente'],
							'pan' => $pan,
							'scadenza_pan' => $scadenza_pan,
						));
						$Email->send();
					}
				}
				$this->redirect(array('controller' => 'carte_di_credito', 'action' => 'activation_success'));
			}
			else {
				$this->redirect(array('controller' => 'carte_di_credito', 'action' => 'activation_error'));
			}
		}

		$this->set('carta', $carta);
		$this->set('ticket', $ticket);
	}
	
	/**
	 * 
	 */
	public function activation_success() {
		$this->layout = 'mangio';
	}
	
	/**
	 * 
	 */
	public function activation_error() {
		$this->layout = 'mangio';
	}
	
	/**
	 * 
	 */
	public function info_contratto($id_contratto)
	{
		$this->layout = 'mangio';

		$carta = $this->CartaDiCredito->find('first', array(
			'conditions' => array(
				'id_contratto' => $id_contratto
			),
			'contain' => array(
				'Cliente'
			)
		));
		if(empty($carta)) {
			throw new NotFoundException('Contratto non presente in archivio');
		}
		
		
		$this->set('carta', $carta);
	}
	
	/**
	 * 
	 */
	public function index($tipo=null) {
		if(!isset($tipo)) $tipo = CARTE_TUTTE;
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
			'CartaDiCredito.id', 
			'CONCAT(Cliente.COGNOME, " ", Cliente.NOME)',
			'pan',
			'scadenza_pan',
			'sent',
			'signed',
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
		if( $t == CARTE_TUTTE ) {
			// nessuna condizione
		}
		elseif( $t == CARTE_SCADUTE ) {
			$options['conditions']["DATE_FORMAT( CONCAT( SUBSTR(scadenza_pan,1,4), '-', SUBSTR(scadenza_pan,5,2), '-15'), '%Y-%m-%d %H:%i:%s') <"] = date('Y-m-d H:i:s');
		}
		elseif( $t == CARTE_NON_ATTIVE ) {
			$options['conditions']['OR'] = array(
				'signed' => NULL,
				'pan' => NULL,
				'scadenza_pan' => NULL
			);
		}
	
		$res = $this->CartaDiCredito->find('all', $options);	
		
		unset($options['limit']);
		unset($options['offset']);
		$iFilteredTotal = $this->CartaDiCredito->find('count', $options);

		/*
		 * Output
		 */
		$totWhere = array();

		$output = array(
			"sEcho" => isset($this->request->query['sEcho']) ? intval($this->request->query['sEcho']) : 1,
			"iTotalRecords" => $this->CartaDiCredito->find('count'),
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
	
		foreach($res as $row) {
			
			$r["DT_RowId"] = $row['CartaDiCredito']["id"];
			$r["id"] = $row['CartaDiCredito']["id"];
			$r["pan"] = $row['CartaDiCredito']["pan"];
			$r["scadenza_pan"] = $row['CartaDiCredito']["scadenza_pan"];
			$r["sent"] = empty($row['CartaDiCredito']["sent"]) ? '' : $row['CartaDiCredito']["sent"];
			$r["signed"] = empty($row['CartaDiCredito']["signed"]) ? '' : $row['CartaDiCredito']["signed"];
			$r["cliente_nome"] = '<a href="'.Router::url(array('controller' => 'clienti', 'action' => 'view', $row['Cliente']["id"])).'">'.$row['Cliente']["COGNOME"].' '.$row['Cliente']["NOME"].'</a>';
			
			$r['actions'] = '<a title="visualizza carta" href="'.Router::url(array('controller' => 'carte_di_credito', 'action' => 'edit', $row['CartaDiCredito']["id"])).'" class="btn btn-xs btn-info">
				<i class="ace-icon fa fa-info-circle bigger-120"></i>
			</a>';

			$output['aaData'][] = $r;
		}

		
		$this->set('res', $output);
		$this->set('_serialize', 'res');
		
	}

}
