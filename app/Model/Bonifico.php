<?php

class Bonifico extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'bonifici';

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
	
	
}

 
