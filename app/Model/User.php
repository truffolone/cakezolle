<?php

App::uses('CakeSession', 'Model/Datasource');

class User extends AppModel {

	public $actsAs = array(
		'Containable',
		'Acl' => array('type' => 'requester', 'enabled' => false)
	);
	
	public $belongsTo = array(
		'Group', 
		'Persona' => array(
			'className' => 'Persona',
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
		),);
	
	/**
	 * 
	 */
	public function canShop() {
		$loggedUser = CakeSession::read('Auth.User');
		return !empty($loggedUser) && $loggedUser['group_id'] == CLIENTE_STANDARD;
	}
	
	
	/**
	 * per-group only permissions
	 */
	public function bindNode($user) {
		return array('model' => 'Group', 'foreign_key' => $user['User']['group_id']);
	}

	public function parentNode() {
        if (!$this->id && empty($this->data)) {
            return null;
        }
        if (isset($this->data['User']['group_id'])) {
            $groupId = $this->data['User']['group_id'];
        } else {
            $groupId = $this->field('group_id');
        }
        if (!$groupId) {
            return null;
        }
        return array('Group' => array('id' => $groupId));
    }
	
}

 
