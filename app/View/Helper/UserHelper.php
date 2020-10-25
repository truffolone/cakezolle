<?php
/**
 * Image Helper class file.
 *
 * Generate an image with a specific size.
 *
 *
 * @package       Cake.View.Helper
 * @since         CakePHP(tm) v 1.1
 */
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('AppHelper', 'View/Helper');

/**
 * Image Helper class for generate an image with a specific size.
 *
 * ImageHelper encloses 2 method needed while resizing images.
 *
 * @package       View.Helper
 */
class UserHelper extends AppHelper{

    public $helpers = array('Html','Form');

	/**
	 * 
	 */
	public function displayName($user) {
		if(is_numeric($user)) {
			$UserModel = ClassRegistry::init('User');
			$UserModel->recursive = -1;
			$user = $UserModel->find('first', [
				'conditions' => ['User.id' => $user],
				'contain' => 'Persona'
			]);
		}
		if(!isset($user['User'])) $user['User'] = $user;
		
		if( !isset($user['Persona']) ) {
			$UserModel = ClassRegistry::init('User');
			$UserModel->recursive = -1;
			$user = $UserModel->find('first', [
				'conditions' => ['User.id' => $user['User']['id']],
				'contain' => 'Persona'
			]);
		}
		if(empty($user)) return "";
		
		$displayName = "";
		if(isset($user['Persona']['Nome']) && isset($user['Persona']['Cognome'])) {
			$displayName = $user['Persona']['Nome']. " " .$user['Persona']['Cognome'];
		}
		if(empty(trim($displayName))) {
			if(isset($user['Persona']['Societa'])) {
				$displayName = $user['Persona']['Societa'];
			}
		}
		if(empty(trim($displayName))) {
			if(isset($user['Persona']['DisplayName'])) {
				$displayName = $user['Persona']['DisplayName'];
			}
		}
		if(empty(trim($displayName))) {
			if(isset($user['User']['username'])) {
				$displayName = $user['User']['username'];
			}
		}
		
		return $displayName;
	}

	/**
	 * restituisce la descrizione del ruolo di un determinato utente all'interno di un contratto
	 */
	public function getRoleInContact($user, $contact_id) {
		if(is_numeric($user)) {
			$UserModel = ClassRegistry::init('User');
			$UserModel->recursive = -1;
			$user = $UserModel->findById($user);
			$user = $user['User'];
		}
		if(empty($user)) return null;
		
		if($user['id'] == -1) {
			return [
				'bkg' => 'bg-black',
				'name' => __('Le Zolle srl')
			];
		}
		
		return [
			'bkg' => 'bg-seagreen',
			'name' => __('Cliente')
		];
		
		/*$ContactModel = ClassRegistry::init('Contact');
		$ContactModel->recursive = -1;
		$contact = $ContactModel->findById($contact_id);

		if( $contact['Contact']['user_id'] == $user['id'] ) {
			return [
				'bkg' => 'bg-warning',
				'name' => __('Gestore')
			];
		}
		else if($user[USER_ROLE_KEY] == ROLE_USER) {
			
			if( $contact['Contact']['persona_id'] == $user['id'] ) {
				return [
					'bkg' => 'bg-seagreen',
					'name' => __('Cliente')
				];
			}
		}
		else { // invididua l'esatto ruolo dell'utente per il contratto (perchè l'utente per avendo ad es. ruolo admin potrebbe fungere da noleggiatore)
			$UserModel = ClassRegistry::init('User');
			$renters = $UserModel->getIds( $UserModel->getAllRenter($contact_id) );
			$bams = $UserModel->getIds( $UserModel->getAllBam($contact_id) );
			if( in_array($user['id'], $renters) ) {
				return [
					'bkg' => 'bg-danger',
					'name' => __('Noleggiatore')
				];
			}
			if( in_array($user['id'], $bams) ) {
				return [
					'bkg' => 'bg-warning',
					'name' => __('Area Manager')
				];
			}
			
			if($user[USER_ROLE_KEY] == ROLE_ADMIN && $user['id'] != -1) {
				return [
					'bkg' => 'bg-purple',
					'name' => __('Amministratore')
				];
			}
		}
		return "";*/
	}
}
