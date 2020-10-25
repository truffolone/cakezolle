<style>
.partipant-collapsed {
	font-size: 70%;
	font-weight: normal;
}
.partipant-expanded {
	font-weight: bold;
}
</style>
<?php
	$user = $this->Session->read('Auth.User');
	
	$avatarSize = $collapse ? '25' : '40';
	$participantCls = $collapse ? 'participant-collapsed' : 'participant-expanded';
?>
<?php foreach($participants as $participant):?>
	<?php if($participant['id'] == $user['id']) continue;?>
	<div class="chat-participant" style="min-height:45px; display:inline-block; margin-right:10px">
		<div style="position:relative">
			<div style="float:left">
				<?= $this->Html->image($this->Avatar->getAvatar($participant), array('class' => '', 'style' => 'height:'.$avatarSize.'px; width:'.$avatarSize.'px; border-radius:50% !important'));?>
			</div>
			
			<div style="padding-left:<?=($avatarSize+5);?>px;">
				<?php if($participant['id'] == -1):?>
				<div class="<?=$participantCls;?>">BikeSquare</div>
				<?php else:?>
					<div class="<?=$participantCls;?>"><?=$this->User->displayName($participant);?></div>
				<?php endif;?>
				<?php $role = $this->User->getRoleInContact($participant, $contact_id);?>
				<?php if(!empty($role)):?>
				<span class="<?=$participantCls;?> <?=$role['bkg'];?> btn-xs" style="color:#fff"><?=$role['name'];?></span>
				<?php else: // span vuoto per mantenere la formattazione?>
				<span></span>
				<?php endif;?>
			</div>
		</div>
	</div>
<?php endforeach;?>
