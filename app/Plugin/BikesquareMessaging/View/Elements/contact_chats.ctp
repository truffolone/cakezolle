<?php
	$ConversationModel = ClassRegistry::init('BikesquareMessaging.Conversation');
	$chats = $ConversationModel->getAllInContact($contact_id);
	$user = $this->Session->read('Auth.User');
	$active_chat = isset($active_chat) ? $active_chat : -1;
?>

<div class="list-group">
<?php foreach($chats as $chat):?>
	<?=$this->element('chat_box', ['chat' => $chat, 'showTitle' => isset($showTitle) ? $showTitle : true, 'active_chat' => $active_chat == $chat['Conversation']['id']]);?>
<?php endforeach;?>
</div>
