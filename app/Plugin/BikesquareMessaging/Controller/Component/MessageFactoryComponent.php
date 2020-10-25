<?php 

App::uses('Component', 'Controller');

include APP . "Plugin/BikesquareMessaging/Controller/Component/MessageFactory/ContactMessage.php";
include APP . "Plugin/BikesquareMessaging/Controller/Component/MessageFactory/ConversationMessage.php";
include APP . "Plugin/BikesquareMessaging/Controller/Component/MessageFactory/DefaultConversationMessage.php";

class MessageFactoryComponent extends Component {

	/**
	 *
	 */
	public function forContact(int $contact_id): ContactMessage {
		return new ContactMessage($contact_id);
	}
	
	/**
	 * usato solo per aggiornare conversazioni esistenti
	 */
	public function forConversation(int $conversation_id): ConversationMessage {
		return new ConversationMessage($conversation_id);
	}

	/**
	 * versione semplificata di tutto il processo di comunicazione:
	 * 
	 * - una sola chat per ogni contact (contact_id = conversation_id)
	 * - all'interno della chat posso inviare un messaggio a tutti o solo ad alcuni 
	 */
	public function forDefaultConversation(int $contact_id): DefaultConversationMessage {
		return new DefaultConversationMessage($contact_id);
	}
} 
