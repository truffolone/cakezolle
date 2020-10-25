<?php $extraCls = isset($active_chat) && $active_chat ? 'active' : '';?>
<?php
	$badge = 0;
	if(isset($chat['Message'])) $badge = sizeof($chat['Message']);
	else {
		if(isset($num_unread)) $badge = $num_unread; 
	}
?>
<a href="<?=$this->Html->url(['plugin' => 'messaging', 'controller' => 'messages', 'action' => 'chat', $chat['Conversation']['id']]);?>" class="list-group-item <?=$extraCls;?>" style="<?= $badge ? 'background:#f4fffd' : '';?>">
	
	<?php if( isset($expanded) && $expanded):?>
		
		<small><i class="fa fa-clock-o"></i> <?=$chat['Conversation']['created'];?></small>
		<div>
			<h5><b>
				<?php if( !empty($badge) ):?>
					<span class="badge bg-success" style="position:relative"><?= sizeof($badge);?></span>
				<?php endif;?>
				<?=$this->Conversation->getTitle($chat);?>
			</b></h5>
		</div>
		<!--<div style="margin-left:20px">
			<?/*=$this->element('chat_participants', [
				'participants' => $chat['Participant'], 
				'contact_id' => $chat['Conversation']['attivita_id'],
				'collapse' => true
			]);*/?>
		</div>-->
	
	<?php else:?>
	
		<div>
			
			<span class="badge <?= $badge > 0 ? 'bg-success' : 'bg-gray';?>" style="float:right"><?=$badge;?></span>
			
			<?=$this->element('chat_participants', [
				'participants' => $chat['Participant'], 
				'contact_id' => $chat['Conversation']['attivita_id'],
				'collapse' => true // anche qui collapsed (è più pulito graficamente)
			]);?>
		</div>
	
	<?php endif;?>
				
</a> 
