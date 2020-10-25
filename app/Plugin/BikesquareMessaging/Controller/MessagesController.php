<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

App::uses('AppController', 'Controller');

/**
 * Message
 */
class MessagesController extends BikesquareMessagingAppController {

	public $uses = array('BikesquareMessaging.Message', 'BikesquareMessaging.Messagingtag', 'Contact');
	public $components = ['BikesquareMessaging.SimpleMessage'];

	public function beforeFilter() {
		parent::beforeFilter();
		$this->set('activeSection', 'messaging');
		
		$user = $this->Session->read('Auth.User');
		if(!empty($user)) {
			
			if($user[USER_ROLE_KEY] == 1) $this->layout = 'default';
			else $this->layout = 'mangio';
			
			$this->Auth->allow('index', 'inbox', 'sent', 'conversation', 'attivita', 'chat', 
				'unread',
				'contact'
				);
		}
	}
	
	/**
	 * visualizza tutte le chat di un utente con messaggi non letti
	 */
	/*public function unread() {
		
	}*/
	
	// visualizza tutte le chat di un utente legate ad un certo contratto
	public function contact($contact_id) {
		
		// nuova gestione a singola chat per contatto
		$this->redirect(['action' => 'chat', $contact_id]);
		
		/*$this->loadModel('Contact');
		$this->Contact->recursive = -1;
		$contact = $this->Contact->find('first', [
			'conditions' => ['Contact.id' => $contact_id ],
			'contain' => [
				'Destination',
				'Persona',
				'BiciPrenotata',
				'AddonPrenotato'
			]
		]);
		if(empty($contact)) {
			throw new NotFoundException('Contatto non trovato');
		}
		
		// rimappa gli indici per l'element dettagli prenotazione
		$contact['Attivita'] = $contact['Contact'];
		foreach(['Destination', 'Persona', 'BiciPrenotata', 'AddonPrenotato'] as $field) {
			$contact['Attivita'][$field] = $contact[$field];
		}
		
		$this->loadModel('BikesquareMessaging.Conversation');
		$this->Conversation->ensureDefaultChatsExistForLoggedUser($contact_id); // important!
		
		$this->set('contact', $contact);*/
	}
		
	/**
	 * una chat per ogni contratto
	 */
	public function chat($contact_id) {
		$this->loadModel('BikesquareMessaging.Conversation');
		$user = $this->Session->read('Auth.User');

		$conversation_id = $contact_id;
		
		if( !$this->Conversation->exists($conversation_id) ) {

			// se il contatto esiste crea la conversazione (in certi casi - es. contratti vecchi - la conversazione non esiste)
			$this->loadModel('Contact');
			if($this->Contact->exists($contact_id)) {
				$contact = $this->Contact->findById($contact_id);
				if($contact['Contact']['legendastati_id'] == 0) {
					$subject = 'Messaggio dal Web da '.$contact['Persona']['Nome'].' '.$contact['Persona']['Cognome'];
				}
				else {
					$subject = 'Prenotazione del '.$contact['Contact']['rental_date'].' di '.$contact['Persona']['Nome'].' '.$contact['Persona']['Cognome'];
				}
				
				$conversation = [
						'id' => $contact_id,
						'subject' => $subject,
						'author_id' => -1,
						'attivita_id' => $contact_id
				];
				$this->Conversation->save($conversation);
				$this->Conversation->ensureDefaultParticipants($conversation_id); // important!
			}
			else {
				throw new UnauthorizedException(__('Contatto non trovato'));
			}	
		}
		else {
			// a seconda dello stato corrente del contact associato sistema i partecipanti alla chat
			$this->Conversation->ensureDefaultParticipants($conversation_id); // important!
		}

		// ricarica la conversazione verificando che la conversazione esista e che l'utente possa accedervi
		$conversation = $this->Message->Conversation->find('first', array(
			'conditions' => array('Conversation.id' => $conversation_id),
			'recursive' => -1,
			'contain' => array(
				'Tag', // mi servono tutti i campi,
				'Participant' => array(
					'fields' => array('Participant.id', 'username', USER_ROLE_KEY),
				),
				'Attivita' => array( // per le conversazioni legate ad una attivita (contratto)
					//'fields' => array('Attivita.id', 'rental_date', 'return_date', 'legendastati_id'),
					'Persona' => array(
						//'fields' => array('id', 'Cognome', 'Nome', 'DisplayName', 'EMail', 'Cellulare')
					),
					/*'Destination',
					'BiciPrenotata' => ['Tipobici'],
					'AddonPrenotato' => ['Addon'],
					'Poi'*/
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
		
		if($user['group_id'] != ROLE_ADMIN) {
			$this->Message->markRead($conversation_id, $user['id']);
		}
		// altrimenti setto come letti solo quando si risponde (in modo che non rimangano richieste lette ma non gestite)
		
		$this->set('conversation', $conversation);
	}
	
	/**
	 *
	 */
	/*public function index() {
		
	}*/
	
	/**
	 * 
	 */
	/*public function index_old() {
		$user = $this->Session->read('Auth.User');
		
		//if( $user[USER_ROLE_KEY] == ROLE_USER ) {
			// redireziona alla chat
		//	$this->redirect(['action' => 'chat']);
		//}
		
		$is_admin = $this->_loggedUserIsAdmin();

		$num_unread = $this->Message->getUnreadNum($user['id']);
		$num_unread_in_detail = false; // settato dopo se necessario
		$visible_tags = $this->Messagingtag->getVisibleTags($user[USER_ROLE_KEY]);
		$usable_tags = $this->Messagingtag->getUsableTags($user[USER_ROLE_KEY]);
		$people = $this->_getAllowedRecipients($user);
		$default_people = $this->_getDefaultRecipients($user);

		// gestisci tag e titolo
		$show_sent_messages = false;
		$tag_id = false;
		$attivita_id = false;
		if( !empty($this->request->params['named']['tag']) ) {
		
			$tag_id = $this->request->params['named']['tag'];
			if($tag_id == 'sent') {
				// in questo caso (e solo in questo caso) gestisci anche l'eventuale parametro in get attivita_id (se presente)
				// per visualizzare i messaggi inviati per un certo contratto
				if( $this->request->query('attivita_id') ) {
					$attivita_id = $this->request->query('attivita_id');
					$people = $this->_getAllowedRecipients($user, $attivita_id); // sovrascrivi i possibili recipienti filtrandoli
					$default_people = $this->_getDefaultRecipients($user, $attivita_id); // sovrascrivi i possibili recipienti filtrandoli
				}
				
				$show_sent_messages = true;
				$title = __('Sent');
			}
			else if($tag_id == 'closed') {
				$title = __('Closed');
				$num_unread_in_detail = $this->Message->getUnreadNumClosed($user['id']);
			}
			else {
				if( !isset($visible_tags[$tag_id]) ) {
					// l'utente non può vedere il tag, lo rimando alla home
					$this->redirect( array('controller' => 'messages', 'action' => 'index') );
				}
				$title = $visible_tags[$tag_id]['Messagingtag']['name'];
				$num_unread_in_detail = $this->Message->getUnreadNumByTag($user['id'], $tag_id);
			}
		}
		else {
			$title = __('Inbox');
		}
		
		// ottieni la lista dei contratti (attività) accessibili da parte dell'utente corrente
		$attivitas = $this->_getContractsAccessibleByCurrUser();
		
		// QUESTA ACTION PUÒ ESSERE RICHIAMATA DA ALTRE SEZIONI DEL SITO PER INIZIARE UNA CONVERSAZIONE GENERICA
		$compose_to = array();
		if( isset($this->request->query['compose_to']) ) {
			$rcpt_ids = explode(',', $this->request->query['compose_to']);
			if($is_admin) {
				$compose_to = $rcpt_ids;
			}
			else {
				// rimuovi tutti gli utenti che non può contattare
				$compose_to = array();
				foreach($rcpt_ids as $rcpt_id) {
					$found = false;
					foreach($people as $role => $role_people) {
						if(isset($role_people[$rcpt_id])) {
							$found = true;
							break;
						}
					}
					if($found) $compose_to[] = $rcpt_id;
				}
			}
		}
		
		if( $this->request->is('json') ) { // restituisci la vista rendered come stringa su layout ad hoc per l'embed
			$view = new View($this, false);
			$view->layout = 'embed';
			$view->set(compact(
				'title', 
				'num_unread',
				'num_unread_in_detail', 
				'visible_tags', 
				'usable_tags', 
				'is_admin', 
				'people', 
				'default_people',
				'show_sent_messages',
				'tag_id',
				'job_id',
				'job', // mi serve per il crumb
				'attivitas',
				'compose_to',
				'attivita_id'
			));
			$html = $view->render('index');
			$this->set('html', $html);
		}
		else { // render normale
			$this->set(compact(
				'title', 
				'num_unread',
				'num_unread_in_detail', 
				'visible_tags', 
				'usable_tags', 
				'is_admin', 
				'people', 
				'default_people',
				'show_sent_messages',
				'tag_id',
				'job_id',
				'job', // mi serve per il crumb
				'attivitas',
				'compose_to',
				'attivita_id'
			));
		}
	}*/
	

	/**
	 * 
	 */
	/*public function attivita($attivita_id) {
			
		$user = $this->Session->read('Auth.User');
		$is_admin = $this->_loggedUserIsAdmin();
		
		// gestisci il tag con cui (potenzialmente) è filtrata la comunicazione di attività
		$tag_id = false;
		if( !empty($this->request->params['named']['tag']) ) {
			$tag_id = $this->request->params['named']['tag'];
		}
		
		// verifica che l'attivita sia accessibile dall'utente corrente
		$attivitas = $this->_getContractsAccessibleByCurrUser();
		$accessible_attivita_ids = array_keys($attivitas);
		
		if( !in_array($attivita_id, $accessible_attivita_ids) ) {
			$this->Session->setFlash(__("Contratto non trovato o non disponibile"), "flash_error");
			$this->redirect(array('action' => 'index'));
		}
		
		$attivita = $this->Contact->find('first', array(
			'conditions' => array('Contact.id' => $attivita_id),
			'contain' => array(
				'Persona' => array(
					'fields' => array('id', 'Cognome', 'Nome', 'DisplayName', 'EMail', 'Cellulare')
				),
				'BiciPrenotata' => ['Tipobici'],
				'AddonPrenotato' => ['Addon'],
				'Poi',
				'Destination'
			), 
			'recursive' => -1
		));
		$attivita['Attivita'] = $attivita['Contact'];
		
		unset($attivita['Contact']);
		
		// modifica gli indici (per l'element che visualizza i dettagli contratto, compatibilmente con lo stesso blocco in conversation.ctp)
		foreach(['BiciPrenotata', 'AddonPrenotato', 'Poi'] as $k) {
			$attivita['Attivita'][$k] = $attivita[$k];
			unset($attivita[$k]);
		}
		
		// setta il nome dell'attività
		$personaName = 'n.d.';
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
		$attivita['Attivita']['name'] = $attivita['Attivita']['id'] . ' - ' . $personaName;
		
		$num_unread = $this->Message->getUnreadNum($user['id']);
		$num_unread_in_detail = $this->Message->getUnreadNumByAttivita($user['id'], $attivita_id);
		$visible_tags = $this->Messagingtag->getVisibleTags($user[USER_ROLE_KEY]);
		$usable_tags = $this->Messagingtag->getUsableTags($user[USER_ROLE_KEY]);
		
		$people = $this->_getAllowedRecipients($user, $attivita_id);
		$default_people = $this->_getDefaultRecipients($user, $attivita_id);
			
		// QUESTA ACTION PUÒ ESSERE RICHIAMATA DA ALTRE SEZIONI DEL SITO PER INIZIARE UNA CONVERSAZIONE GENERICA
		$compose_to = array();
		if( isset($this->request->query['compose_to']) ) {
			$rcpt_ids = explode(',', $this->request->query['compose_to']);
			if($is_admin) {
				$compose_to = $rcpt_ids;
			}
			else {
				// rimuovi tutti gli utenti che non può contattare
				$compose_to = array();
				foreach($rcpt_ids as $rcpt_id) {
					$found = false;
					foreach($people as $role => $role_people) {
						if(isset($role_people[$rcpt_id])) {
							$found = true;
							break;
						}
					}
					if($found) $compose_to[] = $rcpt_id;
				}
			}
		}
			
		if( $this->request->is('json') ) { // restituisci la vista rendered come stringa su layout ad hoc per l'embed
			$view = new View($this, false);
			$view->layout = 'embed';
			$view->set(compact(
				'is_admin',
				'attivita',
				'num_unread',
				'num_unread_in_detail',
				'visible_tags',
				'usable_tags',
				'people',
				'default_people',
				'attivitas',
				'tag_id',
				'compose_to'));
			$html = $view->render('attivita');
			$this->set('html', $html);
		}
		else { // render normally
			$this->set(compact(
				'is_admin',
				'attivita',
				'num_unread',
				'num_unread_in_detail',
				'visible_tags',
				'usable_tags',
				'people',
				'default_people',
				'attivitas',
				'tag_id',
				'compose_to'
			));
		}
	}*/
		
	/**
	 * 
	 */
	/*public function conversation($conversation_id) {
		
		$this->redirect(['action' => 'chat', $conversation_id]);
		
		$user = $this->Session->read('Auth.User');
		$is_admin = $this->_loggedUserIsAdmin();
		
		// verifica che la conversazione esista e che l'utente possa accedervi
		$conversation = $this->Message->Conversation->find('first', array(
			'conditions' => array('Conversation.id' => $conversation_id),
			'recursive' => -1,
			'contain' => array(
				'Tag', // mi servono tutti i campi,
				'Participant' => array(
					'fields' => array('Participant.id', 'username', USER_ROLE_KEY),
				),
				'Attivita' => array( // per le conversazioni legate ad una attivita (contratto)
					'fields' => array('Attivita.id', 'rental_date', 'return_date', 'legendastati_id'),
					'Persona' => array(
						'fields' => array('id', 'Cognome', 'Nome', 'DisplayName', 'EMail', 'Cellulare')
					),
					'Destination',
					'BiciPrenotata' => ['Tipobici'],
					'AddonPrenotato' => ['Addon'],
					'Poi'
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
		
		if( $this->request->query('origin') == 'notification' && !empty($user) && in_array($user[USER_ROLE_KEY], [ROLE_ADMIN, ROLE_BAM]) ) {
			// cliccato su notifica, redireziono in automatico al messaging del contratto
			if(!empty($conversation['Attivita'])) {
				$this->redirect([
					'plugin' => null,
					'controller' => 'contacts', 
					'action' => 'contratto',
					$conversation['Attivita']['id'],
					'?' => [
						'messaging_conversation' => $conversation_id
					]
				]);
			}
		}
		
		// se è una conversazione legata a un contratto impostane il nome
		if(!empty($conversation['Attivita'])) {
			$nome = $conversation['Attivita']['id'];
			if(!empty($conversation['Attivita']['Persona'])) {
				$p = $conversation['Attivita']['Persona'];
				$nomePersona = trim($p['Cognome'].' '.$p['Nome']);
				if(empty($nomePersona)) {
					$nomePersona = trim($p['DisplayName']);
				}
				if(!empty($nomePersona)) {
					$nome .= ' - '.$nomePersona;
				}
			}
			$conversation['Attivita']['name'] = $nome;
		}
		
		// se è una conversazione di progetto carica il numero di messaggi non letti per l'attività
		if(!empty($conversation['Attivita'])) {
			$num_unread_in_detail = $this->Message->getUnreadNumByAttivita($user['id'], $conversation['Attivita']['id']);
			$this->set(compact('num_unread_in_detail'));
		}
		
		// 160922: per i partecipanti devo visualizzare possibilmente il nome persona
		// ma non posso usare containable con Participant -> User perchè con HABTM non funziona.
		// Eseguo una extra query
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
		
		// aggiorna lo stato di tutti i messaggi della conversazione a "letto"
		$this->Message->markRead($conversation_id, $user['id']);
		$this->Message->updateAll(array('is_read' => 1), array('conversation_id' => $conversation_id, 'to_id' => $user['id']));
		
		$num_unread = $this->Message->getUnreadNum($user['id']);
		$visible_tags = $this->Messagingtag->getVisibleTags($user[USER_ROLE_KEY]);
		$usable_tags = $this->Messagingtag->getUsableTags($user[USER_ROLE_KEY]);
		$people = $this->_getAllowedRecipients($user, isset($conversation['Attivita']) ? $conversation['Attivita']['id'] : null);
		$default_people = $this->_getDefaultRecipients($user, isset($conversation['Attivita']) ? $conversation['Attivita']['id'] : null);
		
		// ottieni la lista delle attività accessibili da parte dell'utente corrente
		$attivitas = $this->_getContractsAccessibleByCurrUser();
		
		// importante! Se l'utente non può accedere al contratto associato alla conversazione (è destinatario della conversazione
		// ma non può accedere ai dettagli della prenotazione) rimuovilo
		if( !empty($conversation['Conversation']['attivita_id']) ) {
			if( !in_array($conversation['Conversation']['attivita_id'], array_keys($attivitas)) ) {
				$conversation['Conversation']['attivita_id'] = null;
				unset($conversation['Attivita']);
			}
		}
		
		if( $this->request->is('json') ) { // restituisci la vista rendered come stringa su layout ad hoc per l'embed
			$view = new View($this, false);
			$view->layout = 'embed';
			$view->set(compact(
				'conversation', 
				'num_unread', 
				'visible_tags', 
				'usable_tags', 
				'is_admin', 
				'people',
				'default_people',
				'user',
				'attivitas'
			));
			$html = $view->render('conversation');
			$this->set('html', $html);
		}
		else { // render normale
			$this->set(compact(
				'conversation', 
				'num_unread', 
				'visible_tags', 
				'usable_tags', 
				'is_admin', 
				'people',
				'default_people',
				'user',
				'attivitas'
			));
		}
		
	}*/
	
	/*public function chat_old($conversation_id=null)
	{
		$this->layout = "prenota";
		
		$user = $this->Session->read('Auth.User');
		$uid = $user['id'];
		
		// - se sono loggato come utente provo sempre a creare una conversazione di default
		// per ogni prenotazione che ne è priva in modo da poter comunicare
		if( !in_array($user[USER_ROLE_KEY], [ROLE_ADMIN, ROLE_BAM]) ) { //
			$this->loadModel('Contact');
			$prenotazioni = $this->Contact->find('all', [
				'recursive' => -1,
				'conditions' => ['Contact.persona_id' => $uid, 'cc.id' => null],
				'joins' => [
					array(
						'table' => 'messaging_conversations',
						'alias' => 'cc',
						'type' => 'LEFT',
						'conditions' => array(
							'cc.attivita_id = Contact.id',
							'cc.author_id' => $uid,
						)
					)
				],
				'contain' => 'Persona'
			]);

			$conversations = [];
			foreach($prenotazioni as $p) {
				$rcpts = array_keys($this->_getDefaultRecipients($user, $p['Contact']['id']));
				$rcpts[] = $uid;
				
				if($p['Contact']['legendastati_id'] == 0) {
					$subject = 'Richiesta informazioni di ' . $p['Contact']['Nome']. ' ' . $p['Contact']['Cognome']. ' del '.$p['Contact']['created'];
				}
				else {
					$subject = 'Prenotazione di ' . $p['Contact']['Nome']. ' ' . $p['Contact']['Cognome']. ' del '.$p['Contact']['created'];
				}
				
				$conversations[] = [
					'Conversation' => [
						'subject' => $subject,
						'author_id' => $uid,
						'attivita_id' => $p['Contact']['id']
					],
					'Participant' => ['Participant' => $rcpts]
				];
			}
			$this->loadModel('BikesquareMessaging.Conversation');
			$this->Conversation->saveAll($conversations);
		}

		if (!empty($this->request->data))
		{
			//Assumo che ci sia una conversazione, se non c'è ti sbatto fuori
			if(empty($conversation_id))
			{
				throw new NotFoundException('Conversazione inesistente');
			}
			$this->Message->Conversation->recursive = -1;
			$conversation = $this->Message->Conversation->findById($conversation_id)['Conversation'];

			$to = $this->_getDefaultRecipients($user, $conversation['attivita_id']);						
			$to_ids = array_keys($to);

			$data = $this->request->data;
			$data['subject'] = $conversation['subject'];

			//Fa la chiamata al plugin che invia i messaggi davvero
			if ($this->SimpleMessage->inviaMessaggio($data, $to_ids, $user, $conversation['attivita_id'], $conversation_id))
			{
					$this->Session->setFlash(__('Messaggio inviato correttamente'));					
			}
			else
			{
					$this->Session->setFlash(__('Si è verificato un errore. Si prega di ripetere la richiesta'), 'default', ['class' => 'alert alert-danger']);					
			}
			
			$this->redirect(['action'=>'chat', $conversation_id]);    
		}
		else
		{
			if(empty($conversation_id))
			{
				$conversation = $this->Message->getLatestConversation($uid);
				$conversation_id = $conversation['id'];
			}
			else
			{
				$this->Message->Conversation->recursive = -1;
				$conversation = $this->Message->Conversation->findById($conversation_id)['Conversation'];
			}
			
			// aggiorna lo stato di tutti i messaggi della conversazione a "letto"
			$this->Message->markRead($conversation_id, $user['id']);

			//Prendo i messaggi della conversazione corrente
			$messages = $this->Message->find('all',[
				'conditions'=>['conversation_id'=>$conversation_id, 'to_id'=>$uid],
				'contain' => ['From'],
				'order' => 'Message.modified ASC'
			]);

			//Prendo l'elenco delle mie conversazioni
			$conversations = $this->Message->Conversation->getConversationsOfUser($uid);		
			
			$this->set('messages',$messages);
			$this->set('conversation',$conversation);
			$this->set('conversations',$conversations);

			$this->Message->markRead($conversation_id, $uid);
		}
	}*/
	
	/**
	 * 
	 */
	public function inbox() {
		
		$user = $this->Session->read('Auth.User');
		
		$this->RequestHandler->setContent('json', 'application/json');

		$conversation_id = null;
		if( !empty($this->request->params['named']['conversation']) ) {
			$conversation_id = $this->request->params['named']['conversation'];
		}

		/* Array of database columns which should be read and sent back to DataTables. Use a space where
	  	 * you want to insert a non-database field (for example a counter or static image)
	 	 */
	 	 
	 	if( empty($conversation_id) ) {
			$aColumns = array(
				'From.username', //'CONCAT(From.last_name, " ", From.first_name)',
				'Conversation.subject'
			);
		}
		else {
			$aColumns = array(
				'From.username', //'CONCAT(From.last_name, " ", From.first_name)',
				'Conversation.subject',
				'Message.content'
			);
		}
		
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

		/*
	 	 * Ordering
		 */
		if($conversation_id) {
			// sono all'interno di una conversazione, l'ordine è fisso per data decrescente
			$sOrder = array(
				'Message.created DESC',
				'Message.id DESC' // necessario nel caso di più messaggi automatici con la stessa data
			);
		}
		else {
			// elenco messaggi
			$sortingCols = array(
				'From.username',
				'Conversation.subject',
				"", // serve unicamente per fare il match con le colonne del datatables
				'Message.created'
			);
			$sOrder = array();
			if ( isset( $this->request->query['iSortCol_0'] ) )
			{
				for ( $i=0 ; $i<intval( $this->request->query['iSortingCols'] ) ; $i++ )
				{
					if ( $this->request->query[ 'bSortable_'.intval($this->request->query['iSortCol_'.$i]) ] == "true" )
					{
						$sOrder[] = $sortingCols[ intval( $this->request->query['iSortCol_'.$i] ) ].' '.($this->request->query['sSortDir_'.$i]==='asc' ? 'ASC' : 'DESC');
					}
				}
			}
			// aggiungo sempre come extra condizione di ordering l'id desc nel caso di messaggi multipli generati automaticamente
			// o messaggi con le stesse caratteristiche (es. stesso oggetto e/o stesso mittente)
			$sOrder[] = 'Message.id DESC';
		}
	
		/* 
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		$sWhere['OR'] = array();
		if ( isset($this->request->query['sSearch']) && $this->request->query['sSearch'] != "" )
		{
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere['OR'][$aColumns[$i].' LIKE'] = "%".( $this->request->query['sSearch'] )."%";
			}
		}
	
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($this->request->query['bSearchable_'.$i]) && $this->request->query['bSearchable_'.$i] == "true" && $this->request->query['sSearch_'.$i] != '' )
			{
				$sWhere[$aColumns[$i].' LIKE'] = "%".($this->request->query['sSearch_'.$i])."%";
			}
		}
		
		$options = array();
		
		if(!empty($sLimit)) $options['limit'] = $sLimit;
		if(!empty($sOffset)) $options['offset'] = $sOffset;

		if(!empty($sWhere)) $options['conditions'] = $sWhere;
		if(!empty($sOrder)) $options['order'] = $sOrder;
		
		$extraConditions = array();
		// un utente deve vedere solo i messaggi che gli appartengono ( NOTA: è la logica giusta sia in una conversazione sia al di fuori)
		$extraConditions['Message.to_id'] = $user['id']; 
		if(!empty($conversation_id)) {
			$extraConditions['Message.conversation_id'] = $conversation_id;
		}
		else {
			$extraConditions['Message.is_received'] = 1; // nelle liste visualizzo solo i messaggi ricevuti
		}
		
		if(empty($conversation_id)) {
			// se non sono dentro una conversazione non leggo il contenuto dei messaggi x tirare su meno dati
			$options['fields'] = array(
				'Message.id', 
				'Message.from_id', 
				'Message.to_id', 
				'Message.conversation_id',
				'Message.created',
				'Message.modified',
				'Message.is_read',
				'Message.is_first',
				'Message.is_received',
			);
		}

		$options['recursive'] = -1;
		$options['contain'] = array(
			'Conversation' => array(
				'fields' => array('id', 'subject', 'closed'),
				'Tag', // mi servono tutti i campi per la visualizzazione
				'Attivita' => array(
					'fields' => array('id'),
					'Persona' => [
						'fields' => ['id', 'COGNOME', 'NOME'/*, 'DisplayName'*/]
					]
				)
			),
			'Attachment' => array(
				'fields' => array('id', 'name', 'path') // mi serve il path x l'icona
			),
			'From' => array(
				'fields' => array('id', 'username', USER_ROLE_KEY),
				'Persona' => array(
					'fields' => array('id', /*'DisplayName',*/ 'COGNOME', 'NOME')
				)
			)
		);
		
		$options['joins'] = array();
		// gestisco l'eventuale tag richiesto in visualizzazione
		// NOTA: non mi devo preoccupare se il tag è visibile all'utente oppure no perchè in ogni caso restituisco
		// solo i messaggi che gli appartengono
		if( !empty($this->request->params['named']['tag']) ) {
			$tag_id = $this->request->params['named']['tag'];
			if($tag_id == 'closed') { // tag "speciale"
				$options['joins'][] = array(
					'table' => 'messaging_conversations',
					'alias' => 'cc',
					'type' => 'INNER',
					'conditions' => array(
						'Message.conversation_id = cc.id',
						'cc.closed <>' => null,
					)
				);
			}
			else { // tag standard
				$options['joins'][] = array(
					'table' => 'messaging_conversations_tags',
					'alias' => 'ct',
					'type' => 'INNER',
					'conditions' => array(
						'Message.conversation_id = ct.conversation_id',
						'ct.tag_id' => $tag_id,
					)
				);
			}
		}
		
		// gestisco l'eventuale attivita richiesta in visualizzazione
		// NOTA: non mi devo preoccupare se l'attivita è visibile all'utente oppure no perchè in ogni caso restituisco
		// solo i messaggi che gli appartengono
		$attivita_id = null; // riferimento esterno, mi serve dopo 
		if( !empty($this->request->params['named']['attivita_id']) ) {
			$attivita_id = $this->request->params['named']['attivita_id'];
			$options['joins'][] = array(
				'table' => 'messaging_conversations',
				'alias' => 'ca',
				'type' => 'INNER',
				'conditions' => array(
					'Message.conversation_id = ca.id',
					'ca.attivita_id' => $attivita_id,
				)
			);
		}
		
		$options['conditions'] = array_merge($options['conditions'], $extraConditions);
		
		// read the result
		$res = $this->Message->find('all', $options);	
		
		unset($options['limit']);
		unset($options['offset']);
		$iFilteredTotal = $this->Message->find('count', $options);

		/*
		 * Output
		 */
		$totWhere = array();

		$output = array(
			"sEcho" => isset($this->request->query['sEcho']) ? intval($this->request->query['sEcho']) : 1,
			"iTotalRecords" => $this->Message->find('count', array(
				'conditions' => $extraConditions,
				'joins' => $options['joins'] // mi servono anche i joins
			)),
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);

		$view = new View($this, false);

		foreach($res as $row) {
			
			$r["DT_RowId"] = $row['Message']["id"];
			$r["id"] = $row['Message']["id"];
			
			$row['From']['username'] = $this->_getUserDisplayName($row['From']);
			
			// componi il nome attività
			if(!empty($row['Conversation']['Attivita'])) {
				$nome = $row['Conversation']['Attivita']['id'];
				if(!empty($row['Conversation']['Attivita']['Persona'])) {
					$p = $row['Conversation']['Attivita']['Persona'];
					$nomePersona = trim($p['Cognome'].' '.$p['Nome']);
					if(empty($nomePersona)) {
						$nomePersona = trim($p['DisplayName']);
					}
					if(!empty($nomePersona)) {
						$nome .= ' - '.$nomePersona;
					}
				}
				$row['Conversation']['Attivita']['name'] = $nome;
			}
			
			// vengono passati in ogni caso i dati per le colonne che sono ricercabili
			$r["created"] = $row['Message']["created"];
			
			if(empty($conversation_id)) { // lista messaggi generale
				
				$r["from"] = $view->element('messaging/message_col_from', array(
					'message' => $row,
					'attivita_id' => $attivita_id // mi serve per determinare se cliccando su di un tag devo andare nel messaging generale o in quello di progetto
				));
				$r["subject"] = $view->element('messaging/message_col_subject', array(
					'message' => $row,
					'attivita_id' => $attivita_id // mi serve per determinare se cliccando su di un tag devo andare nel messaging generale o in quello di progetto
				));
				$r["attachment"] = $view->element('messaging/message_col_attachment', array(
					'message' => $row,
					'attivita_id' => $attivita_id // mi serve per determinare se cliccando su di un tag devo andare nel messaging generale o in quello di progetto
				));
				$r["date"] = $view->element('messaging/message_col_date', array(
					'message' => $row,
					'attivita_id' => $attivita_id // mi serve per determinare se cliccando su di un tag devo andare nel messaging generale o in quello di progetto
				));
			}
			else { // lista messaggi in conversazione
				$r["content"] = $view->element('messaging/message_in_conversation', array(
					'message' => $row
				));
			}
				
			$output['aaData'][] = $r;
		}

		
		$this->set('res', $output);
		$this->set('_serialize', 'res');
		
	}
	
	/**
	 * 
	 */
	/*public function sent() {
		
		$user = $this->Session->read('Auth.User');
		
		$this->RequestHandler->setContent('json', 'application/json');

		//Array of database columns which should be read and sent back to DataTables. Use a space where
	  	//you want to insert a non-database field (for example a counter or static image)
	 	 
	 	$aColumns = array(
			'Conversation.subject'
		);

		// Paging
		$sLimit = "";
		$sOffset = "";
		if ( isset( $this->request->query['iDisplayStart'] ) && $this->request->query['iDisplayLength'] != '-1' )
		{
			$sLimit = $this->request->query['iDisplayLength'];
			$sOffset = $this->request->query['iDisplayStart'];
		}

		// Ordering
		$sortingCols = array(
			'', // serve unicamente per fare il match con le colonne del datatables
			'Conversation.subject',
			"", // serve unicamente per fare il match con le colonne del datatables
			'Message.created'
		);
		$sOrder = array();
		if ( isset( $this->request->query['iSortCol_0'] ) )
		{
			for ( $i=0 ; $i<intval( $this->request->query['iSortingCols'] ) ; $i++ )
			{
				if ( $this->request->query[ 'bSortable_'.intval($this->request->query['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder[] = $sortingCols[ intval( $this->request->query['iSortCol_'.$i] ) ].' '.($this->request->query['sSortDir_'.$i]==='asc' ? 'ASC' : 'DESC');
				}
			}
		}
		// aggiungo sempre come extra condizione di ordering l'id desc nel caso di messaggi multipli generati automaticamente
		// o messaggi con le stesse caratteristiche (es. stesso oggetto e/o stesso mittente)
		$sOrder[] = 'Message.id DESC';
	
		// Filtering
		// NOTE this does not match the built-in DataTables filtering which does it
		// word by word on any field. It's possible to do here, but concerned about efficiency
		// on very large tables, and MySQL's regex functionality is very limited
		$sWhere['OR'] = array();
		if ( isset($this->request->query['sSearch']) && $this->request->query['sSearch'] != "" )
		{
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere['OR'][$aColumns[$i].' LIKE'] = "%".( $this->request->query['sSearch'] )."%";
			}
		}
	
		// Individual column filtering
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($this->request->query['bSearchable_'.$i]) && $this->request->query['bSearchable_'.$i] == "true" && $this->request->query['sSearch_'.$i] != '' )
			{
				$sWhere[$aColumns[$i].' LIKE'] = "%".($this->request->query['sSearch_'.$i])."%";
			}
		}
		
		$options = array();
		
		if(!empty($sLimit)) $options['limit'] = $sLimit;
		if(!empty($sOffset)) $options['offset'] = $sOffset;

		if(!empty($sWhere)) $options['conditions'] = $sWhere;
		if(!empty($sOrder)) $options['order'] = $sOrder;
		
		
		// gestisci i parametri in get
		if( isset($this->request->query['conversation_id']) ) {
			$conversation_id = $this->request->query['conversation_id'];
		}
		else {
			$conversation_id = NULL;
		}
		
		$extraConditions = array();
		$extraConditions['Message.from_id'] = $user['id']; // un utente deve vedere solo i messaggi che gli appartengono
		$extraConditions['Message.is_received'] = 0; // solo i messaggi inviati
		
		if($conversation_id) {
			$extraConditions['Message.conversation_id'] = $conversation_id; // (separate perchè da applicare anche a count)
		}
		
		$options['joins'] = array();
		$attivita_id = $this->request->query('attivita_id');
		//if( !empty($attivita_id) && $attivita_id != null ) {
		//	$options['joins'][] = array(
		//		'table' => 'messaging_conversations',
		//		'alias' => 'mc',
		//		'type' => 'INNER',
		//		'conditions' => array(
		//			'Message.conversation_id = mc.id',
		//			'mc.attivita_id' => $attivita_id
		//		)
		//	);
		//}
		
		$options['recursive'] = -1;
		$options['contain'] = array(
			'Conversation' => array(
				'fields' => array('id', 'subject', 'closed'),
				'Tag', // mi servono tutti i campi per la visualizzazione
				'Participant' => array(
					'fields' => array('id', 'username', USER_ROLE_KEY),
					'conditions' => array('user_id <>' => $user['id']),
				),
				'Attivita' => array(
					'fields' => array('id'),
					'Persona' => [
						'fields' => ['id', 'Cognome', 'Nome', 'DisplayName']
					]
				)
			),
			'Attachment' => array(
				'fields' => array('id')
			),
		);
		
		$options['conditions'] = array_merge($options['conditions'], $extraConditions);
		
		// read the result
		$res = $this->Message->find('all', $options);	
		unset($options['limit']);
		unset($options['offset']);
		$iFilteredTotal = $this->Message->find('count', $options);

		// Output
		$totWhere = array();

		$output = array(
			"sEcho" => isset($this->request->query['sEcho']) ? intval($this->request->query['sEcho']) : 1,
			"iTotalRecords" => $this->Message->find('count', array(
				'conditions' => $extraConditions,
				//'joins' => $options['joins'] // mi servono anche i joins
			)),
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);

		$view = new View($this, false);

		// 160922: devo visualizzare il nome della persona ma cake sembra avere dei problemi ad usare il containable con HABTM (Participant -> Persona)
		// Per risolvere uso una query ulteriore per leggere le persone che mi servono
		$persona_ids = array();
		foreach($res as $row) {
			foreach($row['Conversation']['Participant'] as $p) {
				if(!empty($p['id'])) $persona_ids[] = $p['id']; // su bikesquare id(persona) = id(utente)
			}
		}
		$persone = $this->_getListaPersone($persona_ids);

		foreach($res as $row) {
			
			$r["DT_RowId"] = $row['Message']["id"];
			$r["id"] = $row['Message']["id"];
			
			// componi il nome attività
			if(!empty($row['Conversation']['Attivita'])) {
				$nome = $row['Conversation']['Attivita']['id'];
				if(!empty($row['Conversation']['Attivita']['Persona'])) {
					$p = $row['Conversation']['Attivita']['Persona'];
					$nomePersona = trim($p['Cognome'].' '.$p['Nome']);
					if(empty($nomePersona)) {
						$nomePersona = trim($p['DisplayName']);
					}
					if(!empty($nomePersona)) {
						$nome .= ' - '.$nomePersona;
					}
				}
				$row['Conversation']['Attivita']['name'] = $nome;
			}
			
			// per tutti i destinatari aggiungo il nome persona
			for($i=0;$i<sizeof($row['Conversation']['Participant']);$i++) {
				if( isset($persone[ $row['Conversation']['Participant'][$i]['id'] ]) ) {
					$persona_name = $persone[ $row['Conversation']['Participant'][$i]['id'] ];
					if( !empty($persona_name) ) {
						$row['Conversation']['Participant'][$i]['username'] = $persona_name;
					}
					// altrimenti rimane lo username
				}
			}
			
			// vengono passati in ogni caso i dati per le colonne che sono ricercabili
			$r["created"] = $row['Message']["created"];
			
			$r["to"] = $view->element('messaging/message_col_to', array(
				'message' => $row,
				'attivita_id' => null 
			));
			$r["subject"] = $view->element('messaging/message_col_subject', array(
				'message' => $row,
				'attivita_id' => null // necessario perchè il template è lo stesso dei messaggi ricevuti
			));
			$r["attachment"] = $view->element('messaging/message_col_attachment', array(
				'message' => $row,
				'attivita_id' => null // necessario perchè il template è lo stesso dei messaggi ricevuti
			));
			$r["date"] = $view->element('messaging/message_col_date', array(
				'message' => $row,
				'attivita_id' => null // necessario perchè il template è lo stesso dei messaggi ricevuti
			));
				
			$output['aaData'][] = $r;
		}

		
		$this->set('res', $output);
		$this->set('_serialize', 'res');
		
	}*/
	
}
