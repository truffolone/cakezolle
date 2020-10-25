<?php

class ContantiController extends AppController {

	var $name = "Contanti";

	public $uses = array('Contante', 'Cliente');
	
	public function beforeFilter() {
		
		parent::beforeFilter();
				
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
		
		$cliente['Contante'][0]['cliente_id'] = $cliente_id;
		
		if( $this->Cliente->saveAll($cliente) ) {
			
			// attiva automaticamente il metodo di pagamento
			$this->requestAction('/clienti/attiva_metodo_pagamento/'.$cliente_id.'/'.CONTANTI.'/'.$this->Contante->getLastInsertID());
			
			// ottieni i recapiti a cui inviare il messaggio
			$cliente = $this->Cliente->findById($cliente_id);
			$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'A');
			foreach($recapiti as $recapito) {
				// invia mail di benvenuto
				$Email = new CakeEmail('mandrillapp');
				$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
				$Email->to($recapito);
				$Email->subject(__("Le Zolle - pagamento con Contanti"));
				$Email->setHeaders(array(
					'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['id'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
				));
				$Email->emailFormat('html');
				$Email->template('info_contanti', 'default');
				$Email->viewVars(array(
					'nomeCliente' => $cliente['Cliente']['displayName'],
				));
				//$Email->send();
			}
			
			if($requested) {
				return true;
			}
			else {
				$this->Session->setFlash('Metodo contanti aggiunto correttamente', 'flash_ok');
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
	
	/*
	 * 
	 */
	public function edit($id) {
		
		$c = $this->Contante->findById($id);

		if(empty($c)) throw new NotFoundException('Metodo di pagamento non trovato');

		$this->set('c', $c);
		
		if( !empty($this->request->data) ) {
			
			if( $this->Contante->save($this->request->data) ) {
				$this->Session->setFlash('Contante memorizzato correttamente', 'flash_ok');
				$this->redirect(array('action' => 'edit', $id));
			}
			else {
				$this->Session->setFlash('Errore salvataggio', 'flash_error');
			}
			
			return;
			
		}
		
		$this->request->data = $c;
	}
	
	/**
	 * 
	 */
	public function confirm_delete($id) {
		
		$c = $this->Contante->findById($id);
		
		if(empty($c)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		$this->set('c', $c);
		
	}
	
	/**
	 * 
	 */
	public function delete($id) {
		
		$c = $this->Contante->findById($id);
		if( empty($c) ) throw new NotFoundException('Contante non trovato');
		if( $this->Contante->delete($id) ) {
			$this->Session->setFlash('Contante rimosso correttamente', 'flash_ok');
		}
		else {
			$this->Session->setFlash('Errore durante cancellazione', 'flash_error');
		}
		
		$this->redirect( array('controller' => 'clienti', 'action' => 'view', $c['Cliente']['id']) );
	}
	
}
