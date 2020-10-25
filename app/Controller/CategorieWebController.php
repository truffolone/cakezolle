<?php

class CategorieWebController extends AppController {

	var $name = "CategorieWeb";

	public $uses = array('CategoriaWeb');
	
	public $helpers = array();

	public $components = array();


	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->layout = 'mangio';
	}

	/*
	 */
	public function index() {
		
		// 2019-02-13: blocco per visualizzare al login il messaggio di richiesta ri-attivazione su adyen
		$contrattoDaRiattivare = $this->Session->read('contrattoDaRiattivare');
		if($contrattoDaRiattivare != null) {
			$firstVisitToCategorieAfterLogin = $this->Session->read('firstVisitToCategorieAfterLogin');
			$this->set('displayAdyenReminder', $firstVisitToCategorieAfterLogin === null); // occhio all'uso di === !!!
			$this->Session->write('firstVisitToCategorieAfterLogin', false);
		}
		else {
			$this->set('displayAdyenReminder', false);
		}
		
		$env = $this->Session->read('env');
		
		// se la modalità di visualizzazione per l'ambiente corrente è "quick" redireziono alla lista articoli con
		// uno specifico parametro
		$shopping_type = Configure::read('shopping_type.' . strtolower($env));
		if($shopping_type == null) $shopping_type = 'normal'; // default
		if(!in_array($shopping_type, array('normal', 'quick'))) $shopping_type = 'normal'; // default
		
		// importante! resetta la chiave di ricerca (perchè il pulsante 'reset' del form di ricerca è un link a questo url)
		$this->Session->write('searchKeyword', null); 
		
		if($shopping_type == 'quick') { // visualizzazione articoli in quick list
			$this->redirect(array('controller' => 'articoli', 'action' => 'index'));
		} 
	}

} 
