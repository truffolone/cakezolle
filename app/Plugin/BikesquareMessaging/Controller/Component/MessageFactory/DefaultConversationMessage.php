<?php

	App::uses('CakeEmail', 'Network/Email', 'CakeSession');
	
	/**
	 * versione semplificata di tutto il processo di comunicazione:
	 * 
	 * - una sola chat per ogni contact (contact_id = conversation_id)
	 * - all'interno della chat posso inviare un messaggio a tutti o solo ad alcuni 
	 */
	class DefaultConversationMessage extends ContactMessage {
		
		public function __construct(int $aContact_id) {
			parent::__construct($aContact_id);
		} 
		
		public function sendWithEmail($sendEmail=true) {
			
			// qualunque cosa succeda bikesquare (= admin) deve essere SEMPRE tra i destinatari
			if( 
				$this->from['User']['id'] != -1 // non posso mandare un messaggio a me stesso!
				&&
				$this->from['User']['id'] != -2 // se lo sta mandando il bot non ho bisogno di aggiugermi!
			) { 
				$this->toBikesquare();
			}
			
			// se la conversazione di default non esiste creala
			$ConversationModel = ClassRegistry::init('BikesquareMessaging.Conversation');
			$ConversationModel->recursive = -1;
			$conversation = $ConversationModel->find('first', [
				'conditions' => ['Conversation.id' => $this->contact_id],
				'contain' => ['Participant' => ['fields' => ['id']]]
			]);
			$participant_ids = [];
			if($this->from['User']['id'] != -2) { // altrimenti è il bot mittente
				$participant_ids[] = $this->from['User']['id'];
			}
			foreach($this->to as $to) {
				$participant_ids[] = $to['User']['id'];
			}
			if(empty($conversation)) { 
				// primo messaggio per il contatto, crea conversazione con i destinatari correnti
				// come partecipanti iniziali
				 $ConversationModel->saveAll([
					'Conversation' => [
						'id' => $this->contact_id,
						'subject' => $this->subject,
						'author_id' => $this->from['User']['id'],
						'attivita_id' => $this->contact_id // rimane per retro compatibilità
					],
					'Participant' => [
						'Participant' => $participant_ids
					]
				 ]);
			}
			else {
				// conversazione già esistente. 
				// Se uno o più destinatari non fanno parte dei partecipanti (admin li ha aggiunti successivamente) aggiungili
				$ConversationModel->ensureParticipantsExist($this->contact_id, $participant_ids);
			}
			
			parent::sendWithEmail($sendEmail);
		}
		
		/**
		 * sovrascritto da contact message
		 */
		public function fromAdmin(): ContactMessage {
			return $this->fromBikesquare();
		}
		
		/**
		 * sovrascritto da contact message
		 */
		public function toAdmin(): ContactMessage {
			return $this->toBikesquare();
		}
		
		/**
		 * sovrascritto da contact message: restituisce sempre la stessa conversazione (quella appunto di default)
		 */
		protected function getTargetConversationId(array $recipient_ids): int {
			return $this->contact_id;
		}
		
	}
