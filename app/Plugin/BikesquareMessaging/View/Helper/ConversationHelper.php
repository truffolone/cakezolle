<?php
/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Helper', 'View');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class ConversationHelper extends AppHelper {
	
	public $helpers = ['User'];
	
	public function getTitle($conversation) {
		
		if(is_numeric($conversation)) {
			$conversation = $this->fetchConversation($conversation);
		}
		else if(!isset($conversation['Attivita'])) {
			$conversation = $this->fetchConversation( isset($conversation['Conversation']) ? $conversation['Conversation']['id'] : $conversation['id'] );
		}
		
		if(empty($conversation)) return __("Richiesta info");
		if(!isset($conversation['Attivita']) || empty($conversation['Attivita'])) {
			return empty($conversation['Conversation']['subject']) ? __("Richiesta info") : $conversation['Conversation']['subject'];
		}
		else {
			$subjectPrefix = $this->User->displayName($conversation['Attivita']['persona_id']);
			$subjectSuffix = '';
			if( !empty($conversation['Attivita']['Destination']) ) {
				$subjectSuffix = ' - '.$conversation['Attivita']['Destination']['name'];
			}
				
			if( $conversation['Attivita']['legendastati_id'] == 0 ) {
				// richiesta info, uso il titolo originale se presente
				return empty($conversation['Conversation']['subject']) ? $subjectPrefix.' - '.__("Richiesta info").$subjectSuffix : $subjectPrefix.' - '.$conversation['Conversation']['subject'].$subjectSuffix;
			}
			else {
				// prenotazione
				return $subjectPrefix.' - '.__("Prenotazione del %s", $conversation['Attivita']['created']).$subjectSuffix;
			}
		}
	}
	
	private function fetchConversation($conversation_id) {
		$ConversationModel = ClassRegistry::init('BikesquareMessaging.Conversation');
		$ConversationModel->recursive = -1;
		return $ConversationModel->find('first', [
			'conditions' => ['Conversation.id' => $conversation_id],
			'contain' => ['Attivita' => ['Destination', 'fields' => ['id', 'created', 'legendastati_id', 'persona_id', 'destination_id']]]
 		]);
	}
	
}
