<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppController', 'Controller');

/**

 */
class ConversationsController extends BikesquareMessagingAppController {

	public $uses = array('Contact', 'BikesquareMessaging.Conversation', 'BikesquareMessaging.Message', 'BikesquareMessaging.Messagingtag');
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		$user = $this->Session->read('Auth.User');
		
		if(!empty($user)) {
			if($user['group_id'] == ROLE_ADMIN) {
				$this->Auth->allow('close', 'edit', 'open', 'update', 'index', 'inbox');
			}
			else {
				$this->Auth->allow('edit', 'index', 'inbox');
			}
		}

		$this->set('activeSection', 'messaging');
	}
	
	/**
	 * rimane per il cliente e per i non admin
	 */
	public function index() {
		
	}
	
	/**
	 * lista json delle conversazioni di un utente con messaggi nuovi ordinate per messaggi da leggere
	 * 
	 * serve per gli utenti non admin
	 */
	public function inbox() {
		$user = $this->Session->read('Auth.User');
		
		$this->RequestHandler->setContent('json', 'application/json');

		/* 
	 	 * Paging
	 	 */
		$sLimit = "";
		$sOffset = "";
		if ( isset( $this->request->query['iDisplayStart'] ) && $this->request->query['iDisplayLength'] != '-1' )
		{
			$sLimit = $this->request->query['iDisplayLength'];
			$sOffset = $this->request->query['iDisplayStart'];
		}
		if(empty($sLimit)) $sLimit = 20;
		if(empty($sOffset)) $sOffset = 0;

		if($this->request->query('inbox')) {
			$res = $this->Conversation->query("SELECT c.id, COUNT(mm.id) AS num_unread
				FROM messaging_conversations AS c
				INNER JOIN messaging_conversations_users AS mcu
				ON c.id = mcu.conversation_id
				AND mcu.user_id = ".$user['id']."
				INNER JOIN messaging_messages AS mm
				ON c.id = mm.conversation_id
				AND mm.to_id = ".$user['id']."
				AND mm.is_received = 1
				AND mm.is_read = 0
				GROUP BY c.id
				ORDER BY num_unread DESC, id DESC
				LIMIT $sLimit 
				OFFSET $sOffset");

			$resTotal = $this->Conversation->query("SELECT COUNT(c.id) AS num
				FROM messaging_conversations AS c
				INNER JOIN messaging_conversations_users AS mcu
				ON c.id = mcu.conversation_id
				AND mcu.user_id = ".$user['id']."
				INNER JOIN messaging_messages AS mm
				ON c.id = mm.conversation_id
				AND mm.to_id = ".$user['id']."
				AND mm.is_received = 1
				AND mm.is_read = 0");
		}
		else { // conversazioni senza messaggi da leggere
			$res = $this->Conversation->query("SELECT DISTINCT c.id
				FROM messaging_conversations AS c
				INNER JOIN messaging_conversations_users AS mcu
				ON c.id = mcu.conversation_id
				AND mcu.user_id = ".$user['id']."
				LEFT JOIN messaging_messages AS mm
				ON c.id = mm.conversation_id
				AND mm.to_id = ".$user['id']."
				AND mm.is_received = 1
				AND mm.is_read = 0
				WHERE mm.id IS NULL
				ORDER BY id DESC
				LIMIT $sLimit 
				OFFSET $sOffset");

			$resTotal = $this->Conversation->query("SELECT COUNT(DISTINCT c.id) AS num
				FROM messaging_conversations AS c
				INNER JOIN messaging_conversations_users AS mcu
				ON c.id = mcu.conversation_id
				AND mcu.user_id = ".$user['id']."
				LEFT JOIN messaging_messages AS mm
				ON c.id = mm.conversation_id
				AND mm.to_id = ".$user['id']."
				AND mm.is_received = 1
				AND mm.is_read = 0
				WHERE mm.id IS NULL");
		}

		$conversations = [];
		foreach($res as $r) {
			$conversations[ $r['c']['id'] ] = [
				'num_unread' => $this->request->query('inbox') ? $r[0]['num_unread'] : 0
			];
		}
		// leggi tutte le conversazioni con i dettagli specifici
		if(!empty($conversations)) {
			$this->Conversation->recursive = -1;
			$res = $this->Conversation->find('all', [
				'conditions' => ['Conversation.id' => array_keys($conversations)],
				'contain' => [
					'Participant' => ['Persona'],
					'Attivita' => [
						'fields' => ['id', 'persona_id', 'legendastati_id', 'created', 'destination_id'],
						'Destination'
					]
				]
			]);
			foreach($res as $r) {
				$conversations[ $r['Conversation']['id'] ]['conversation'] = $r;
			}
		}
		
		$output = array(
			"sEcho" => isset($this->request->query['sEcho']) ? intval($this->request->query['sEcho']) : 1,
			"iTotalRecords" => $resTotal[0][0]['num'],
			"iTotalDisplayRecords" => $resTotal[0][0]['num'],
			"aaData" => array()
		);
		
		
		$view = new View($this, false);

		foreach($conversations as $row) {
			
			$r = [];
			
			$r["DT_RowId"] = $row['conversation']['Conversation']["id"];
			$r["id"] = $row['conversation']['Conversation']["id"];
			
			$r["content"] = $view->element('BikesquareMessaging.chat_box', array(
				'chat' => $row['conversation'],
				'num_unread' => $row['num_unread'],
				'expanded' => true
			));
				
			$output['aaData'][] = $r;
		}

		
		$this->set('res', $output);
		$this->set('_serialize', 'res');
		
	}
	
	
	/**
	 * consente ad admin di aggiornare titolo, tags e jobs di una conversazione
	 */
	public function update($id) {
		$user = $this->Session->read('Auth.User');
		$is_admin = $this->_loggedUserIsAdmin();
		
		if( $this->request->is('post') || $this->request->is('put') ) {
			
			if( $this->Conversation->saveAll($this->request->data) ) {
				$this->Session->setFlash(__('Conversation successfully updated'), 'flash_ok');
				$this->redirect( array('controller' => 'messages', 'action' => 'chat', $id) );
			}
			else {
				$this->Session->setFlash(__('An error occured, please retry'), 'flash_error');
				$this->redirect( array('action' => 'update', $id) );
			}
			
		}
		
		$conversation = $this->Conversation->find('first', array(
			'conditions' => array(
				'Conversation.id' => $id
			),
			'recursive' => -1,
			'contain' => array(
				'Tag', 'Participant',
				'Attivita' => array(
					'fields' => array('Attivita.id'),
					'Persona' => ['fields' => ['id', 'Cognome', 'Nome', 'DisplayName']]
				)
			)
		));
		if( empty($conversation) ) throw new NotFoundException(__('Conversazione non trovata'));
		
		// genera il nome attivita
		$personaName = 'n.d.';
		$attivita = $conversation['Attivita'];
		if(!empty($attivita['Persona'])) {
			$p = $attivita['Persona'];
			$personaName = trim($p['Cognome'].' '.$p['Nome']);
			if(empty($personaName)) {
				$personaName = trim($p['DisplayName']);
			}
			if(empty($personaName)) {
				$personaName = 'n.d.';
			}
		}
		$conversation['Attivita']['name'] = $conversation['Attivita']['id'] . ' - ' . $personaName;
		
		// per un problema con habtm non posso ottenere via contain le persone associate ai partecipanti, le tiro su "a mano"
		$persona_ids = array();
		foreach($conversation['Participant'] as $p) {
			$persona_ids[] = $p['id']; // su bikesquare id(persona) = id(utente)
		}
		$persone = $this->_getListaPersone($persona_ids);
		for($i=0;$i<sizeof($conversation['Participant']);$i++) {
			if(isset($persone[ $conversation['Participant'][$i]['id'] ])) {
				$persona_name = $persone[ $conversation['Participant'][$i]['id'] ];
				if( !empty($persona_name) ) {
					$conversation['Participant'][$i]['username'] = $persona_name;
				}
				// altrimenti rimane lo username
			}
		}

		$num_unread = $this->Message->getUnreadNum($user['id']);
		$usable_tags = $this->Messagingtag->getUsableTags($user[USER_ROLE_KEY]);
		
		$this->request->data = $conversation;

		$this->set(compact(
			'user',
			'is_admin',
			'conversation',
			'num_unread',
			'usable_tags'
		));
		
	}
	
	/**
	 * 
	 */
	public function close($conversation_id) {
		
		$conversation = $this->Conversation->find('first', array(
			'conditions' => array(
				'id' => $conversation_id
			),
			'recursive' => -1
		));
		if( empty($conversation) ) throw new NotFoundException(__('Conversazione non trovata'));
		if( $conversation['Conversation']['closed'] ) throw new BadRequestException(__('Conversazione già chiusa'));
		
		$conversation['Conversation']['closed'] = date('Y-m-d H:i:s');
		$this->Conversation->save($conversation);
		// se chiudo una conversazione aggiorno lo stato del contratto collegato
		$this->loadModel('Contact');
		$this->Contact->save([
			'id' => $conversation_id,
			'legendastati_id' => 5 // archiviata
		]);
		
		$this->Session->setFlash(__("Conversation successfully closed"), 'default', ['class' => 'alert alert-success']);
		$this->redirect( $this->referer() );
	}
	
	/**
	 * ri-apre una converazione precedentemente chiusa
	 */
	public function open($conversation_id) {
		
		$conversation = $this->Conversation->find('first', array(
			'conditions' => array(
				'id' => $conversation_id
			),
			'recursive' => -1
		));
		if( empty($conversation) ) throw new NotFoundException(__('Conversazione non trovata'));
		if( !$conversation['Conversation']['closed'] ) throw new BadRequestException(__('Conversazione già aperta'));
		
		$conversation['Conversation']['closed'] = null;
		$this->Conversation->save($conversation);
		// se ri-apro una conversazione aggiorno lo stato del contratto collegato
		$this->loadModel('Contact');
		$this->Contact->save([
			'id' => $conversation_id,
			'legendastati_id' => 0 // info
		]);
		
		$this->Session->setFlash(__("Conversation successfully opened"), 'default', ['class' => 'alert alert-success']);
		$this->redirect( $this->referer() );
	}
	
	/**
	 * 
	 */
	public function edit($conversation_id=null) {
		
		$user = $this->Session->read('Auth.User');
		$is_admin = $this->_loggedUserIsAdmin();
		
		if( $this->request->is('post') || $this->request->is('put') ) {
			
			// shorten ...
			$data = $this->request->data;
			
			// ottieni gli utenti che possono essere contattati (mi servono dopo in varie occasioni)
			$utenti_contattabili = $this->_getAllowedRecipients($user);
			//flatten utenti contattabili
			$flattened_utenti_contattabili = [];
			foreach($utenti_contattabili as $group => $utenti_contattabili_in_group) {
				$flattened_utenti_contattabili = array_merge($flattened_utenti_contattabili, array_keys($utenti_contattabili_in_group));
			}
			$utenti_contattabili = $flattened_utenti_contattabili;
			
			if( isset($conversation_id) ) { // risposta ad una conversazione esistente
				// verifica che l'utente possa rispondere nella conversazione
				$conversation = $this->Conversation->find('first', array(
					'conditions' => array(
						'Conversation.id' => $conversation_id,
						'Conversation.closed' => null // IMPORTANTE! in questo modo controllo subito se l'utente può rispondere oppure no anche se la conversazione esiste
					),
					'recursive' => -1,
					'contain' => array(
						'Participant' => array(
							'fields' => array('id', 'username', USER_ROLE_KEY)
						)
					),
					'joins' => array(
						array(
							'table' => 'messaging_conversations_users',
							'alias' => 'cu',
							'type' => 'INNER',
							'conditions' => array(
								'Conversation.id = cu.conversation_id',
								'cu.user_id' => $user['id']
							)
						)
					)
				));
				if(empty($conversation)) {
					throw new UnauthorizedException(__('Conversazione non trovata'));
				}
			}
			else {
				$conversation = null;
			}
			
			// valida il messaggio
			if( empty($conversation ) ) { // nuova conversazione
				
				$data['Conversation']['author_id'] = $user['id'];
				
				$subject = $data['Conversation']['subject']; // mi serve dopo per la notifica mail
				
				// 1. rimuovi tutti i destinatari che l'utente non può contattare (nel caso in cui un bad guy ci provasse ...)
				foreach($data['Participant']['Participant'] as $k => $v) {
					if(empty($v)) continue; // è il caso in cui non è stato selezionato alcun valore
					if( !in_array($v, $utenti_contattabili) ) unset($k);
				}
				$data['Participant']['Participant'] = array_values($data['Participant']['Participant']);
				
				// 2. rimuovi tutti i tag che l'utente non può usare (nel caso in cui un bad guy ci provasse ...)
				$usable_tags = $this->Messagingtag->getUsableTags($user[USER_ROLE_KEY]);				
				$usable_tags = array_keys($usable_tags);				
				foreach($data['Tag']['Tag'] as $k => $v) {
					if(empty($v)) continue; // è il caso in cui non è stato selezionato alcun valore
					if( !in_array($v, $usable_tags) ) unset($k);
				}
				$data['Tag']['Tag'] = array_values($data['Tag']['Tag']);
				
				// 3. se un bad guy prova a forzare una attivita a cui non può accedere resetta il campo
				if( !empty($data['Conversation']['attivita_id']) ) {
					if( !$this->_loggedUserIsAdmin() ) {
						$attivitas = $this->_getContractsAccessibleByCurrUser();
						$attivita_ids = array_keys($attivitas);
						if( !in_array($data['Conversation']['attivita_id'], $attivita_ids) ) {
							$data['Conversation']['attivita_id'] = null;
						}
					}
				}			
			}
			else { // conversazione esistente
				
				$subject = $conversation['Conversation']['subject']; // mi serve dopo per la notifica mail
				
				// conversazione esistente, rimuovi tutti i dati non pertinenti (nel caso in cui un bad guy li forzasse)
				unset($data['Conversation']['subject']);
				unset($data['Participant']);
				unset($data['Tag']);
			}
			
			// 3. valida i campi
			$validationErrors = array();
			$content = trim( strip_tags( $data['Message'][0]['content']) ); // strip_tags per via di ckeditor
			if( empty($conversation) ) { // nuova conversazione
				
				if( empty($data['Conversation']['subject']) ) {
					$validationErrors[] = __('Nessun oggetto inserito');
				}
				else {
					if( strlen($data['Conversation']['subject']) > 100 ) {
						$validationErrors[] = __("L'oggetto può contenere al massimo 100 caratteri");
					}
				}
				$participants = $data['Participant']['Participant'];
				if( empty($participants) || 
					(sizeof($participants) == 1 && isset($participants[0]) && empty($participants[0])) ) { 
					$validationErrors[] = __('Nessun destinatario scelto');
				}
				
			}
			if( empty($content) ) {
				$validationErrors[] = __('Nessun messaggio inserito');
			}
			
			if( empty($data['recipients']) ) {
				$validationErrors[] = __('Nessun destinatario scelto');
			}
			
			if(empty($validationErrors)) {
							
				/*if( empty($conversation) ) { // NUOVA CONVERSAZIONE
					
					// completa i dati del messaggio inviato
					$data['Message'][0]['is_received'] = 0;
					$data['Message'][0]['is_read'] = 1;
					$data['Message'][0]['is_first'] = 1;
					$data['Message'][0]['from_id'] = $user['id']; // aggiungo il dato via php per evitare che un bad guy lo midifichi
					// IMPORTANTE! nei messaggi inviati metto from = to così nelle conversazioni ottengo immediatamente tutti i messaggi che un utente può vedere mediante una sola condizione to
					$data['Message'][0]['to_id'] = $user['id']; 
					
					// crea i messaggi ricevuti
					$i = 1;
					foreach($data['Participant']['Participant'] as $uid) {
						$data['Message'][$i]['is_received'] = 1;
						$data['Message'][$i]['is_read'] = 0;
						$data['Message'][$i]['is_first'] = 1; // a livello di visualizzazione è sempre per il primo perchè è una nuova conversazione
						$data['Message'][$i]['from_id'] = $user['id']; // aggiungo il dato via php per evitare che un bad guy lo midifichi
						$data['Message'][$i]['to_id'] = $uid;
						$data['Message'][$i]['content'] = $data['Message'][0]['content'];
						$i++;
					}
					
					// IMPORTANTE! AGGIUNGO SEMPRE COME PARTECIPANTE CHI STA INVIANDO IL MESSAGGIO 
					$data['Participant']['Participant'][] = $user['id'];
				}
				else { // MESSAGGIO SUCCESSIVO IN UNA CONVERAZIONE GIA' ESISTENTE
					
					$data['Conversation']['id'] = $conversation_id;
					
					// completa i dati del messaggio inviato
					$data['Message'][0]['is_received'] = 0;
					$data['Message'][0]['is_read'] = 1;
					$data['Message'][0]['is_first'] = 0; // E' MESSAGGIO SUCCESSIVO
					$data['Message'][0]['from_id'] = $user['id']; // aggiungo il dato via php per evitare che un bad guy lo midifichi
					// IMPORTANTE! nei messaggi inviati metto from = to così nelle conversazioni ottengo immediatamente tutti i messaggi che un utente può vedere mediante una sola condizione to
					$data['Message'][0]['to_id'] = $user['id']; 
					
					$i = 1;
					foreach($conversation['Participant'] as $p) {
						
						if($p['id'] == $user['id']) continue; // NON MANDO UN MESSAGGIO A CHI LO INVIA !
						
						// IMPORTANTE! RIMUOVI TUTTI I DESTINATARI CHE L'UTENTE NON PUÒ CONTATTARE (NEL CASO DI UN BAD GUY ...)
						//if( !in_array($p['id'], $utenti_contattabili) ) continue;
						
						$data['Message'][$i]['is_received'] = 1;
						$data['Message'][$i]['is_read'] = 0;
						$data['Message'][$i]['is_first'] = 0; // un messaggio ricevuto non può mai essere il primo di una conversazione
						$data['Message'][$i]['from_id'] = $user['id']; // aggiungo il dato via php per evitare che un bad guy lo midifichi
						$data['Message'][$i]['to_id'] = $p['id'];
						$data['Message'][$i]['content'] = $data['Message'][0]['content'];
						$i++;
					}
					
				}
				
				// collega eventuali allegati collegati con questo messaggio ai messaggi generati
				$attachments = $this->Message->Attachment->find('all', array(
					'fields' => array('id'),
					'conditions' => array(
						'link_id' => $data['Conversation']['link_id']
					)
				));
				foreach($data['Message'] as $k => $v) {
					foreach($attachments as $attachment) {
						$attachment_id = $attachment['Attachment']['id'];
						$data['Message'][$k]['Attachment']['Attachment'][$attachment_id] = $attachment_id; 
					}
				}
							
				$success = $this->Conversation->saveAll($data, array('deep' => true));
				
				if($success) {
					// invia notifica email
					for($i=1;$i<sizeof($data['Message']);$i++) {
						$this->_send_notification(
							empty($conversation), 
							empty($conversation) ? $this->Conversation->getLastInsertID() : $conversation_id, 
							$user, 
							$subject, 
							$data['Message'][$i]['content'], 
							$data['Message'][$i]['to_id']);
					}
				}*/
				
				if( empty($conversation) ) { // NUOVA CONVERSAZIONE
					// QUI NON ENTRO PIÙ ...
					/*$this->Conversation->saveAll($data, array('deep' => true));
					$this->MessageFactory
						->forConversation($this->Conversation->getLastInsertId())
						->subject($subject ? $subject : __('Nuovo messaggio da un utente'))
						->tplBody('messaging_message', [
							'message' => $data['Message'][0]['content'],
							'recipient' => $user['id']
						])
						->sendWithEmail(true);
					$success = true;*/
					
				}
				else {
					if(empty($conversation['Conversation']['subject'])) {
						$conversation['Conversation']['subject'] = __('Richiesta informazioni');
					}
					$subject = 'Re:' . $conversation['Conversation']['subject'];
					if($conversation['Conversation']['attivita_id']) {
						$this->loadModel('Contact');
						$contact = $this->Contact->find('first', [
							'conditions' => ['Contact.id' => $conversation['Conversation']['attivita_id']],
							'contain' => ['Destination']
						]);
						if($contact) {
							if($contact['Contact']['legendastati_id'] != 0) {
								$subject = 'Re:Bikesquare '.$contact['Destination']['name'].' - Messaggio per e-bike ' . $contact['Contact']['rental_date'];
							}
							// altrimenti richiesta di informazioni, lascio il subject originale
						}
					}
					
					$this->MessageFactory
						->forDefaultConversation($conversation['Conversation']['attivita_id'])
						->withAttachmentLink($data['Conversation']['link_id'])
						->subject($subject ? $subject : __('Nuovo messaggio da un utente'))
						->tplBody('messaging_message', [
							'message' => $data['Message'][0]['content'],
							'recipient' => $user['id']
						])
						->fromUser($user['id'])
						->toUserIds(array_values($data['recipients']))
						->sendWithEmail(true);
					$success = true;
					
					// importante: ho creato un messaggio (= ho risposto ad una converazione) -> se admin setto i messaggi
					// come letti e aggiorno il "gestito da" del contratto con l'utente corrente
					// (questo perchè per admin i messaggi non vengono aggiornati come letti fino a quando non si risponde
					// al fine di non lasciare richieste pendenti lette ma non gestite/risposte)
					if($user['group_id'] == ROLE_ADMIN) {
						$this->loadModel('BikesquareMessaging.Message');
						$this->Message->markRead($conversation_id, $user['id']);
						$actualLoggedAdminUser = $this->Session->read('ActualLoggedAdminUser');
						$this->loadModel('Contact');
						$this->Contact->save([
							'id' => $conversation_id, // conversation_id = contact_id
							'user_id' => $actualLoggedAdminUser ? $actualLoggedAdminUser['id'] : ''
						]);
					}
				}
				
				$res = array(
					'success' => $success ? true : false,
					'errorMessage' => $success ? '' : __('An error occured')
				);
			}
			else {
				$res = array(
					'success' => false,
					'errorMessage' => implode('<br/>', $validationErrors)
				);
			}
			
			$this->set('res', $res);
			$this->set('_serialize', 'res');
		}
		
	}
	
}
