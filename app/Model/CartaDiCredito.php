<?php

App::import('Component', 'ClientiUtil');

class CartaDiCredito extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'carte_di_credito';

	public $belongsTo = array(
		'Cliente' => array(
			'className' => 'Cliente',
			'foreignKey' => 'cliente_id',
			'dependent' => false,
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
	
	public $hasOne = array(
		'ContractActivationTicket' => array(
			'className' => 'ContractActivationTicket',
			'foreignKey' => 'id',
			'dependent' => false,
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

 
