<?php 
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class BikesquareMessagingAppController extends AppController {
	
	public $uses = array(
		'Contact',
		'Persona',
		'User',
		'Partner'
	);

	public $helpers = ['Text', 'BikesquareMessaging.Conversation'];
	
	public $components = ['BikesquareMessaging.MessageFactory'];
	
	public function beforeFilter() {
		parent::beforeFilter();
		/*$user = $this->Session->read('Auth.User');
		if( empty($user) || $user[USER_ROLE_KEY] == ROLE_USER ) {
			$this->layout = 'prenota';
		}
		else {
			$this->layout = 'default';
		}*/
	}
	
	/**
	 * 
	 */
	function _getUserDisplayName($user) {
		if( empty($user['Persona']) ) return $user['username'];
		$personaName = trim($user['Persona']['Nome'].' '.$user['Persona']['Cognome']);
		if(empty($personaName)) { // prova ad usare il display name
			$personaName = trim($user['Persona']['DisplayName']);
		}
		if(!empty($personaName)) {
			return $personaName;
		}
		return $user['username'];
	}
	
	/**
	 * 
	 */
	function _getListaPersone($persona_ids) {
		$this->loadModel('Persona');
		$persone = $this->Persona->find('all', array(
			'recursive' => -1,
			'fields' => ['id', 'Cognome', 'Nome', 'DisplayName'], // non posso usare find(list) perchè in molti casi il DisplayName è vuoto
			'conditions' => array('Persona.id' => $persona_ids)));
		$personeList = [];
		foreach($persone as $p) {
			$p = $p['Persona'];
			$personeList[ $p['id'] ] = trim( $p['Cognome'].' '.$p['Nome'] );
			if( empty($personeList[ $p['id'] ]) ) { // usa il display name
				$personeList[ $p['id'] ] = trim($p['DisplayName']);
			}
		}
		return $personeList;
	}
	
	/**
	 * 
	 */
	function _loggedUserIsAdmin() {
		$user = $this->Session->read('Auth.User');
		if( empty($user) ) return false;
		
		$group = $user[USER_ROLE_KEY];
		
		return $group == ROLE_ADMIN;
	}
	
	/**
	 * 
	 */
	function _getContractsAccessibleByCurrUser() {
		
		// la logica è già la stessa applicata in tutte le altre sezioni del prenota in Contact::beforeFind
		$attivitas = $this->Contact->find('all', [
			'recursive' => -1,
			'contain' => ['Persona']
		]);
		
		// componi i contratti come lista
		$attivitaList = [];
		foreach($attivitas as $a) {
			$attivitaList[ $a['Contact']['id'] ] = $a['Contact']['id'] . ' - ' . (empty($a['Persona']) ? 'n.d.' : $a['Persona']['Cognome'].' '.$a['Persona']['Nome'] );
		}
		return $attivitaList;
	}
	
	/**
	 * 
	 */
	private function _getPartners($destination_id = null) {
		$conditions = [];
		if($destination_id) {
			$conditions['User.destination_id'] = $destination_id;
		}
		$users = $this->User->find('all', [
			'contain' => ['Persona'],
			'conditions' => $conditions,
			'joins' =>  [[
				'table' => 'partners',
				'alias' => 'pp',
				'type' => 'INNER',
				'conditions' => [
					'User.id = pp.id' // persona_id = user_id = partner_id
				]
			]]
		]);
		$partners = [];
		foreach($users as $u) {
			$partners[ $u['User']['id'] ] = $this->_getUserDisplayName($u['User']);
		}
		asort($partners);
		return $partners;
		
	}
	
	/**
	 * alcune classi di utenti (es. i clienti) NON possono scegliere a chi inviare un messaggio,
	 * i destinatari vengono selezionati automaticamente
	 * 
	 * @return array semplice (id -> nome) degli utenti, vuoto se per l'utente non sono previsti destinatari di default
	 */
	function _getDefaultRecipients($user, $attivita_id = null) {
		
		$recipients = [];
		switch($user[USER_ROLE_KEY]) {
			case ROLE_USER:
				// invia a tutti i bam del territorio per l'attivita scelta. Se attività vuota manda a tutti gli admin
				$destination_id = null;
				if($attivita_id) { // specificato contratto
					// ottieni il territorio al quale il contratto si riferisce
					$contact = $this->Contact->findById($attivita_id);
					if(!empty($contact)) {
						$destination_id = $contact['Contact']['destination_id'];
					}
					// else l'utente non può accedere al contratto specificato
				}
				if($destination_id) {
					// individua i bam disponibili
					$recipients = $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], ['destination_id' => $destination_id], true);
					/*if(empty($recipients)) {
						// i default recipients sono tutti gli admin disponibili
						$recipients = $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true);
					}*/
					// almeno in questa prima fase aggiungo sempre di default anche tutti gli admin
					$recipients = array_merge($recipients, $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], ['destination_id' => $destination_id], true));
				}
				else {
					// i default recipients sono tutti gli admin disponibili
					$recipients = $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true);
				}
				
				break;
			default:
				$recipients = []; // per tutti gli altri tipi di utente non sono previsti destinatari di default
		}
		return $recipients;
	}
	
	/**
	 * restituisce i possibili destinatari di un messaggio per un certo utente separati per tipo
	 */
	function _getAllowedRecipients($user, $attivita_id = null) {
		
		$recipients = [];
		
		/*$destination_id = null;
		if($attivita_id) { // specificato contratto
			
			// ottieni il territorio al quale il contratto si riferisce
			$contact = $this->Contact->findById($attivita_id);
			if(!empty($contact)) {
				$destination_id = $contact['Contact']['destination_id'];
			}
			// else l'utente non può accedere al contratto specificato
		
			if( $user[USER_ROLE_KEY] == ROLE_ADMIN ) {
				// può comunicare con tutti gli altri amministratori
				$amministratori = $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true);
				// può comunicare con tutti i bam della destination associata al contratto
				$bams = $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], ['destination_id' => $destination_id], true);
				// può comunicare con tutti gli utenti eon
				$eons = $this->_getAllUsersByGroup([ROLE_EON, ROLE_EON_PLUS], $user['id'], [], true);
				// può comunicare con tutti i noleggiatori della destination associata al contratto
				$noleggiatori = $this->_getAllUsersByGroup(ROLE_RENTER, $user['id'], ['destination_id' => $destination_id], true);
				// può comunicare con il cliente associato al contratto (NOTA: persona_id = user_id)
				$clienti = $this->_getAllUsersByGroup(ROLE_USER, $user['id'], ['User.id' => $contact['Contact']['persona_id']], true);
				// può comunicare con tutti i partner
				$partners = $this->_getPartners();
				
				$recipients = array(
					__('Amministratori') => $amministratori,
					__('Bam') => $bams,
					__('Eon') => $eons,
					__('Noleggiatori') => $noleggiatori,
					__('Clienti') => $clienti,
					__('Partners') => $partners			
				);
			}
			else if( $user[USER_ROLE_KEY] == ROLE_BAM ) { 
				// può comunicare con tutti gli altri amministratori
				$amministratori = $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true);
				// può comunicare con tutti i bam della destination associata al contratto
				$bams = $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], ['destination_id' => $destination_id], true);
				// può comunicare con tutti i noleggiatori della destination associata al contratto
				$noleggiatori = $this->_getAllUsersByGroup([ROLE_RENTER, ROLE_EON_PLUS], $user['id'], ['destination_id' => $destination_id], true);
				// può comunicare con il cliente associato al contratto (NOTA: persona_id = user_id)
				$clienti = $this->_getAllUsersByGroup(ROLE_USER, $user['id'], ['User.id' => $contact['Contact']['persona_id']], true);
				// può comunicare con tutti i partner del suo territorio
				$partners = $this->_getPartners($user['destination_id']);
				
				$recipients = array(
					__('Amministratori') => $amministratori,
					__('Bam') => $bams,
					__('Noleggiatori') => $noleggiatori,
					__('Clienti') => $clienti,
					__('Partners') => $partners				
				);
			}
			else if( $user[USER_ROLE_KEY] == ROLE_EON ) { 
				// può comunicare con tutti gli altri amministratori
				$amministratori = $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true);
				// può comunicare con tutti gli altri eon
				$eons = $this->_getAllUsersByGroup(ROLE_EON, $user['id'], [], true);
				
				$recipients = array(
					__('Amministratori') => $amministratori,
					__('Eon') => $eons
				);
			}
			else if( in_array($user[USER_ROLE_KEY],[ROLE_EON, ROLE_EON_PLUS])) {
				// può comunicare con:
				// - amministratori
				// - il suo bam
				// - clienti di contratti di cui è azienda, gestore o commerciale
				$recipients = array(
					__('Amministratori') => $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true),
					__('Bam') => $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], ['destination_id' => $user['destination_id']], true),
					// TODO: aggiungi i clienti
				);
			}
			else if( $user[USER_ROLE_KEY] == ROLE_USER ) {
				// può comunicare con tutti gli altri amministratori
				$amministratori = $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true);
				// può comunicare con tutti i bam della destination associata al contratto
				$bams = $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], ['destination_id' => $destination_id], true);
				
				$recipients = array(
					__('Amministratori') => $amministratori,
					__('Bam') => $bams,
				);
			}
		}
		else { // nessun contratto specifico
			if( $user[USER_ROLE_KEY] == ROLE_ADMIN ) { // può comunicare con tutti gli utenti
				$recipients = array(
					__('Amministratori') => $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true),
					__('Bam') => $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], [], true),
					__('Eon') => $this->_getAllUsersByGroup([ROLE_EON, ROLE_EON_PLUS], $user['id'], [], true),
					__('Noleggiatori') => $this->_getAllUsersByGroup(ROLE_RENTER, $user['id'], [], true),
					__('Clienti') => $this->_getAllUsersByGroup(ROLE_USER, $user['id'], [], true),
					// può comunicare con tutti i partner
					__('Partners') => $this->_getPartners()			
				);
			}
			else if( $user[USER_ROLE_KEY] == ROLE_BAM ) { // può comunicare con gli amministratori e con tutti gli utenti del suo territorio
				$recipients = array(
					__('Amministratori') => $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true),
					__('Bam') => $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], ['destination_id' => $user['destination_id']], true),
					__('Noleggiatori') => $this->_getAllUsersByGroup([ROLE_RENTER, ROLE_EON_PLUS], $user['id'], ['destination_id' => $user['destination_id']], true),
					__('Clienti') => $this->_getAllUsersByGroup(ROLE_USER, $user['id'], ['destination_id' => $user['destination_id']], true),
					// può comunicare con tutti i partner del suo territorio
					__('Partners') => $this->_getPartners($user['destination_id'])			
				);
			}
			else if( in_array($user[USER_ROLE_KEY], [ROLE_EON, ROLE_EON_PLUS,ROLE_RENTER] )) { // può comunicare con gli amministratori e con tutti gli altri eon
				$recipients = array(
					__('Amministratori') => $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true),
					__('Bam') => $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], ['destination_id' => $user['destination_id']], true)
				);
			}
			else if( $user[USER_ROLE_KEY] == ROLE_USER ) {
				// può comunicare con
				// - tutti gli amministratori
				// - tutti i bam (il cliente non ha un destination perchè potrebbe prenotare per più destinations)
				
				$recipients = array(
					__('Amministratori') => $this->_getAllUsersByGroup(ROLE_ADMIN, $user['id'], [], true),
					__('Bam') => $this->_getAllUsersByGroup(ROLE_BAM, $user['id'], [], true),
				);
			}
		}*/

		return $recipients;
	}
	
	/**
	 * 
	 */
	 function _getAllUsersByGroup($group_id, $curr_user_id, $extraConditions, $showPersona = false) {
		 /*$extraConditions[USER_ROLE_KEY] = $group_id;
		 $extraConditions['User.id <>'] = $curr_user_id;
		 
		if($showPersona) {
			$res1 = $this->User->find('all', array(
				'conditions' => $extraConditions,
				'contain' => array(
					'Persona' => array(
						'fields' => array('id', 'DisplayName', 'Cognome', 'Nome')
					)
				)
			));
			$res = array();
			foreach($res1 as $r) {
				$r['User']['Persona'] = $r['Persona'];
				unset($r['Persona']);
				$res[ $r['User']['id'] ] = $this->_getUserDisplayName($r['User']);
			}
			asort($res);
		}
		else {
			$res = $this->User->find('list', array(
				'conditions' => $extraConditions,
				'order' => 'username'
			));
		}
		return $res;*/
		return [];
	}
	
	/**
	 * INVIO NOTIFICHE MAIL DEL MESSAGING
	 */
	function _send_notification($isNewConversation, $conversationID, $sender, $subject, $message, $recipient_id) {
		if( !MESSAGING_SEND_EMAIL_NOTIFICATION ) return;
		
		$sender = $this->User->findById($sender['id']);
		$senderUser = $sender['User'];
		
		$recipient = $this->User->findById($recipient_id);
		$recipientUser = $recipient['User'];
		
		$emails = [];
		foreach(['username'] as $emailKey) {
			if(!empty($recipientUser[$emailKey]) && filter_var($recipientUser[$emailKey], FILTER_VALIDATE_EMAIL) ) { // senza la validazione cakemail si arrabbia di brutto se non è un indirizzo valido
				$emails[] = $recipientUser[$emailKey];
			}
		}
		
		// importante! rimuovi eventuali doppioni (per evitare di mandare troppe notifiche inutili)
		$emails = array_unique($emails);
		
		foreach($emails as $address) {
			$Email = new CakeEmail();
			$Email->from(array('noreply@bikesquare.eu' => 'Bikesquare'));
			$Email->to($address);
			$Email->subject($subject ? $subject : __('Nuovo messaggio da un utente'));
			$Email->emailFormat('html');
			$Email->template('BikesquareMessaging.messaging_message', 'default');
			$Email->attachments([
					'BikeSquare_BtoC_EON.png' => [
						'file' => IMAGES . Configure::read('logo-mail'),
						'mimetype' => 'image/png',
						'contentId' => '12345',
					],
			]);
			$Email->viewVars(array(
				'isNewConversation' => $isNewConversation,
				'conversationID' => $conversationID,
				'sender' => $sender,
				'recipient' => $recipient,
				'subject' => $subject,
				'message' => $message
			));
			try {
				if( $Email->send() ) {
					CakeLog::write('emails_info', "Notifica messaging inviata con successo a ".$address);
				}
				else {
					CakeLog::write('emails_error', "Errore durante invio notifica messaging a ".$address);
				}
			}
			catch(Exception $e) {
				CakeLog::write('emails_error', "Errore durante invio notifica messaging a ".$address. ' '.$e->getMessage());
			}
		}
		
	}
	
} 
