<?php

class Contratto extends AppModel {

	public $actsAs = array('Containable');

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
		'ClienteFatturazione' => array(
			'className' => 'Cliente',
			'foreignKey' => 'cliente_fatturazione_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
	
	public $validate = array(
		'cliente_id' => array(
			'required' => true,
			'rule' => array('naturalNumber', false),
			'message' => 'Inserire un ID valido (numerico)',
		),
		'cliente_fatturazione_id' => array(
			'required' => true,
			'rule' => array('naturalNumber', false),
			'message' => 'Inserire un ID valido (numerico)',
		),
		// non fa parte del contratto, uso per validare in fase di creazione nuovo contratto
		'metodo_pagamento' => array(
			'required' => true,
			'rule' => array('naturalNumber', false),
			'message' => 'Selezionare un metodo di pagamento',
		),
	);
	
}
 
