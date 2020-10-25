<?php

class ClienteZolla extends AppModel {
    
    public $useDbConfig = 'zolla';
    
    public $useTable = 'clienti';

	public $primaryKey = 'ID_CLIENTE';
	
	public $actsAs = array('Containable');

	public $hasMany = array(
		'RecapitoZolla' => array(
			'className' => 'RecapitoZolla',
			'foreignKey' => 'ID_CLIENTE',
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
		),
		'IndirizzoZolla' => array(
			'className' => 'IndirizzoZolla',
			'foreignKey' => 'ID_CLIENTE',
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
		),
		'SpesaZolla' => array(
			'className' => 'SpesaZolla',
			'foreignKey' => 'ID_CLIENTE',
		),
		'AcquistoMercatoLiberoZolla' => array(
			'className' => 'AcquistoMercatoLiberoZolla',
			'foreignKey' => 'ID_CLIENTE',
		),
		'AcquistoArticoloFissoZolla' => array(
			'className' => 'AcquistoArticoloFissoZolla',
			'foreignKey' => 'ID_CLIENTE',
		),
	);

}

