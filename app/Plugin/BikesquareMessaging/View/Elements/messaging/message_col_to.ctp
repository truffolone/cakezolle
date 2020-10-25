<div class="mail-ontact read">
	<?php 
		$to = $message['Conversation']['Participant'];
		$to_displayed = isset($to[0]) ? $to[0]['username'] : __('n.a.'); // lo uso come display name
		// se il destinatario è un cliente visualizzo anche il nome referente (Nome cliente - nome persona)
		/*if($to[0][USER_ROLE_KEY] == CLIENTI_GROUP_ID) {
			if( !empty($to[0]['Persona']['id']) ) {
				$to_displayed = $to[0]['Persona']['DisplayName'].' - '.$to_displayed;
			}
		}*/
		if( sizeof($to) > 1 ) $to_displayed .= ' and ' . (sizeof($to)-1) . ' more';
		echo $this->Html->link($to_displayed, array('controller' => 'messages', 'action' => 'conversation', $message['Conversation']['id']));
	?>
</div>
