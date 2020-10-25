<?php

class BonificiController extends AppController {

	var $name = "Bonifici";

	public $uses = array('Bonifico', 'Cliente');
	
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
		
		$cliente['Bonifico'][0]['cliente_id'] = $cliente_id;
		
		if( $this->Cliente->saveAll($cliente) ) {
			
			// attiva automaticamente il metodo di pagamento
			$this->requestAction('/clienti/attiva_metodo_pagamento/'.$cliente_id.'/'.BONIFICO.'/'.$this->Bonifico->getLastInsertID());
			
			// ottieni i recapiti a cui inviare il messaggio
			$cliente = $this->Cliente->findById($cliente_id);
			$recapiti = $this->ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'A');
			foreach($recapiti as $recapito) {
				// invia mail di benvenuto
				$Email = new CakeEmail('mandrillapp');
				$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
				$Email->to($recapito);
				$Email->subject(__("Le Zolle - pagamento con Bonifico"));
				$Email->setHeaders(array(
					'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['id'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
				));
				$Email->emailFormat('html');
				$Email->template('info_bonifico', 'default');
				$Email->viewVars(array(
					'nomeCliente' => $cliente['Cliente']['displayName'],
				));
				//$Email->send();
			}
			
			if($requested) {
				return true;
			}
			else {
				$this->Session->setFlash('Bonifico aggiunto correttamente', 'flash_ok');
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
		
		$b = $this->Bonifico->findById($id);

		if(empty($b)) throw new NotFoundException('Metodo di pagamento non trovato');

		$this->set('b', $b);
		
		if( !empty($this->request->data) ) {
			
			if( $this->Bonifico->save($this->request->data) ) {
				$this->Session->setFlash('Bonifico memorizzato correttamente', 'flash_ok');
				$this->redirect(array('action' => 'edit', $id));
			}
			else {
				$this->Session->setFlash('Errore salvataggio', 'flash_error');
			}
			
			return;
			
		}
		
		$this->request->data = $b;
	}
	
	/**
	 * 
	 */
	public function confirm_delete($id) {
		
		$b = $this->Bonifico->findById($id);
		
		if(empty($b)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		$this->set('b', $b);
		
	}
	
	/**
	 * 
	 */
	public function delete($id) {
		
		$b = $this->Bonifico->findById($id);
		if( empty($b) ) throw new NotFoundException('Bonifico non trovato');
		if( $this->Bonifico->delete($id) ) {
			$this->Session->setFlash('Bonifico rimosso correttamente', 'flash_ok');
		}
		else {
			$this->Session->setFlash('Errore durante cancellazione', 'flash_error');
		}
		
		$this->redirect( array('controller' => 'clienti', 'action' => 'view', $b['Cliente']['id']) );
	}
	
	
}
