<?php

class ContractActivationTicket extends AppModel
{
	var $name='ContractActivationTicket';
	
	public $actsAs = array('Containable');
	
	public $useTable = 'ticket_attivazione_carte';
	
	public $belongsTo = array(
		'CartaDiCredito' => array(
			'className' => 'CartaDiCredito',
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
