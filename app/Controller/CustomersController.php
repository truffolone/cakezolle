<?php

/**
 * mi serve per redirezionare i vecchi link a contratti già esistenti (computazionalmente meno pesante rispetto
 * ad impostare delle route ad hoc)
 * 
 */

class CustomersController extends AppController {

	var $name = "Customers";

	public $uses = false;
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow('*'); // tutto pubblico in questo controller
		
		$this->layout = 'mangio';
	}
	
	/**
	 * 
	 */
	public function visualizza_contratto($id_contratto) {
		
		$this->redirect( array('controller' => 'carte_di_credito', 'action' => 'contratto', $id_contratto) );
		
	}
	
	/**
	 * 
	 */
	public function visualizza_contratto_rid($id_contratto) {
		
		$this->redirect( array('controller' => 'autorizzazioni_rid', 'action' => 'contratto', $id_contratto) );
		
	}
	
	/**
	 * 
	 */
	public function info_contratto($id_contratto) {
		
		$this->redirect( array('controller' => 'carte_di_credito', 'action' => 'info_contratto', $id_contratto) );
		
	}
	
	/**
	 * 
	 */
	public function info_contratto_rid($id_contratto) {
		
		$this->redirect( array('controller' => 'autorizzazioni_rid', 'action' => 'info_contratto', $id_contratto) );
		
	}

} 
