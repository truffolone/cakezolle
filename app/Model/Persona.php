<?php

App::uses('AppModel', 'Model', 'CakeSession');

/**
 * Created by PhpStorm.
 * User: Lorenzo
 * Date: 22/04/2016
 * Time: 16:35
 */
/**
 * Created by PhpStorm.
 * User: merix
 * Date: 04/12/2015
 * Time: 21:00
 */
	/* per compatibilità con il messaging*/
    class Persona extends AppModel{
		
		// mi serve per compatibilità con il messaging ma uso un barbatrucco e lo cortocircuito sugli utenti (contact_id = user_id)
		public $useTable = 'clienti';
		
		// stessa associazione di Cliente
		public $hasMany = array(
			'User' => array( // in realtà ne ha uno solo ....
				'className' => 'User',
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
			)
		);
		
		public function afterFind($results, $primary = false) {
			foreach ($results as $key => $val) {
				// barbatrucchi per il messaging
				$results[$key]['Persona']['Nome'] = $results[$key]['Persona']['NOME'];
				$results[$key]['Persona']['Cognome'] = $results[$key]['Persona']['COGNOME'];
				$results[$key]['Persona']['DisplayName'] = "";
			}
			return $results;
		}

		
}
?>
