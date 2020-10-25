<?php

class Indirizzo extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'clienti_indirizzi';

	public $belongsTo = array(
		'Cliente' => array(
			'className' => 'Cliente',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);
	
}

 
