<?php

class ProcedureLegaliController extends AppController {

	var $name = "ProcedureLegali";

	public $uses = array(
		'ProceduraLegale', 
		'Cliente'
	);
	
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
		$cliente['ProceduraLegale'][0]['cliente_id'] = $cliente_id;
		
		if( $this->Cliente->saveAll($cliente) ) {
			
			// attiva automaticamente il metodo di pagamento
			$this->requestAction('/clienti/attiva_metodo_pagamento/'.$cliente_id.'/'.PROCEDURA_LEGALE.'/'.$this->ProceduraLegale->getLastInsertID());
			
			if($requested) {
				return true;
			}
			else {
				$this->Session->setFlash('Metodo procedura legale aggiunto correttamente', 'flash_ok');
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
		
		$p = $this->ProceduraLegale->findById($id);

		if(empty($p)) throw new NotFoundException('Metodo di pagamento non trovato');

		$this->set('p', $p);
		
		if( !empty($this->request->data) ) {
			
			if( $this->ProceduraLegale->save($this->request->data) ) {
				$this->Session->setFlash('Procedura legale memorizzata correttamente', 'flash_ok');
				$this->redirect(array('action' => 'edit', $id));
			}
			else {
				$this->Session->setFlash('Errore salvataggio', 'flash_error');
			}
			
			return;
			
		}
		
		$this->request->data = $p;
	}
	
	/**
	 * 
	 */
	public function confirm_delete($id) {
		
		$p = $this->ProceduraLegale->findById($id);
		
		if(empty($p)) throw new NotFoundException('Metodo di pagamento non trovato');
		
		$this->set('p', $p);
		
	}
	
	/**
	 * 
	 */
	public function delete($id) {
		
		$p = $this->ProceduraLegale->findById($id);
		if( empty($p) ) throw new NotFoundException('Procedura legale non trovata');
		if( $this->ProceduraLegale->delete($id) ) {
			$this->Session->setFlash('Procedura legale rimossa correttamente', 'flash_ok');
		}
		else {
			$this->Session->setFlash('Errore durante cancellazione', 'flash_error');
		}
		
		$this->redirect( array('controller' => 'clienti', 'action' => 'view', $p['Cliente']['id']) );
	}
	
}
