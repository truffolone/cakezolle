<?php echo $this->element('BikesquareMessaging.jsCss');?>

<?php //echo $this->Html->css('BikesquareMessaging.summernote');?>
<?php //echo $this->Html->css('BikesquareMessaging.summernote-bs3');?>

<?php //echo $this->Html->script('BikesquareMessaging.summernote.min', array('inline' => false));?>
<?php echo $this->Html->script('BikesquareMessaging.toastr.min');?>
<?php echo $this->Html->script('BikesquareMessaging.messaging', array('inline' => false));?>

<link href="//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.css" rel="stylesheet">
<?php echo $this->Html->script('//cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote.js', array('inline' => false));?>

<?php
	$user = $this->Session->read('Auth.User');
	$is_admin = $user[USER_ROLE_KEY] == ROLE_ADMIN;
	
	// non pulitissimo per il modello MVC ma così facendo mi basta passare l'id della conversazione e posso
	// embeddare la chat ovunque ...
	if(isset($conversation_id)) {
		$ConversationModel = ClassRegistry::init('BikesquareMessaging.Conversation');
		$conversation = $ConversationModel->find('first', [
			'conditions' => ['Conversation.id' => $conversation_id],
			'recursive' => -1,
			'contain' => array(
				'Tag', // mi servono tutti i campi,
				'Participant' => array(
					'fields' => array('Participant.id', 'username', USER_ROLE_KEY),
				),
				'Attivita' => array( // per le conversazioni legate ad una attivita (contratto)
					'fields' => array('Attivita.id', 'rental_date', 'return_date', 'legendastati_id')
				)
			)
		]);
	}
	// altrimenti ho già passato l'effettiva conversazione
	
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
?>

<?php
	$loggedUser = $this->Session->read('Auth.User');
?>
<?php if(empty($conversation['Conversation']['closed'])):?>
<div id="reply-container">
					<?php echo $this->element('BikesquareMessaging.messaging/reply', array(
						'is_admin' => $is_admin,
						'conversation' => $conversation,
						'user' => $user,
						'subtitle' => empty($conversation['Attivita']['id']) ? null : $conversation['Attivita']['name']
					));?>
</div>
<?php endif;?>

				
<!-- conversazione -->
<div id="messages-container" class="">
					<?php echo $this->element('BikesquareMessaging.messaging/conversation', array(
						'is_admin' => $is_admin,
						'conversation' => $conversation,
						'user' => $user,
						'subtitle' => empty($conversation['Attivita']['id']) ? null : $conversation['Attivita']['name'],
						'is_chat' => true // non faccio vedere elenco partecipanti
					));?>
</div> 
