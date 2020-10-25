<?php

class PagamentoCarta extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'pagamenti_carta';

	public $belongsTo = array(
		'Addebito' => array(
			'className' => 'Addebito',
			'foreignKey' => 'saldo_id',
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

 
