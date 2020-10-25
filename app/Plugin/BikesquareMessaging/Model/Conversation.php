<?php

App::uses('AppModel', 'Model', 'CakeSession');

class Conversation extends BikesquareMessagingAppModel {	
    
    var $actsAs = array('Containable');
    
    public $useTable = 'messaging_conversations';
    
    public $belongsTo = array(
		'Attivita' => array(
			'className' => 'Contact',
			'foreignKey' => 'attivita_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'author_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
    );
    
    public $hasMany = array(
		'Message' => array(
			'className' => 'BikesquareMessaging.Message',
			'foreignKey' => 'conversation_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
    );
    
    public $hasAndBelongsToMany = array(
        'Participant' =>
            array(
                'className' => 'User',
                'joinTable' => 'messaging_conversations_users',
                'foreignKey' => 'conversation_id',
                'associationForeignKey' => 'user_id',
                'unique' => true,
                'fields' => array('id', 'username', USER_ROLE_KEY) // su bikesquare id(persona) = id(utente)
                //'order' => 'name',
                /*'conditions' => '',
                'fields' => '',
                'limit' => '',
                'offset' => '',
                'finderQuery' => '',
                'with' => ''*/
            ),
		'Tag' =>
            array(
                'className' => 'BikesquareMessaging.Messagingtag',
                'joinTable' => 'messaging_conversations_tags',
                'foreignKey' => 'conversation_id',
                'associationForeignKey' => 'tag_id',
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
	
	public function ensureParticipantsExist($conversation_id, $participant_ids) {
		$conversation = $this->find('first', [
			'conditions' => ['Conversation.id' => $conversation_id],
			'contain' => ['Participant' => ['fields' => ['id']]]
		]);
		$this->query("DELETE FROM messaging_conversations_users WHERE conversation_id = " . $conversation_id);
		$participants = [];
		if(!isset($conversation['Participant'])) $conversation['Participant'] = []; 
		foreach($conversation['Participant'] as $p) {
			$participants[] = $p['id'];
		}
		foreach($participant_ids as $p_id) {
			$participants[] = $p_id;
		}
		$participants = array_unique($participants);
		$this->saveAll([
			'Conversation' => ['id' => $conversation_id],
			'Participant' => ['Participant' => $participants]
		]);
	}
	
	/**
	 * a seconda dell'utente loggato genera (se ancora non esistono) le chat di "default" associate al contatto
	 * indicato
	 */
	public function ensureDefaultParticipants($contact_id) {
		$ContactModel = ClassRegistry::init('Contact');
		$UserModel = ClassRegistry::init('User');
		$ContactModel->recursive = 2; // per zolle mi serve recursive 2
		$contact = $ContactModel->findById($contact_id);
		if(empty($contact)) return; // nothing to do
		
		$customer = $contact['Persona']['User'][0]['id'];
		
		$default_participants = [
			-1, // info@bikesquare.eu
			$customer, // il cliente,
		];
		
		if($contact['Contact']['legendastati_id'] > 0) {
			//$bams = $UserModel->getIds( $UserModel->getAllBam($contact_id) );
			$renters = $UserModel->getIds( $UserModel->getAllRenter($contact_id) );
			$admins = $UserModel->getIds( $UserModel->getAllAdmin() );
			
			// importante: dai renters togli gli admins (se nn esistono renters restituisce bam o admin) in quanto 
			// admin è solo info@bikesquare a livello di chat
			$clean_renters = [];
			foreach($renters as $r) {
				if(!in_array($r, $admins)) $clean_renters[] = $r;
			}
			$renters = $clean_renters;
			
			
			$default_participants = array_merge($default_participants, $renters);
			// IMPORTANTE: deve partecipare alla conversazione anche chi gestisce il contatto!
			if( !empty($contact['Contact']['user_id']) ) {
				$default_participants[] = $contact['Contact']['user_id'];
			}
		}
		
		$this->query("DELETE FROM messaging_conversations_users WHERE conversation_id = " . $contact_id); // rimuovo i partecipanti esistenti (serve se ad es. cambio il poi e quindi il noleggiatore)
		$this->ensureParticipantsExist($contact_id, $default_participants);
		
	}
	
	
	/**
	 * usato per individuare una determinata chat
	 * 
	 * restituisce (generandola prima se non esiste) la conversazione:
	 * - associata al contact passato
	 * - i cui partecipanti sono l'utente loggato e i recipients (per l'utente loggato) dei messaggi
	 * 
	 * In questo modo viene garantità l'univocità della conversazione tra gli stessi utenti (= uso sempre la 
	 * stessa conversazione)
	 */
	public function getChat(int $contact_id, int $from, array $rcpts, $subject=null): int {
		$participants = $rcpts; 
		$participants[] = $from;
		asort($participants); // necessario per il successivo check
		
		// individua se esiste la conversazione tra i partecipanti indicati per il contact indicato
		$res = $this->query("SELECT cu.conversation_id AS conversation_id, 
			GROUP_CONCAT(DISTINCT cu.user_id ORDER BY cu.user_id SEPARATOR '-') AS participant_list 
			FROM messaging_conversations_users AS cu
			INNER JOIN messaging_conversations AS c
			ON cu.conversation_id = c.id 
			AND c.attivita_id = $contact_id
			GROUP BY conversation_id HAVING participant_list = '".implode('-', $participants)."' ORDER BY conversation_id");
		
		if(sizeof($res) > 0) {
			// trovata una o più conversazioni valide, in teoria dovrebbe essere una (ma potrebbero esserci conversazioni
			// vecchie "spurie"), restituisci la prima che trovi (in ordine)
			return $res[0]['cu']['conversation_id'];
		} 
		else {
			// non esiste ancora una chat tra gli utenti indicati, creala
			if( $this->saveAll([
				'Conversation' => [
					'subject' => $subject ? $subject : "",
					'author_id' => $from, // dato che la sto creando con questa chiamata uso convenzionalmente from come author
					'attivita_id' => $contact_id
				],
				'Participant' => [
					'Participant' => $participants
				]
			]) ) {
				return $this->getLastInsertId();
			}
			else { // errore salvataggio
				return null; 
			}
		}
	}
	
	/**
	 * restituisce il numero di chat in cui l'utente partecipa
	 */
	public function getChatNumForLoggedUser() {
		$user = CakeSession::read('Auth.User');
		return $this->getChatNum($user['id']);
	}
	
	/**
	 * 
	 */
	public function getChatNum($user_id) {
		return $this->find('count', [
			'joins' => [
				[
					'table' => 'messaging_conversations_users',
					'alias' => 'mcu',
					'type' => 'INNER',
					'conditions' => [
						'Conversation.id = mcu.conversation_id',
						'mcu.user_id' => $user_id,
					]
				]
			]
		]);
	}
	
	/**
	 * restituisce tutte le conversazioni dell'utente corrente per il contratto specificato
	 */
	public function getAllInContact($contact_id) {
		$user = CakeSession::read('Auth.User');
		return $this->find('all', [
			'conditions' => [
				'Conversation.attivita_id' => $contact_id
			],
			'recursive' => -1,
			'contain' => [
				'Participant' => ['Persona'],
				// per ottenere lato vista il numero di messaggi da leggere
				'Message' => [
					'fields' => ['Message.id'],
					'conditions' => [
						'Message.is_received' => 1,
						'Message.is_read' => 0,
						'Message.to_id' => $user['id']
					]
				]
			],
			'joins' => [
				[
					'table' => 'messaging_conversations_users',
					'alias' => 'mcu',
					'type' => 'INNER',
					'conditions' => [
						'Conversation.id = mcu.conversation_id',
						'mcu.user_id' => $user['id'],
					]
				]
			]
		]);
	}
    
  public function getConversationsOfUser($uid)
  {
    //TODO: Pulire da sql injection
    $result =  $this->query("SELECT Conversation.* FROM 
                          messaging_conversations Conversation JOIN messaging_conversations_users mc  on (Conversation.id = mc.conversation_id)
                          WHERE user_id=$uid
                          ORDER BY modified DESC
                          ");
    foreach ($result as &$r)
    {
      $conversation_id = $r['Conversation']['id'];
      $r['Conversation']['num_unread'] = $this->Message->getUnreadNumByConversation($uid, $conversation_id);
    }

    return $result;
  }

}
 
