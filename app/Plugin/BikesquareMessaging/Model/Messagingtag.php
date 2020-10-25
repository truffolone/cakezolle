<?php

class Messagingtag extends BikesquareMessagingAppModel {	
    
    var $actsAs = array('Containable');
    
    public $useTable = 'messaging_messagingtags';
    
    /**
     * restituisce come array associativo tutti i tag visibili da un certo gruppo di utenti
     */
    public function getVisibleTags($group_id) {
		$res = $this->find('all', array(
			'joins' => array(
				array(
					'table' => 'messaging_messagingtag_usergroup_view',
					'alias' => 'muv',
					'type' => 'INNER',
					'conditions' => array(
						'Messagingtag.id = muv.tag_id',
						'muv.'.USER_ROLE_KEY => $group_id
					)
				)
			),
			'recursive' => -1
		));
		// inseriscili in un array associativo
		$visible_tags = array();
		foreach($res as $r) {
			$visible_tags[ $r['Messagingtag']['id'] ] = $r;
		}
		
		return $visible_tags;
	}
	
	/**
	 * restituisce la lista di tag utilizzabili da un certo gruppo di utenti
	 */
	public function getUsableTags($group_id) {
		$res = $this->find('list', array(
			'joins' => array(
				array(
					'table' => 'messaging_messagingtag_usergroup_use',
					'alias' => 'muu',
					'type' => 'INNER',
					'conditions' => array(
						'Messagingtag.id = muu.tag_id',
						'muu.'.USER_ROLE_KEY => $group_id
					)
				)
			),
			'recursive' => -1
		));
		
		return $res;
	}
	
	
    
    public $hasAndBelongsToMany = array(
        'UsedBy' =>
            array(
                'className' => 'Group',
                'joinTable' => 'messaging_messagingtag_usergroup_use',
                'foreignKey' => 'tag_id',
                'associationForeignKey' => USER_ROLE_KEY,
                'unique' => true,
                'order' => 'name',
                /*'conditions' => '',
                'fields' => '',
                'limit' => '',
                'offset' => '',
                'finderQuery' => '',
                'with' => ''*/
            ),
		'ViewedBy' =>
            array(
                'className' => 'Group',
                'joinTable' => 'messaging_messagingtag_usergroup_view',
                'foreignKey' => 'tag_id',
                'associationForeignKey' => USER_ROLE_KEY,
                'unique' => true,
                'order' => 'name',
                /*'conditions' => '',
                'fields' => '',
                'limit' => '',
                'offset' => '',
                'finderQuery' => '',
                'with' => ''*/
            ),
	);
    
    public $validate = array(
		'name' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'Campo obbligatorio'
			),
		),
		'color' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'Campo obbligatorio'
			),
			'hexColor' => array(
				'rule' => '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/',
				'message' => 'Il colore deve essere specificato in formato esadecimale'
			)
		),
	);
    
}
