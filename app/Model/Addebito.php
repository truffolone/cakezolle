<?php

class Addebito extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'addebiti';

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
		'CartaDiCredito' => array(
			'className' => 'CartaDiCredito',
			'foreignKey' => 'carta_id',
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
		'AutorizzazioneRid' => array(
			'className' => 'AutorizzazioneRid',
			'foreignKey' => 'rid_id',
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
		'Bonifico' => array(
			'className' => 'Bonifico',
			'foreignKey' => 'bonifico_id',
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
		'Contante' => array(
			'className' => 'Contante',
			'foreignKey' => 'contante_id',
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
		'ProceduraLegale' => array(
			'className' => 'ProceduraLegale',
			'foreignKey' => 'legale_id',
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
	
	public $hasMany = array(
		'PagamentoCarta' => array(
			'className' => 'PagamentoCarta',
			'foreignKey' => 'saldo_id',
			'dependent' => false,
			//'type' => 'inner'
			'conditions' => '',
			'order' => 'created DESC',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);
	
}

 
