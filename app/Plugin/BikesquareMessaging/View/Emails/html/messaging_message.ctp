<?php 
	echo __('Ciao %s,', array($this->Kenn->getUserDisplayName($recipient)));
?>

<br/>

<?php if($isNewConversation):?>
<?php echo __('%s ha iniziato una nuova conversazione', array(
	$this->Kenn->getUserDisplayName($sender)
));?>
<?php else:?>
<?php echo __('%s ha aggiornato una conversazione', array(
	$this->Kenn->getUserDisplayName($sender)
));?>
<?php endif;?>

<hr/>

<?php echo $message;?>

<hr/>

<?=$this->Html->link(__('Clicca qui per visualizzare la conversazione'), array(
		'plugin' => 'messaging', 
		'controller' => 'messages', 
		'action' => 'chat', 
		$conversationID, 
		'full_base' => true,
		'?' => [
			'authkey' => $recipient['User']['username'],
			'authpass' => $recipient['User']['password'],
			'origin' => 'notification' // senza questo non potrei fare il redirect al contratto per admin e bam (altro redirezionerei sempre e non va bene)
		]
	))?>

<br/>
<br/>
