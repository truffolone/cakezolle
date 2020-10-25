<?php

	App::uses('CakeEmail', 'Network/Email', 'CakeSession');
	
	//include APP . "Plugin/BikesquareMessaging/Controller/Component/MessageFactory/ContactMessage.php";

	class ConversationMessage extends ContactMessage {
		
		private $conversation_id; 
		
		public function __construct(int $aConversation_id) {
			parent::__construct($this->getContactId($aConversation_id));
			$this->conversation_id = $aConversation_id;
		} 
		
		public function sendWithEmail($sendEmail=true) {
			// sovrascrivi from con l'utente loggato
			$loggedUser = CakeSession::read('Auth.User');
			$this->from = [
				'User' => $loggedUser
			];
			// sovrascrivi to con i partecipanti
			$ConversationModel = ClassRegistry::init('BikesquareMessaging.Conversation');
			$ConversationModel->recursive = -1;
			$conversation = $ConversationModel->find('first', [
				'conditions' => ['Conversation.id' => $this->conversation_id],
				'contain' => ['Participant' => ['fields' => ['id', 'username', USER_ROLE_KEY, 'password']]]
			]);
			$this->to = [];
			foreach($conversation['Participant'] as $p) {
				if($p['id'] != $loggedUser['id']) {
					$this->to[] = [
						'User' => $p
					];
				}
			}
			
			parent::sendWithEmail($sendEmail);
		}
		
		/**
		 * 
		 */
		protected function getTargetConversationId(array $recipient_ids): int {
			return $this->conversation_id;
		}
		
		/**
		 * 
		 */
		private function getContactId($conversation_id) {
			$ConversationModel = ClassRegistry::init('BikesquareMessaging.Conversation');
			$ConversationModel->recursive = -1;
			$conversation = $ConversationModel->findById($conversation_id);
			if(empty($conversation)) return null;
			return $conversation['Conversation']['attivita_id'];
		}

	}
