<?php

App::uses('HttpSocket', 'Network/Http');

// vendor autoload non funge, li carico a mano
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Client.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'ConfigInterface.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Config.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Environment.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'AbstractResource.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'Payment.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'ResourceModel' . DS . 'Recurring' . DS . 'Disable.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'ResourceModel' . DS . 'Recurring' . DS . 'ListRecurringDetails.php');
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Service' . DS . 'Recurring.php');
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


class PagamentiCartaController extends AppController {

	var $name = "PagamentiCarta";
	
	public $components = array('MyUtil', 'Email');
	
	public $uses = array('PagamentoCarta');

	public function beforeFilter() {
		
		parent::beforeFilter();
	
		// metodi pubblici per i clienti
		$this->Auth->allow('esito_pagamento', 'test');
		
	}
	
	public function view($id) {
		
		if( $this->request->is('post') || $this->request->is('put') ) {
			
			// se esito_banca non è vuoto aggiorna last_payment_ok e sblocca la spesa (eventualmente bloccata)
			if( !empty($this->request->data['PagamentoCarta']['esito_banca']) ) {
				
				$d = $this->request->data['PagamentoCarta'];
				
				$this->request->data['Addebito']['id'] = $d['saldo_id'];
				$this->request->data['Addebito']['last_payment_ok'] = $d['esito_banca'] == 'OK' ? 1 : 0; 
				$this->request->data['Addebito']['blocked'] = null;
				
			}
			
			if( $this->PagamentoCarta->saveAll($this->request->data) ) {
				$this->Session->setFlash('Salvataggio eseguito correttamente', 'flash_ok');
			}
			else {
				$this->Session->setFlash('Errore salvataggio', 'flash_error');
			}
		}
		
		$p = $this->PagamentoCarta->find('first', array(
			'conditions' => array('PagamentoCarta.id' => $id),
			'contain' => array(
				'Addebito' => array(
					'Cliente'
				)
			)
		));
		if(empty($p)) throw new NotFoundException('Pagamento carta non trovato');
		
		$this->request->data = $p;
		$this->set('p', $p);
	}
	
	/**
	 * seleziona le spese ricorrenti che devono essere processate con carta di credito
	 */
	public function seleziona_ricorrenti($escludi_tutti=0) {
		
		// mandatory.  Quando visito la pagina resetto tutti gli excluded che sono valori temporanei
		$this->PagamentoCarta->Addebito->updateAll(
			array('Addebito.excluded' => $escludi_tutti == 0 ? NULL : 1),
			array('Addebito.id >' => 0)
		);
				
	}
	
	/**
	 * 
	 */
	public function esegui_ricorrenti() {
		
		// controllo di sicurezza: verifica che non ci siano spese attive per precedenti periodi di esazione
		// (che altrimenti verrebbero ripagate!!!)
		$addebitoPiuRecente = $this->PagamentoCarta->Addebito->find('first', array(
			'conditions' => array('type' => RICORRENTE),
			'order' => array('Addebito.anno DESC', 'Addebito.mese DESC')
		));
		$meseEsazione = $addebitoPiuRecente['Addebito']['mese'];
		$annoEsazione = $addebitoPiuRecente['Addebito']['anno'];
		// verifica se esistono degli addebiti attivi con periodo di esazione diverso da quello corrente
		$num = $this->PagamentoCarta->Addebito->find('count', array(
			'conditions' => array(
				'type' => RICORRENTE,
				'active' => 1,
				'OR' => array(
					'mese <>' => $meseEsazione,
					'anno <>' => $annoEsazione
				)
			)
		));
		if($num > 0) {
			$this->Session->setFlash("Attenzione: sono presenti a sistema <b>{$num}</b> spese attive relative a periodi di esazione diversi da quello attuale (<b>{$meseEsazione}/{$annoEsazione}</b>). Correggere il problema per procedere", 'flash_error');
			$this->redirect('/');
		}
		
		// tutto ok, visualizza all'utente il periodo per cui stanno per passare i pagamenti
		$this->Session->setFlash("<b>Attenzione</b>: stanno per essere pagate le spese del periodo <b>{$meseEsazione}/{$annoEsazione}</b>", 'flash_ok');
		
		// ottieni la lista di tutte gli addebiti da pagare (ovvero quelli pagabili e non esclusi)
		$options['fields'] = array('id');
		$options['conditions']['active'] = 1;
		$options['conditions']['type'] = RICORRENTE;
		$options['conditions']['last_payment_ok <>'] = 1;
		$options['conditions']['blocked'] = NULL;
		$options['conditions']['excluded'] = NULL;
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
		$options['recursive'] = -1;
		
		$addebiti = $this->PagamentoCarta->Addebito->find('all', $options);
		$this->set('num_addebiti', sizeof($addebiti));
		$this->set('addebiti', json_encode($addebiti));
	}
	
	//url di risposta per i pagamenti di tipo 'primo pagamento' ovvero pagamenti fatti dal cliente
	//NOTA: a differenza dei pagamenti ricorrenti, non posso controllare un eventuale messaggio di errore nella risposta alla richiesta
	//di pagamento, perche' questa viene restituita al cliente e non al sistema
	public function esito_pagamento() {
		die; // METODO DI NEXTI/CARTASI, SU ADYEN NON SI USA PIÙ
		$this->layout = 'mangio';
		
		$params = $this->request->query;
		
		$res = $this->_handle_bank_response($params, PRIMO_PAGAMENTO);

		//------------------parte del sistema------------------
		if($res != null)
		{
			if($res['esito'] == 'OK') $last_payment_ok = 1;
			else $last_payment_ok = 0;

			//aggiorna il pagamento nel database
			$pagamento = $this->PagamentoCarta->find('first', array(
				'conditions' => array(
					'transaction_id' => $params['codTrans'],
				)
			));
			$pagamento['Addebito']['last_payment_ok'] = $last_payment_ok;
			$pagamento['PagamentoCarta'] = array(
				'esito_banca' => $res['esito'],
				'response_details' => $res['response_details'],
				'response_errors' => $res['response_errors'],
			);
			
			if( !$this->PagamentoCarta->saveAll($pagamento) ) {
				
				$res['esito'] = '';
				
				/*$msg = 'Non e\' stato possibile memorizzare il primo pagamento eseguito dal cliente.'
					'Inserire manualmente l\'avvenuto tentativo di pagamento:<BR>'.
					'Esito: '.$res['esito'].'<BR>'.
					'Dettagli richiesta: '.$pagamento['PagamentoCarta']['request_details'].'<BR>'.
					'Errori richiesta: '.$pagamento['PagamentoCarta']['request_errors'].'<BR>'.
					'Dettagli risposta: '.$pagamento['PagamentoCarta']['response_details'].'<BR>'.
					'Errori risposta: '.$pagamento['PagamentoCarta']['response_errors'].'<BR>';
				Configure::load('zolle');
				$emails = Configure::read('Zolle');		
				foreach($emails as $email) $this->_sendInternalEmail(
					$email, 
					'Errore pagamenti ricorrenti', 
					$msg);*/
			}
		}
		

		//------parte per il cliente-------
		$this->set('res', $res);
	}
	
	/**
	 * 
	 */
	public function paga_ajax($addebito_id) {
		$this->RequestHandler->setContent('json', 'application/json');
		$this->_paga($addebito_id);
		$this->set('res', array());
		$this->set('_serialize', 'res');
	}
	
	/**
	 * 
	 */
	public function paga($addebito_id) {
		$this->_paga($addebito_id);
		$this->redirect( $this->referer() );
	}
	
	/**
	 * 
	 */
	function _paga($addebito_id) {
		
		$addebito = $this->PagamentoCarta->Addebito->find('first', array(
			'conditions' => array(
				'Addebito.id' => $addebito_id,
				'Addebito.type' => RICORRENTE,
				'Addebito.active' => 1, // mandatory! Solo quelle attive
				'Addebito.blocked' => NULL, // mandatory! Solo quelle non bloccate!
			),
			'contain' => array(
				'PagamentoCarta',
				'CartaDiCredito',
				'Cliente' => array(
					'Recapito'
				)
			)
		));
		if(empty($addebito)) return;
		
		// se l'addebito è già stato pagato non procedere
		if( $addebito['Addebito']['last_payment_ok'] == 1 ) return;
		
		$c = empty($addebito['CartaDiCredito']) ? array() : $addebito['CartaDiCredito'];
		if( empty($c['id']) ) return; // la carta non esiste
		if( empty($c['signed']) ) return; // contratto non firmato
		if( !empty($c['data_disattivazione']) ) return; // carta disattivata! (non deve succedere)
		
		// se il cliente associato all'addebito non esiste non procedere (mi serve dopo per i recapiti)
		if( empty($addebito['Cliente']['id']) ) return;
		
		// se esiste un pagamento carta con esito 'OK' non procedere
		$pagamentoOkFound = false;
		if( !isset($addebito['PagamentoCarta']) ) $addebito['PagamentoCarta'] = array();
		foreach($addebito['PagamentoCarta'] as $p) {
			if($p['esito_banca'] == 'OK') {
				$pagamentoOkFound = true;
				break;
			}
		}
		if($pagamentoOkFound) return;
		
		// se l'ultimo tentativo di pagamento è avvenuto meno di 2 ore fa non procedere
		if( !empty($addebito['PagamentoCarta']) ) {
			if( strtotime($addebito['PagamentoCarta'][0]['created']) + 120*60 > time() ) { // i pagamenti sono in ordine di creazione decrescente
				return;
			}
		}
		
		// procedi
		if( empty($c['adyen_psp_reference']) ) {
			if( empty($c['scadenza_pan']) ) { // carta su adyen non ancora attiva, non posso fare nulla
				return;
			}
			else {
				// paga con keyclient
				return;
				// $this->_pagaConKeyclient($addebito); KEYCLIENT DEFINITIVAMENTE SPENTO
			}
		}
		else {
			// paga con adyen
			$this->_pagaConAdyen($addebito);
		}
	}
	
	/**
	 * 
	 */
	function _pagaConAdyen($addebito) {
		$c = empty($addebito['CartaDiCredito']) ? array() : $addebito['CartaDiCredito'];
		if( empty($c['adyen_psp_reference']) ) return; // non posso pagare
		
		// esegui il pagamento
		$addebito_id = $addebito['Addebito']['id'];
		
		// 1. crea un nuovo pagamento (necessario perchè i transaction id inviati a keyclient devono essere sempre diversi
		$pagamento = array(); 
		$pagamento['saldo_id'] = $addebito_id;
		$pagamento['transaction_id'] = $addebito['Addebito']['cliente_id'].'-'.$addebito_id.'-'.(sizeof($addebito['PagamentoCarta'])+1); // solo per compatibilità, su adyen non serve a nulla questo campo
		
		// 2. setta l'addebito come blocked.  
		// Se l'operazione andrà a buon fine (nessun errore di rete o db) sarà sbloccato solo a quel punto
		$addebito['Addebito']['blocked'] = 1;

		// salva
		if(! $this->PagamentoCarta->Addebito->saveAll(array(
			'Addebito' => $addebito['Addebito'],
			'PagamentoCarta' => array(
				$pagamento
			)
		) ) ) return; // salvataggio non avvenuto, mi fermo.
		
		$pagamento['id'] = $this->PagamentoCarta->id; // MANDATORY, usato successivamente per aggiornare il pagamento
		
		// 3. esegui il pagamento vero e proprio
		$client = new \Adyen\Client();
		$client->setApplicationName(ADYEN_APPLICATION_NAME);
		$client->setUsername(ADYEN_USERNAME);
		$client->setXApiKey(ADYEN_API_KEY);
		$client->setPassword(ADYEN_PASSWORD);
		$client->setEnvironment(ADYEN_ENVIRONMENT);

		$service = new \Adyen\Service\Payment($client);
		
		$importo = $addebito['Addebito']['importo'];
		$importo = (int)(100*$importo);
		
		$params = [
			'amount' => [
				'value' => $importo,
				'currency' => 'EUR'
			],
			'reference' => 'Saldo Zolle '.$addebito['Addebito']['mese'].'/'.$addebito['Addebito']['anno'],
			'merchantAccount' => ADYEN_MERCHANT,
			//"shopperIP" => "61.294.12.12",
			'shopperReference' => $addebito['Addebito']['cliente_id'],
			'selectedRecurringDetailReference' => 'LATEST',
			'recurring' => [
				'contract' => 'RECURRING'
			],
			'shopperInteraction' => 'ContAuth',
			'recurringProcessingModel' => 'CardOnFile'
		];
		$recapiti = $this->ClientiUtil->getRecapitiPerTipo($addebito['Cliente']['Recapito'], 'A');
		if(!empty($recapiti) && !empty($recapiti[0]) && $recapiti[0] != 'cliente.senza.mail@zolle.it' ) {
			$params['shopperEmail'] = $recapiti[0]; // The shopper's email address. We recommend you provide this data, as it is used in velocity fraud checks.
		}
			
		try {
			$result = $service->authorise($params);
			if($result['resultCode'] == 'Authorised') {
				$pagamento['esito_banca'] = 'OK';
				$pagamento['response_errors'] = '';
				$pagamento['response_details'] = json_encode($result);
				
				$addebito['Addebito']['last_payment_ok'] = 1;
				$addebito['Addebito']['blocked'] = NULL; // sbloccata
			}
			else {
				$pagamento['esito_banca'] = 'KO';
				$pagamento['response_errors'] = json_encode($result);
				$pagamento['response_details'] = '';
				
				$addebito['Addebito']['last_payment_ok'] = 0;
				$addebito['Addebito']['blocked'] = NULL; // sbloccata
				
				if( sizeof($addebito['PagamentoCarta']) > 0 ) { // NOTA: è il numero senza il pagamento attuale
					// Tentativo di pagamento già effettuato in precedenza. Invia mail di 'recupero'
					$this->_invia_email_recupero($addebito);
				}
				else {
					// primo tentativo di pagamento. Email standard
						
					//notifica il cliente
					$this->_invia_email_spesa_ko($addebito, '');
				}
			}
		}
		catch(Exception $e) {
			$pagamento['request_errors'] = json_encode($e);
			$addebito['Addebito']['last_payment_ok'] = 0;
			// Nota: NON sblocco l'addebito
		}
		
		// aggiorna su db
		$this->PagamentoCarta->Addebito->saveAll(array(
			'Addebito' => $addebito['Addebito'],
			'PagamentoCarta' => array(
				$pagamento
			)
		));
		
		return;
	}
		
	/**
	 * 
	 */
	function _pagaConKeyclient($addebito) {
		return; // KEYCLIENT DEFINITVAMENTE SPENTO
		$c = empty($addebito['CartaDiCredito']) ? array() : $addebito['CartaDiCredito'];
		if( empty($c['pan']) || empty($c['scadenza_pan']) ) return; // non posso pagare
		
		// esegui il pagamento
		$addebito_id = $addebito['Addebito']['id'];
		
		// 1. crea un nuovo pagamento (necessario perchè i transaction id inviati a keyclient devono essere sempre diversi
		$pagamento = array(); 
		$pagamento['saldo_id'] = $addebito_id;
		$pagamento['transaction_id'] = $addebito['Addebito']['cliente_id'].'-'.$addebito_id.'-'.(sizeof($addebito['PagamentoCarta'])+1);
		
		// 2. setta l'addebito come blocked.  
		// Se l'operazione andrà a buon fine (nessun errore di rete o db) sarà sbloccato solo a quel punto
		$addebito['Addebito']['blocked'] = 1;
		
		// salva
		if(! $this->PagamentoCarta->Addebito->saveAll(array(
			'Addebito' => $addebito['Addebito'],
			'PagamentoCarta' => array(
				$pagamento
			)
		) ) ) return; // salvataggio non avvenuto, mi fermo.
		
		$pagamento['id'] = $this->PagamentoCarta->id; // MANDATORY, usato successivamente per aggiornare il pagamento
		
		// 3. esegui il pagamento vero e proprio
		$recapiti = $this->ClientiUtil->getRecapitiPerTipo($addebito['Cliente']['Recapito'], 'A'); // il recapito mi serve come parametro del pagamento
		$request_url = $this->MyUtil->get_payment_url_recurrent(
			$pagamento['transaction_id'],
			$addebito['Addebito']['importo'],
			$addebito['CartaDiCredito']['id_contratto'],
			$recapiti[0],
			$addebito['CartaDiCredito']['scadenza_pan']
		);
		
		$HttpSocket = new HttpSocket();
		$response = $HttpSocket->post($request_url['base_url'], $request_url['rel_path']);
		
		// processa il risultato
		$pagamento['request_details'] = $request_url['rel_path'];
		
		if($response == false) {
			$addebito['Addebito']['last_payment_ok'] = 0;
			$pagamento['request_errors'] = 'ERRORE DI COMUNICAZIONE';
		}
		else {
			//controlla che l'operazione sia andata a buon fine a livello http
			if($response->code != 200) {
				$addebito['Addebito']['last_payment_ok'] = 0;
				$pagamento['request_errors'] = 'ERRORE DI COMUNICAZIONE';
			}
		}

		if( empty($pagamento['request_errors']) ) {
			
			//richiesta di pagamento andata a buon fine
			
			$body = $HttpSocket->response['raw']['body'];
			
			$response_msg =  $this->_get_response_from_body($body);
			if(strpos($response_msg, '?') !== false) {
				//non si sono verificati errori nella richiesta di pagamento. Leggi i dettagli del pagamento
				$response_msg = substr( $response_msg, strpos($response_msg,'?')+1);
				$splitted_response = $this->_split("&",$response_msg,false);
				
				$payment_params = array();
				foreach($splitted_response as $row) {
					if(strlen($row) > 0) { //skip empty pairs
						$values = $this->_split("=",$row,true);
						$payment_params[$values[0]] = $values[1];
					}
				}

				//processa il pagamento e verifica che effettivamente $response_msg contenga la risposta con il pagamento
				$res = $this->_handle_bank_response($payment_params, RICORRENTE);
				if(!empty($res)) {
					$pagamento['response_errors'] = $res['response_errors'];
					$pagamento['response_details'] = $res['response_details'];
					$pagamento['esito_banca'] = $res['esito'];

					//processa l'esito
					if($res['esito'] == 'OK') {
						$addebito['Addebito']['last_payment_ok'] = 1;
						$addebito['Addebito']['blocked'] = NULL; // sbloccata
					}
					else if($res['esito'] == 'KO') {
						
						$addebito['Addebito']['last_payment_ok'] = 0;
						$addebito['Addebito']['blocked'] = NULL; // sbloccata
						
						if( sizeof($addebito['PagamentoCarta']) > 0 ) { // NOTA: è il numero senza il pagamento attuale
							// Tentativo di pagamento già effettuato in precedenza. Invia mail di 'recupero'
							$this->_invia_email_recupero($addebito);
						}
						else {
							// primo tentativo di pagamento. Email standard
						
							//controlla se la carta è scaduta per notificarlo al cliente - generalmente una carta di credito è valida fino
							//all'ultimo giorno del mese specificato
							$cc_anno_scadenza = intval( substr($addebito['CartaDiCredito']['scadenza_pan'], 0, 4) );
							$cc_mese_scadenza = intval( substr($addebito['CartaDiCredito']['scadenza_pan'], 4) );
							//controllo il primo giorno del mese successivo
							if($cc_mese_scadenza == 12)
							{
								$cc_mese_scadenza = 1;
								$cc_anno_scadenza++;
							}
							if( time() >= mktime(0, 0, 0, $cc_mese_scadenza, 1, $cc_anno_scadenza, -1) )
							{
								//carta di credito scaduta
								$additional_msg = ' in quanto la carta di credito '.$addebito['CartaDiCredito']['pan'].' utilizzata risulta scaduta (AAAAMM: '.$addebito['CartaDiCredito']['scadenza_pan'].')';
							}
							else $additional_msg = '';

							//notifica il cliente
							$this->_invia_email_spesa_ko($addebito, $additional_msg);
						}
					}				
					else if($res['esito'] == '') {
						$addebito['Addebito']['last_payment_ok'] = 0;
						// Nota: NON sblocco l'addebito
					}
				}
				else {
					$pagamento['response_errors'] = '';
					$pagamento['response_details'] = '';
					$pagamento['esito'] = 'Errore';
					
					$addebito['Addebito']['last_payment_ok'] = 0;
					// Nota: NON sblocco l'addebito
				}
			}
			else {
				//si e' verificato un errore nella richiesta di pagamento
				$pagamento['esito'] = 'Errore';
				
				$addebito['Addebito']['last_payment_ok'] = 0;
				// Nota: NON sblocco l'addebito
				
				if($response_msg != "") $pagamento['response_errors'] = $response_msg;
				else {
					//la risposta della banca contiene generalmente dei redirect, quindi per poterla visualizzare modifico
					//il codice html in plain text
					$body = str_replace("<", " ", $body);
					$body = str_replace(">", " ", $body);

					$pagamento['response_errors'] = 'Parametri del pagamento non ricevuti<BR>Risposta banca:'.$body;
				}
			}
		}

		// aggiorna su db
		$this->PagamentoCarta->Addebito->saveAll(array(
			'Addebito' => $addebito['Addebito'],
			'PagamentoCarta' => array(
				$pagamento
			)
		));
		
		return;
	}
	
	/**
	 * 
	 */
	function _invia_email_spesa_ko($addebito, $additional_msg) {
		
		$recapiti = $this->ClientiUtil->getRecapitiPerTipo($addebito['Cliente']['Recapito'], 'A');
		foreach($recapiti as $recapito) {
			$Email = new CakeEmail('mandrillapp');
			$Email->from(array('amministrazione@zolle.it' => 'Le Zolle'));
			$Email->to($recapito);
			$Email->subject(__("Avviso Le Zolle"));
			$Email->setHeaders(array(
				'X-MC-Metadata' => '{"id_cliente": "'.$addebito['Cliente']['id'].'", "id_cliente_fatturazione": "'.$addebito['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
			));
			$Email->emailFormat('html');
			$Email->template('spesa_mensile_ko', 'default');
			$Email->viewVars(array(
				'addebito' => $addebito,
				'additional_msg' => $additional_msg
			));
			if(INVIA_MAIL) $Email->send();
		}
	}

	/**
	 * 
	 */
	function _invia_email_recupero($addebito) {
		$recapiti = $this->ClientiUtil->getRecapitiPerTipo($addebito['Cliente']['Recapito'], 'A');
		foreach($recapiti as $recapito) {
			$Email = new CakeEmail('mandrillapp');
			$Email->from(array('amministrazione@zolle.it' => 'Le Zolle'));
			$Email->to($recapito);
			$Email->subject(__("Avviso Le Zolle"));
			$Email->setHeaders(array(
				'X-MC-Metadata' => '{"id_cliente": "'.$addebito['Cliente']['id'].'", "id_cliente_fatturazione": "'.$addebito['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
			));
			$Email->emailFormat('html');
			$Email->template('spesa_mensile_recupero', 'default');
			$Email->viewVars(array(
				'addebito' => $addebito,
				'additional_msg' => ""
			));
			if(INVIA_MAIL) $Email->send();
		}
	}
	
	/*
	 @param $response_body Body della risposta HTTP della banca (dove sono presenti eventuali messaggi di errore)
	*/
	function _get_response_from_body($response_body)
	{
		/*NOTE: quando si usa il campo 'url' nella richiesta, la banca comunica i parametri del pagamento o eventuali errori verificatisi
		  con un Javascript (di redirezione) del tipo:
			<SCRIPT LANGUAGE="JavaScript">

			window.location.href="https://ecommerce.cim-italia.it/ecomm/ErrorMessage.jsp?messaggio=NUMERO CONTRATTO NON PRESENTE IN ARCHIVIO<BR>"

			</SCRIPT>
		 
		 Per la scarsa comunicazione con KeyClient, non sappiamo se ogni messaggio sia contenuto in uno script di redirezione, ma i
		 messaggi che ci interessano lo sono tutti.		
		*/

		//NOTA: BISOGNA SEMPRE CERCARE L'ULTIMA OCCORRENZA PERCHE' IN CASO DI ERRORE L'USO DEL CAMPO 'url' PUO' DETERMINARE PIU' REDIREZIONI!!!
		$pos = strrpos($response_body, 'window.location.href');
		if($pos === false)
		{
			//restituisci l'ultimo (NOTA: SEMPRE L'ULTIMO PER IL PROBLEMA DELLE REDIREZIONI) body trovato
			$pos_start = strrpos($response_body, '<body');
			$pos_stop = strpos($response_body, '</body>',$pos_start);
			return substr($response_body, $pos_start, $pos_stop-$pos_start+7);
		}

		$pos_start = strpos($response_body, '"', $pos) + 1;
		if($pos_start === false) return 'ERRORE SCONOSCIUTO';
		$pos_stop = strpos($response_body, '"', $pos_start+1); //end of string
		return substr($response_body, $pos_start, $pos_stop-$pos_start);
	}
	
	//REPLACEMENT FOR PHP SPLIT (WHICH IS DEPRECATED)
	function _split($pattern, $data, $getAlsoEmptyTokens) {
		$result = array();
		
		if(empty($data) || $data == '') return $result;

		do
		{
			$pos = strpos($data, $pattern);
			if($pos === false)
			{
				array_push($result, $data);
				break;
			}
			else if($pos == 0)
			{
				if($getAlsoEmptyTokens) array_push($result, "");
			}
			else
			{
				array_push($result, substr($data,0,$pos));
			}

			if($pos == strlen($data)-strlen($pattern))
			{
				if($getAlsoEmptyTokens) array_push($result,"");
				break;
			}

			$data = substr($data, $pos+strlen($pattern));
		}
		while(true);

		return $result;
	}
	
	/*
	 @param $params Parametri di del pagamento ricevuti dal server
	 @param $type Tipo di pagamento ('non ricorrente', 'primo pagamento', 'ricorrente')
	
	Note: il metodo viene richiamato da 'esito_pagamento_ricorrente' che e' l'URL a cui il server invia i parametri del pagamento
	*/
	function _handle_bank_response($params, $type) {
		//verifica che la risposta contenga l'id della transazione
		if(!array_key_exists('codTrans',$params)) //la risposta NON contiene il parametro 'codTrans'
		{
			//invia email all'operatore per notificargli la transazione anomala
			$msg = 'Ricevuta transazione con parametro codTrans assente nella risposta dalla banca:<BR>'.$this->_implode_with_key($params,false);
			Configure::load('zolle');
			$emails = Configure::read('Zolle');		
			foreach($emails as $email) $this->_sendInternalEmail(
				$email,
				'Eseguita transazione anomala', 
				$msg);
			return null;
		}

		//ottieni il pagamento a cui si riferisce la risposta della banca
		$pagamento = $this->PagamentoCarta->find('first', array(
			'conditions' => array(
				'transaction_id' => $params['codTrans']
			),
			'contain' => array(
				'Addebito' => array(
					'Cliente'
				)
			),
			'order' => array('PagamentoCarta.id DESC')
		));
		if(empty($pagamento))
		{
			//invia email all'operatore per notificargli la transazione anomala
			$msg = 'Transazione non presente nel database:<BR>'.$this->_implode_with_key($params,false);
			Configure::load('zolle');
			$emails = Configure::read('Zolle');		
			foreach($emails as $email) $this->_sendInternalEmail(
				$email,
				'Eseguita transazione anomala',
				$msg);
			return null;
		}

		$toReturn = array('esito' => '', 'response_details' => '', 'response_errors' => '');

		//memorizza la risposta della banca
		$toReturn['response_details'] = $this->_implode_with_key($params,false);

		//verifica che la risposta contenga l'esito
		if(array_key_exists('esito',$params))
		{
			//aggiorna il record 'esito_banca' con la risposta della banca
			$toReturn['esito'] = $params['esito'];	
			
			//verifica se i parametri di risposta del pagamento coincidono con quelli memorizzati
			$err = $this->_check_payment_params($pagamento['PagamentoCarta']['transaction_id'], $pagamento['Addebito']['importo'], $params, $type);

			if($err == '')
			{
				if($type == PRIMO_PAGAMENTO)
				{
					//memorizza la data di firma del contratto 'signed', 'pan' e 'scadenza pan'
					$contratto = $this->PagamentoCarta->Addebito->Cliente->CartaDiCredito->find('first', array(
						'conditions' => array(
							'id_contratto' => $params['num_contratto']
						)
					));

					if($params['esito'] == 'OK') 
					{
						//memorizza l'attivazione del contratto (che e' contestuale al primo pagamento)
						$contratto['CartaDiCredito']['signed'] = date('Y-m-d H:i:s');
				
						//memorizza scadenza pan - richiesto per l'esecuzione dei pagamenti ricorrenti 
						if(array_key_exists('scadenza_pan',$params)) $contratto['CartaDiCredito']['scadenza_pan'] = $params['scadenza_pan'];
	
						//memorizza se presente anche il pan (non necessario al fine dei pagamenti)
						if(array_key_exists('pan',$params)) $contratto['CartaDiCredito']['pan'] = $params['pan'];

						//salva le modifiche
						if(!$this->PagamentoCarta->Addebito->Cliente->CartaDiCredito->save($contratto))
						{
							//notifica l'operatore sull'avvenuta attivazione del contratto via mail
							$msg = 'Si e\' verificato un errore nella memorizzazione dell\'attivazione del contratto del cliente'.
								$spesa['Customer']['id'].'.Inserire manualmente l\'attivazione del contratto alla data attuale
								con scandenza_pan ='.$params['scadenza_pan'].' e pan='.$params['pan'];
							Configure::load('zolle');
							$emails = Configure::read('Zolle');		
							foreach($emails as $email) $this->_sendInternalEmail(
								$email,
								'Attivare manualmente contratto',
								$msg);
						}
					}
				}
			}
			else 
			{
				$toReturn['response_errors'] = $err;
			}
		}
		else
		{
			$toReturn['response_errors'] = 'Parametro esito assente nella risposta della banca<BR>';
		}

		return $toReturn;
	}
	
	function _implode_with_key($vett,$inline) {
		$str = '';
		
		foreach($vett as $elem)
		{
			$str .= key($vett);
			$str .= ': ';
			$str .= $elem;
			if($inline) $str .= ' - ';
			else $str .= '<BR>';
			next($vett);
		}
		return $str;
	}
	
	/*check payment response parameters
	 @param $pagamento Il pagamento a cui i parametri si riferiscono
	 @param $spesa Spesa corrente
	 @param $type Tipo di pagamento (non ricorrente, primo pagamento, ricorrente)
	 @param $cliente Cliente a cui la spesa si riferisce
	 @param $params Parametri di risposta restituiti dalla banca
	 return '' or string with details about wrong field
	*/
	function _check_payment_params($transaction_id, $importo, $params, $type) {
		//controlla i campi comuni a tutti i tipi di pagamento
		if(!array_key_exists('mac',$params))
			return 'Campo \'MAC\' mancante';
		
		//check mac (SEE COMMENTS ON 'MyUtil->get_mac_to_be_received()' FOR DETAILS)
		$computed_mac = $this->MyUtil->get_mac_to_be_received($transaction_id, $importo, $params);
		$pos = strrpos($computed_mac,"%");
		if($pos === false) $pos = strlen($computed_mac);
		$computed_mac_to_check = substr($computed_mac,0,$pos);

		$pos = strrpos($params['mac'],"%");
		if($pos === false) $pos = strlen($params['mac']);
		$received_mac_to_check = substr($params['mac'],0,$pos);

		if($computed_mac_to_check != $received_mac_to_check) 
			return 'Campo \'MAC\' errato<BR>ricevuto: '.$received_mac_to_check.'<BR>memorizzato: '.$computed_mac_to_check;

		if(!array_key_exists('importo',$params))
			return 'Campo \'Importo\' mancante';
		$importo = (int)(100*$importo);
		if($importo < 100)
		{
			//normalizza l'importo (almeno 3 caratteri)
			while(strlen($importo) < 3) $importo = '0'.$importo;
		}
		if($importo != $params['importo']) //check importo
			return 'Campo \'importo\' errato<BR>ricevuto: '.$params['importo'].'<BR>memorizzato: '.$importo;
		if(!array_key_exists('divisa',$params)) 
			return 'Campo \'divisa\' mancante';
		if('EUR' != $params['divisa']) //check divisa
			return 'Campo \'divisa\' errato<BR>ricevuto: '.$params['divisa'].'<BR>memorizzato: EUR';
		//TODO: controllare in qualche modo 'data' e 'orario' ???
		
		//NOTA: non controllo codTrans perche' lo faccio gia' per chiamare questo metodo (che altrimenti non potrebbe essere chiamato)	

		if($type == NON_RICORRENTE || $type == PRIMO_PAGAMENTO)
		{
			//TODO: check nome, cognome, email ?
		}
		if($type == RICORRENTE)
		{
			if(!array_key_exists('num_contratto',$params)) 
				return 'Campo \'num_contratto\' mancante';
			/*if($cliente['id_contratto'] != $params['num_contratto']) //check num_contratto
				return 'Campo \'num_contratto\' errato<BR>ricevuto: '.$params['num_contratto'].'<BR>memorizzato: ';*/
		}

		return '';
	}
}
