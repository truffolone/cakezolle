<?php

	App::uses('CakeEmail', 'Network/Email');
	App::uses('UserHelper', 'View/Helper');

	class ContactMessage {
		
		protected $contact_id; // usato anche dalle sottoclassi
		protected $subject;
		protected $from; // sovrascritto da ConversationMessage
		protected $to; // sovrascritto da ConversationMessage
		
		private $template;
		private $args;
		 
		private $attachmentLink; 
		 
		public function __construct(int $aContact_id) {
			$this->contact_id = $aContact_id;
			$this->subject = __('BikeSquare');
			$this->rawBody = null;
			$this->template = null;
			$this->args = [];
			$this->from = null;
			$this->to = [];
			$this->attachmentLink = null;
		} 
		
		/**
		 * 
		 */
		public function withAttachmentLink($aLink) {
			$this->attachmentLink = $aLink;
			return $this;
		}
		
		/**
		 * 
		 */
		public function subject($subject): ContactMessage {
			$this->subject = $subject;
			return $this;
		}
		
		/**
		 * 
		 */
		public function rawBody($rawBody): ContactMessage {
			$this->template = 'default';
			$this->args = [
				'content' => $rawBody
			];
			return $this;
		}
		
		/**
		 * 
		 */
		public function tplBody(string $tpl, array $args): ContactMessage {
			$this->template = $tpl;
			$this->args = $args;
			return $this;
		}
		
		/**
		 * 
		 */
		public function fromZolle(): ContactMessage {
			$this->from = $this->getDefaultFrom();
			return $this;
		}
		
		/**
		 * il sistema invia messaggio facendo finta di essere un determinato utente
		 */
		public function fromUser(int $uid): ContactMessage {
			// leggi tutti gli utenti coinvolti
			$UserModel = ClassRegistry::init('User');
			$this->from = $UserModel->findById($uid);
			if(empty($this->from)) { // componi utente "generico"
				$this->from = $this->getDefaultFrom();
			}
			return $this;
		} 
	
		/**
		 * 
		 */
		public function fromRenter(): ContactMessage {
			$UserModel = ClassRegistry::init('User'); 
			$this->from = $UserModel->getAllRenter($this->contact_id)[0];
			return $this;
		}
		
		/**
		 * 
		 */
		public function fromBam(): ContactMessage {
			$UserModel = ClassRegistry::init('User'); 
			$this->from = $UserModel->getAllBam($this->contact_id)[0];
			return $this;
		}
		
		/**
		 * 
		 */
		public function fromAdmin(): ContactMessage {
			$UserModel = ClassRegistry::init('User'); 
			$this->from = $UserModel->getAllAdmin()[0];
			return $this;
		}
		
		/**
		 * 
		 */
		private function getDefaultFrom(): array {
			// è il mittente automatico, è un vero e proprio bot (questo perchè l'utente -1 esiste davvero e deve ricevere anch'esso dei messaggi che non possono arrivare da se stesso)
			return ['User' => [
				'id' => -2,
				'username' => 'Bikesquare'
			]];
		}
		
		/**
		 * 
		 */
		private function getDefaultTo(): array {
			$UserModel = ClassRegistry::init('User'); 
			return $UserModel->findById(-1);
		}
		
		/**
		 * 
		 */
		private function addTo($to) {
			if($to == null) return; // se l'utente è vuoto o non esiste
			if( isset($to[0]) ) { // multiple to
				$this->to = array_merge($this->to, $to);
			}
			else { // single to
				$this->to[] = $to;
			}
			$this->_removeToDuplicates();
		}
		
		/**
		 * 
		 */
		private function _removeToDuplicates() {
			$to = [];
			foreach($this->to as $t) {
				$to[ $t['User']['id'] ] = $t;
			}
			$this->to = array_values($to);
		}
		
		/**
		 * 
		 */
		public function toAdmin(): ContactMessage {
			$UserModel = ClassRegistry::init('User');
			$this->addTo( $UserModel->getAllAdmin() );
			return $this;
		}
		
		/**
		 * 
		 */
		public function toBam(): ContactMessage {
			$UserModel = ClassRegistry::init('User');
			$this->addTo( $UserModel->getAllBam($this->contact_id) );
			return $this;
		}
		
		/**
		 * 
		 */
		public function toCustomer(): ContactMessage {
			$UserModel = ClassRegistry::init('User');
			$this->addTo( $UserModel->getCustomer($this->contact_id) );
			return $this;
		}
		
		/**
		 * 
		 */
		public function toZolle(): ContactMessage {
			$this->addTo( $this->getDefaultTo() );
			return $this;
		}/**
		 * 
		 */
		public function toBikesquare(): ContactMessage {
			$this->addTo( $this->getDefaultTo() );
			return $this;
		}
		
		/**
		 * 
		 */
		public function toRenter(): ContactMessage {
			$UserModel = ClassRegistry::init('User');
			$this->addTo( $UserModel->getAllRenter($this->contact_id) );
			return $this;
		}
		
		/**
		 * 
		 */
		public function toUserId(int $uid): ContactMessage {
			$UserModel = ClassRegistry::init('User');
			$this->addTo( $UserModel->findById($uid) );
			return $this;
		}
		
		/**
		 * 
		 */
		public function toUserIds(array $uids): ContactMessage {
			$UserModel = ClassRegistry::init('User');
			foreach($uids as $uid) {
				$this->addTo( $UserModel->findById($uid) );
			}
			return $this;
		}
		
		/**
		 * 
		 */
		public function toUsername(string $username): ContactMessage {
			$UserModel = ClassRegistry::init('User');
			$this->addTo( $UserModel->findByUsername($username) );
			return $this;
		}
		
		/**
		 * 
		 */
		public function sendWithEmail($sendEmail=true) {
			if(empty($this->from)) $this->from = $this->getDefaultFrom();
			if(empty($this->to)) throw new Exception("Nessun destinatario specificato!");

			$RecapitoModel = ClassRegistry::init('Recapito');

			// leggi gli eventuali allegati al messaggio
			$attachments = [];
			if( !empty($this->attachmentLink) ) {
				$AttachmentModel = ClassRegistry::init('BikesquareMessaging.Attachment');
				$attachments = $AttachmentModel->find('all', array(
					'fields' => array('id', 'name'),
					'conditions' => array(
						'link_id' => $this->attachmentLink
					)
				));
			}
			$this->args['attachments'] = $attachments;

			// render del messaggio come vista per generare il messaggio nel messaging
			$view = new View(null, false);
			$view->layout = false;
			$view->set($this->args);
			$renderedMessage = $view->render('Emails/html/'.$this->template);
						
			// per ogni destinatario: 
			// - individua la conversazione in cui aggiungere il messaggio
			// - genera messaggio + corrispondente notifica mail
			$MessageModel = ClassRegistry::init('BikesquareMessaging.Message');
			$UserModel = ClassRegistry::init('User');
			$internalMessages = [];
			$cakeEmails = [];
			
			$from_id = $this->from['User']['id'];
			
			$recipient_ids = [];
			foreach($this->to as $recipient) {
				$recipient_ids[] = $recipient['User']['id'];
			}
			$conversation_id = $this->getTargetConversationId($recipient_ids);
			
			// completa i dati del messaggio inviato
				$internalMessages[] = [
					'conversation_id' => $conversation_id,
					'is_received' => 0,
					'is_read' => 1,
					'is_first' => 0, // per semplicità considero sempre tutti i messaggi come successivi
					'from_id' => $from_id,
					// IMPORTANTE! nei messaggi inviati metto from = to così nelle conversazioni ottengo immediatamente tutti i messaggi che un utente può vedere mediante una sola condizione to
					'to_id' => $from_id,
					'content' => $renderedMessage,
					'all_rcpts' => implode(',', $recipient_ids)
				];
			
			foreach($this->to as $recipient) {
				$recipient_id = $recipient['User']['id'];
				
				$currRcptRenderedMessage = $renderedMessage;
				
				$renderedAttachments = '';
				if( !empty($attachments) ) {
					// aggiungi gli allegati
					$renderedAttachments = 
					'<br>
					<div style="background:#eee;padding:10px;margin-top:10px; margin-bottom:10px;font-size:90%">
						<b>Allegati</b><br/>';
					foreach($attachments as $a) {
						$renderedAttachments = $renderedAttachments.'<div><a href="'.
							Router::url(['plugin' => 'messaging', 'controller' => 'attachments', 'action' => 'download', $a['Attachment']['id']], true)
							.'">📎 '.$a['Attachment']['name'].'</a></div>';
					}
					$renderedAttachments = $renderedAttachments.'</div>';
				}
				
				$currRcptRenderedMessage = $this->removeEmailsIfNotAdmin($currRcptRenderedMessage, $recipient['User']);
				$currRcptRenderedMessage = $this->removePhoneNumbersIfNotAdmin($currRcptRenderedMessage, $recipient['User']);
				
				// ottieni l'effettivo messaggio per il destinatario corrente aggiungendo l'autenticazione per tutti i link
				$currRcptRenderedMessage = $this->expandUrlsWithAuth($currRcptRenderedMessage, $recipient['User']);
				 
				// completa i dati del messaggio ricevuto	
				$internalMessages[] = [
					'conversation_id' => $conversation_id,
					'is_received' => 1,
					'is_read' => 0,
					'is_first' => 0, // un messaggio ricevuto non può mai essere il primo di una conversazione
					'from_id' => $from_id,
					'to_id' => $recipient_id,
					'content' => $currRcptRenderedMessage,
					'all_rcpts' => implode(',', $recipient_ids)
				];
				
				if($sendEmail) {
					// genera l'email corrispondente al messaggio inviato
					$currRcptRenderedMessage = $currRcptRenderedMessage.$renderedAttachments;
					
					// nelle mail aggiungi sempre il link per accedere alla conversazione
					$currRcptRenderedMessage = $currRcptRenderedMessage.'<br/><br/><hr/><div class="btn btn-primary"><a href="'.Router::url([
						'plugin' => 'messaging', 
						'controller' => 'messages', 
						'action' => 'chat', 
						$conversation_id,
						'?' => [
							'authkey' => $recipient['User']['username'],
							'authpass' => $recipient['User']['password']
						]
					], true).'">✉ Rispondi</a></div>';
					// aggiungi sempre anche il link (più piccolo) per accedere alla propria area riservata (users/home)
					$currRcptRenderedMessage = $currRcptRenderedMessage.'<br/><br/><div class="btn btn-default"><a style="font-size:80% !important" href="'.Router::url([
						'plugin' => false, 
						'controller' => $recipient['User'][USER_ROLE_KEY] == 1 ? 'clienti' : 'categorie_web', 
						'action' => $recipient['User'][USER_ROLE_KEY] == 1 ? 'dashboard' : 'index', 
						'?' => [
							'authkey' => $recipient['User']['username'],
							'authpass' => $recipient['User']['password']
						]
					], true).'">🔒 Accedi alla tua area riservata</a></div>';
					
					// nelle mail aggiungo anche un'intestazione specifica nel caso di messaggi liberi
					if( $this->template == 'messaging_message' ) {
						$UserHelper = new UserHelper(new View());
						$currRcptRenderedMessage = __('Ciao %s,', $UserHelper->displayName($recipient['User']['id']))
							.'<br/><br/>'.__('hai ricevuto un nuovo messaggio da %s', $UserHelper->displayName($this->from['User']['id'])).'<br/><br/><hr/><br/>'
							.$currRcptRenderedMessage;
					}
					
					$emails = [];
					if($recipient['User']['cliente_id']) {
						$emails = $RecapitoModel->getAllEmails($recipient['User']['cliente_id']);
					}
					else if($recipient['User']['id'] < 0) {
						$emails = [CHAT_ADMIN];
					}
					
					foreach($emails as $emailAddr) {
					
					if(empty($emailAddr) || !filter_var($emailAddr, FILTER_VALIDATE_EMAIL) ) continue; // non posso inviare mail
					
					$Email = new CakeEmail();
					$Email->from([CHAT_ADMIN => 'LeZolle']); // fissa, è una notifica a cui non si deve rispondere direttamente!
					$Email->to($emailAddr);
					//$Email->cc('info@bikesquare.eu');
					$Email->replyTo($emailAddr); // in modo che non risponda a questo messaggio ...
					$Email->subject('🥕 '.$this->subject);
					$Email->emailFormat('html');
					// devo inviare il rendered message per poter inserire anche nell'email i link corretti (autenticati)
					$Email->template('default_html'/*$this->template == 'default' ? 'default' : 'default_html'*/, 'default');
					$Email->viewVars([
						'content' => $currRcptRenderedMessage,		
					]);
					/*$Email->attachments([
						'BikeSquare_BtoC_EON.png' => [
							'file' => IMAGES . Configure::read('logo-mail'),
							'mimetype' => 'image/png',
							'contentId' => '12345',
						],
					]);*/
					
					
					$cakeEmails[] = $Email;
					
					}
					
				}
				
			}
			
			// prepara gli interval messages per il salvataggio
			$messagesToSave = [];
			// collega eventuali allegati collegati con questo messaggio ai messaggi generati
			foreach($internalMessages as $m) {
				$messageToSave = [
					'Message' => $m,
					'Attachment' => ['Attachment' => []]
				];
				foreach($attachments as $attachment) {
					$attachment_id = $attachment['Attachment']['id'];
					$messageToSave['Attachment']['Attachment'][$attachment_id] = $attachment_id; 
				}
				$messagesToSave[] = $messageToSave;
			}
			
			if( $MessageModel->saveAll($messagesToSave, ['deep' => true]) ) {
				// salvataggio ok, invia le mail
				foreach($cakeEmails as $cakeEmail) {
					try {
						if( $cakeEmail->send() ) {
							CakeLog::write('emails_info', "Invio con successo a ".implode(',',$cakeEmail->to()));
						}
						else {
							CakeLog::write('emails_error', "Errore durante invio a ".implode(',',$cakeEmail->to()));
						}
					}
					catch(Exception $e) {
						CakeLog::write('emails_error', "Errore durante invio a ".implode(',',$cakeEmail->to()). ' '.$e->getMessage());
					}
				}
			}	
		}
		
		/**
		 * sovrascritto da ConversationMessage come caso speciale
		 */
		protected function getTargetConversationId(array $recipient_ids) {
			$ConversationModel = ClassRegistry::init('BikesquareMessaging.Conversation');
			return $ConversationModel->getChat($contact_id = $this->contact_id, $from = $this->from['User']['id'], $to = $recipient_ids, $this->subject);
		}
		
		/**
		 * 
		 */
		private function removeEmailsIfNotAdmin($message, $user) {
			return $message; // ancora spenta in attesa di trovare una regexp che funzioni senza cancellare le mail nei link (es. link di conferma al cliente della prenotazione)
			
			if(in_array($user[USER_ROLE_KEY], [ROLE_ADMIN])) return $message; 
			// per individuare la regex devo "espandere i tag html con degli spazi
			$message = $this->expandedHtml($message);
			$message = preg_replace('/([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/','[email removed]',$message);
			// ricompatta l'html
			return $this->compactHtml($message);
		}
		
		/**
		 * 
		 */
		private function removePhoneNumbersIfNotAdmin($message, $user) {
			if(in_array($user[USER_ROLE_KEY], [ROLE_ADMIN])) return $message; 
			// per individuare la regex devo "espandere i tag html con degli spazi
			$message = $this->expandedHtml($message);
			$message = preg_replace('/\s*\d{2,8}[-. ]?\d{3,8}[-. ]?\d{3,8}\s*/', '[phone removed]',$message);
			// ricompatta l'html
			return $this->compactHtml($message);
		}
		
		/**
		 * 
		 */
		private function expandedHtml($message) {
			$message = str_replace('<', '< ', $message);
			$message = str_replace('>', '> ', $message);
			return $message;
		}
		
		/**
		 * 
		 */
		private function compactHtml($message) {
			$message = str_replace('< ', '<', $message);
			$message = str_replace('> ', '>', $message);
			return $message;
		}
		
		/**
		 * 
		 */
		private function expandUrlsWithAuth($message, $user) {
			$msgTokens = explode('http', $message);
			
			for($i=0;$i<sizeof($msgTokens);$i++) {
				if( strpos($msgTokens[$i], 's://') === 0 || strpos($msgTokens[$i], '://') === 0 ) {
					// il token comincia effettivamente con un url assoluto, aggiungi le credenziali
					// individua il primo tra spazio, opening tag, \r o \n che individua la fine
					// dell'url
					$ends = [
						strpos($msgTokens[$i], '<'),
						strpos($msgTokens[$i], ' '),
						strpos($msgTokens[$i], '\r'),
						strpos($msgTokens[$i], '\n'),
						strpos($msgTokens[$i], '\t'),
						strpos($msgTokens[$i], '"'),
						strpos($msgTokens[$i], '\''),
						strlen($msgTokens[$i]) // importante nel caso in cui il token finisca con l'url!
					];
					// rimuovi tutti gli end non trovati
					$actualEnds = [];
					foreach($ends as $end) {
						if($end !== false) $actualEnds[] = $end;
					}
					if(empty($actualEnds)) continue; // non dovrebbe succedere ...
					$urlEnd = min($actualEnds);
					
					$url = substr($msgTokens[$i], 0, $urlEnd);
					$tail = substr($msgTokens[$i], $urlEnd);
					
					$q = 'authkey=' . $user['username'] . '&authpass=' . $user['password'];
					if(strpos($url, '?') !== false) {
						$url .= '&'.$q;
					}
					else {
						$url .= '?'.$q;
					}
					
					$msgTokens[$i] = $url . $tail;
				}
			}
			return implode('http', $msgTokens);
		}

	}
