<?php

class Message extends BikesquareMessagingAppModel {	
    
    var $actsAs = array('Containable');
    
    public $useTable = 'messaging_messages';
    
    /**
     * restituisce gli ultimi n messaggi (in ordine) tra quelli non letti
     */
    public function getUnread($user_id, $num) {
		$messages = $this->find('all', array(
			'conditions' => array(
				'to_id' => $user_id,
				'is_received' => 1,
				'is_read' => 0
			),
			'recursive' => -1,
			'contain' => array(
				'Conversation',
				'From' => array(
					'fields' => array('id', 'first_name')
				)
			),
			'limit' => $num,
			'order' => array('Message.created DESC')
		));
		return $messages;
	}
    
    /**
     * 
     */
    public function getUnreadNum($user_id) {
		
		$num = $this->find('count', array('conditions' => array(
			'to_id' => $user_id,
			'is_received' => 1,
			'is_read' => 0
		)));
		return $num;
	}
	
	/**
	 * 
	 */
	public function getUnreadNumByAttivita($user_id, $attivita_id) {
		
		$num = $this->find('count', array(
			'conditions' => array(
				'to_id' => $user_id,
				'is_received' => 1,
				'is_read' => 0
			),
			'joins' => array(
				array(
					'table' => 'messaging_conversations',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Message.conversation_id = c.id',
						'c.attivita_id' => $attivita_id
					)
				),
			)
		));
		return $num;
	}
	
	/**
	 * 
	 */
	public function getUnreadNumByTag($user_id, $tag_id) {
		
		$num = $this->find('count', array(
			'conditions' => array(
				'to_id' => $user_id,
				'is_received' => 1,
				'is_read' => 0
			),
			'joins' => array(
				array(
					'table' => 'messaging_conversations',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Message.conversation_id = c.id'
					)
				),
				array(
					'table' => 'messaging_conversations_tags',
					'alias' => 'ct',
					'type' => 'INNER',
					'conditions' => array(
						'c.id = ct.conversation_id',
						'ct.tag_id' => $tag_id
					)
				),
			)
		));
		return $num;
	}
	
		/**
	 * 
	 */
	public function getUnreadNumByConversation($user_id, $conversation_id) {
		
		$num = $this->find('count', array(
			'conditions' => array(
				'to_id' => $user_id,
				'is_received' => 1,
				'conversation_id' => $conversation_id,
				'is_read' => 0
			),			
		));		
		return $num;
	}
    
    public $belongsTo = array(
		'Conversation' => array(
			'className' => 'BikesquareMessaging.Conversation',
			'foreignKey' => 'conversation_id',
			'dependent' => false,
			'fields' => '',
			'order' => ''
		),
		'From' => array(
			'className' => 'User',
			'foreignKey' => 'from_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
		// To come associazione non mi serve perchè so già che il destinatario è l'utente corrente
    );
    
    public $hasAndBelongsToMany = array(
        'Attachment' =>
            array(
                'className' => 'BikesquareMessaging.Attachment',
                'joinTable' => 'messaging_messages_attachments',
                'foreignKey' => 'message_id',
                'associationForeignKey' => 'attachment_id',
                'unique' => true,
                'order' => 'name',
                /*'conditions' => '',
                'fields' => '',
                'limit' => '',
                'offset' => '',
                'finderQuery' => '',
                'with' => ''*/
            )
	);
    
		
	public function getLatestConversation($uid)
  {
			$result = $this->find('first',[
						'conditions' => [
							'to_id' => $uid
						],
						'contain' => ['Conversation'],
						'order' => 'Message.modified DESC'
			]);
			if(empty($result)) {
				$ConversationModel = ClassRegistry::init('BikesquareMessaging.Conversation');
				$result = $ConversationModel->find('first',[
						'conditions' => [
							'author_id' => $uid
						],
						'recursive' => -1,
						'order' => 'Conversation.modified DESC'
				]);
			}

			if (!empty($result))
			{
				return $result['Conversation'];
			}
			return null;
  }
		
	public function markRead($conversation_id, $uid)
	{
		$this->updateAll(array('is_read' => 1), array('conversation_id' => $conversation_id, 'to_id' => $uid));
	}

	/**
	 * 
	 */
	public function getUnreadNumClosed($user_id) {
		
		$num = $this->find('count', array(
			'conditions' => array(
				'to_id' => $user_id,
				'is_received' => 1,
				'is_read' => 0
			),
			'joins' => array(
				array(
					'table' => 'messaging_conversations',
					'alias' => 'c',
					'type' => 'INNER',
					'conditions' => array(
						'Message.conversation_id = c.id',
						'c.closed <>' => null
					)
				)
			)
		));
		return $num;
	}
}
 
