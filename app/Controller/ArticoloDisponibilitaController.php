<?php

class ArticoloDisponibilitaController extends AppController {

	var $name = "ArticoloDisponibilita";

	public $uses = array('ArticoloDisponibilita', 'ArticoloDisponibilitaZolla');
	
	public $helpers = array();

	public $components = array();


	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('aggiorna_da_zolla');
		$this->layout = 'mangio';
	}

	/*
	 */
	public function aggiorna_da_zolla() {
		
		$this->layout = 'ajax';
		
		shell_exec( APP. "Console/cake sync_db_locale_con_zolla sync_articoli_disponibilita > /dev/null 2>/dev/null &");
		
	}

} 
