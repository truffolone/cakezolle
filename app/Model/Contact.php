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
    class Contact extends AppModel{
		
		// mi serve per compatibilità con il messaging ma uso un barbatrucco e lo cortocircuito sugli utenti (contact_id = cliente_id)
		public $useTable = 'clienti';
		
		public $belongsTo = array(
			'Persona' => array(
				'className' => 'Persona',
				'foreignKey' => 'id'
			),
		);
		
		public function afterFind($results, $primary = false) {
			foreach ($results as $key => $val) {
				// barbatrucchi per il messaging
				$model = 'Contact';
				if( isset($results[$key]['Attivita']) ) $model = 'Attivita';
				$results[$key][$model]['persona_id'] = $results[$key][$model]['id'];
				 $results[$key][$model]['legendastati_id'] = 0;
				 $results[$key][$model]['rental_date'] = '';
				 $results[$key][$model]['return_date'] = '';
			}
			return $results;
		}

		
}
?>
