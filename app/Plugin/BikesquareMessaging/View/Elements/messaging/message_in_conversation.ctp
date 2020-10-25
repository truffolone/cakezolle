<?php
	$user = $this->Session->read('Auth.User');
	$msgCls = $message['From']['id'] == $user['id'] ? 'right' : 'left';
	$isRcvd = $message['From']['id'] != $user['id'];
	$from = $message['From']['username']; // lo uso come display name	
?>

<div class="chat-message <?= $msgCls;?>">
	<div style="<?= $isRcvd ? 'float:left; margin-right:10px' : 'float:right; margin-left:10px';?>">
		<?php
			// gestione avatar utente
			if( $message['Message']['from_id'] > 0 && !isset($message['From']['id']) ) { // utente collegato al messaggio non esiste più ...
				$message['From'] = [
					'id' => 0,
					'username' => 'Missing User'
				];
			}
			if( $message['Message']['from_id'] == -1 ) { // Bikesqure (utente speciale)
				$message['From'] = [
					'id' => -1,
					'username' => 'info@bikesquare.eu'
				];
				$imagePath = 'BikeSquare_Icona.svg';
			}
			else if( $message['Message']['from_id'] == -2 ) { // Bot (utente speciale)
				$message['From'] = [
					'id' => -2,
					'username' => 'noreply@bikesquare.eu'
				];
				$imagePath = 'BikeSquare_Icona.svg';
			}
			
			echo $this->Html->image($this->Avatar->getAvatar($message['From']), array('class' => ''/*'message-avatar'*/, 'style' => 'height:50px; border-radius:50% !important'));
		?>
		
			<?php if($isRcvd && $user[USER_ROLE_KEY] == ROLE_ADMIN): // per i clienti il messaggio reply non ha senso?>
			<div style="margin-top:5px">
				  <button type="button" title="<?=__('Rispondi a questo messaggio');?>" style="width:100%" class="btn btn-default dropdown-toggle btn-reply-to-message" data-from="<?=$message['Message']['from_id'];?>" data-to="<?=$message['Message']['all_rcpts'];?>">
					<i class="fa fa-reply"></i>
				  </button>
			</div>
			<?php endif;?>
	</div>
	
	<div class="message">
		
		<?php if($message['Message']['all_rcpts']): // se messaggio vecchio non è valorizzato?>
			<?php if(!$isRcvd):?>
				<?php foreach(explode(',', $message['Message']['all_rcpts']) as $r):?>
					<?=$this->Html->image($this->Avatar->getAvatar(trim($r)), array('title' => $this->User->displayName($r), 'class' => '', 'style' => 'border-radius:50% !important; height:25px'));?>
				<?php endforeach;?>
				<i class="fa fa-mail-reply"></i> 
			<?php endif;?>
		<?php endif;?>
		
		<?php if($isRcvd):?>
        <span class="message-author text-primary"> 
		<?php else:?>
		<span class="message-author"> 
		<?php endif;?>
			<b>
			<?php 			
				echo $from;
			?>
			</b> 
		</span> 
		
		<?php if($message['Message']['all_rcpts']): // se messaggio vecchio non è valorizzato?>
			<?php if($isRcvd):?>
				<i class="fa fa-mail-forward"></i> 
				<?php foreach(explode(',', $message['Message']['all_rcpts']) as $r):?>
					<?=$this->Html->image($this->Avatar->getAvatar(trim($r)), array('title' => $this->User->displayName($r), 'class' => '', 'style' => 'border-radius:50% !important; height:25px'));?>
				<?php endforeach;?>
			<?php endif;?>
		<?php endif;?>
		
		<span class="message-content" style="text-align:left">
			<?php echo $this->Text->autoLinkUrls( $message['Message']['content'], ['escape' => false] );?>
		</span>
		<span class="message-date" style="float:left; margin-top:5px; margin-bottom:5px"> <?php echo $this->Kenn->niceDatetime($message['Message']['created']);?> </span>
		
		<?php if(!empty($message['Attachment'])):?>
		
		<div class="space-25"></div>
		<hr/>
		<div class="clearfix">
			<p>
				<span>
					<i class="fa fa-paperclip"></i>
					<?php if( sizeof($message['Attachment']) == 1 ):?> 
						1 attachment
					<?php else:?>
						<?php echo sizeof($message['Attachment']);?> attachments
					<?php endif;?>
				</span>
			</p>
					
			<?php echo $this->element('messaging/message/attachments', array('attachments' => $message['Attachment']));?>
		</div>
		
		<?php endif;?>
		
		<br/> <!-- mandatory -->
		
	</div>
	
</div>
