<?php

class Recapito extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'clienti_recapiti';

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
	
	public function getAllEmails($cliente_id) {
		$res = $this->find('all', [
			'conditions' => [
				'Recapito.cliente_id' => $cliente_id,
				'Recapito.tipo' => 'email'
			]
		]);
		$emails = [];
		foreach($res as $r) {
			$emails[] = $r['Recapito']['RECAPITO'];
		}
		
		return $emails;
	}
	
}

 
