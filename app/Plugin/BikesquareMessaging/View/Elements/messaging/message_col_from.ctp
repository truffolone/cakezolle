<?php
	if( $message['Message']['is_read'] ) $row_class = 'read';
	else $row_class = 'unread';
?>
<div class="mail-ontact <?php echo $row_class;?>">
<div class="">
<?php 
	$from = $message['From']['username']; // lo uso come display name
	if( empty($from) ) $from = 'n.a.'; // non deve mai succedere ...
	
	if( $message['Message']['from_id'] == -1 ) $from = 'BikeSquare'; // bot
	
	echo $this->Html->link($from, array('controller' => 'messages', 'action' => 'conversation', $message['Conversation']['id']));
?>
</div>
</div>
