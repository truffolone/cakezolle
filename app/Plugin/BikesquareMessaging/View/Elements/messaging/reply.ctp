<!-- NON RE-INIZIALIZZO SUMMERNOTE PERCHÈ GIÀ INIZIALIZZATO CON compose.ctp --> 

<?php
	$user = $this->Session->read('Auth.User');
?>

<!--
<div class="mail-box-header">
	<div class="pull-right tooltip-demo">
		<a href="#" class="discard-reply btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="<?php echo __("Return");?>"><i class="fa fa-times"></i> <?php echo __("Return");?></a>
	</div>
    <h2>
		<?php echo 'Re: '.$conversation['Conversation']['subject'];?>
	</h2>
	<?php if(isset($subtitle)):?>
		<h3><?php echo $subtitle;?></h3>
	<?php endif;?>
	
	<div>
		<?php
			$tags = array();
			if(!empty($conversation['Conversation']['attivita_id'])) {
				// aggiungo tag di progetto
				$tag_url = Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'attivita', $conversation['Conversation']['attivita_id']));
				$tags[] = '<a href="'.$tag_url.'"><span class="label label-danger"><big>'.$conversation['Attivita']['name'].'</big></span></a>';
			}
			foreach($conversation['Tag'] as $t) {
				if(empty($conversation['Conversation']['attivita_id'])) {
					// se clicco sul tag filtra x tag il messaging generale
					$tag_url = Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'index', 'tag' => $t['id']));
				}
				else {
					// se clicco sul tag filtra x tag il messaging di progetto
					$tag_url = Router::url(array('plugin' => 'messaging', 'controller' => 'messages', 'action' => 'attivita', $conversation['Conversation']['attivita_id'], 'tag' => $t['id']));
				}
				// specifico in ogni caso label-warning per la struttura della label
				$tags[] = '<a href="'.$tag_url.'"><span class="label label-warning" style="background:'.$t['color'].'"><big>'.$t['name'].'</big></span></a>';
			}
						
			echo implode(' ', $tags);
		?>
	</div>
	
</div>
-->
  
<div class="row">
<div class="col-xs-10">
           
<div class="mail-box">

	<div class="">

        <?php echo $this->Form->create('Conversation', array(
			'id' => 'ReplyMsgForm',
			'url' => Router::url(array('plugin' => 'messaging', 'controller' => 'conversations', 'action' => 'edit', $conversation['Conversation']['id']), true),
			'class' => 'form-horizontal'
		));?>
			
			<?php echo $this->Form->input('Message.0.content', array('type' => 'hidden', 'id' => 'ReplyContent'));?>
			
			<!-- di default al caricamento sono tutti selezionati (così sono a posto anche per il cliente che non vede la selezione) -->
			<select class="hidden" name="recipients" multiple>
				<?php foreach($conversation['Participant'] as $p):?>
					<?php if($p['id'] == $user['id']) continue;?>
					<option class="opt-recipient" value="<?=$p['id'];?>" id="recipient-opt-<?=$p['id'];?>" selected></option>
				<?php endforeach;?>	
			</select>
			
			<?php
				$link_id = uniqid(); // id temporaneo per linkare gli allegati al messaggio
			?>
			<?php echo $this->Form->input('link_id', array('id' => 'attachment-link-id-reply', 'type' => 'hidden', 'value' => $link_id));?>
				
		<?php echo $this->Form->end();?>
	
	</div>

    <div class="mail-text">

		<div id="summernote-reply">
        </div>
		<div class="clearfix"></div>
	</div>
	
	<?php 
			// solo alcuni utenti posso caricare gli attachments
			if( in_array($user[USER_ROLE_KEY], array(ROLE_ADMIN) ) ):?>
	<div class="mail-body" style="background:#F9F8F8">
		<?php echo $this->element('BikesquareMessaging.messaging/form_add_attachments', array(
			'suffix' => 'reply',
			'link_id' => $link_id
		));?>
		<div id="attachments-container-reply"></div>
		<div id="existing-attachments-container-reply">
			<?php echo $this->element('BikesquareMessaging.messaging/message/uploaded_attachments', array(
				'suffix' => 'reply',
				'link_id' => 0, // lato server non viene usato perchè gli allegati sono già collegati a un messaggio
				'attachments' => array()
			));?>
		</div>
	</div>
	<div class="clearfix" style="background:#F9F8F8"></div>
	<?php endif;?>
	
    <div class="mail-body tooltip-demo">
		
		<div id="validation-errors-reply"></div>
		
		<?php if( $user[USER_ROLE_KEY] == ROLE_ADMIN ): // il cliente non può scegliere a chi parlare, tutti altri si?>
        <div class="form-group">
				
					<label>A (<span id="selected-recipients-num"><?=sizeof($conversation['Participant'])-1?></span> destinatari selezionati):</label>
					<!--<?php if($user[USER_ROLE_KEY] == ROLE_ADMIN):?>
						<button type="button" class="btn btn-primary btn-xs" onClick="alert('Coming soon: admin può aggiungere partecipanti alla chat se occorre')"><i class="fa fa-plus-circle"></i></button>
					<?php endif;?>-->
					<div>
					<?php foreach($conversation['Participant'] as $p):?>
						<?php if($p['id'] == $user['id']) continue;?>
						<button type="button" class="btn btn-default btn-chat-participant selected" id="recipient-btn-<?=$p['id'];?>" style="white-space:normal; text-align:left" data-participant-id="<?=$p['id'];?>">
							<i class="fa fa-check"></i>
							<?php echo $this->Html->image($this->Avatar->getAvatar($p), array('class' => '', 'style' => 'height:25px; border-radius:50% !important'));?>
							<?=$this->User->displayName($p);?>
							<?php
								$role = $this->User->getRoleInContact($p, $conversation['Conversation']['attivita_id']);
							?> 
							<span class="label <?=$role['bkg'];?>"><?=$role['name'];?></span>
						</button>
					<?php endforeach;?>
					</div>
				
            </div>
        <br/>
        <?php endif;?>
        <a id="submit-reply" href="#" class="btn btn-primary btn-block" data-toggle="tooltip" data-placement="top" title="<?php echo __("Invia messaggio");?>"><i class="fa fa-send"></i> <?php echo __("Invia messaggio");?></a>
    </div>
    <div class="clearfix"></div>

</div>

</div>

<div class="col-xs-2 text-center">
	<?php
		$user = $this->Session->read('Auth.User');
		echo $this->Html->image($this->Avatar->getAvatar($user), array('style' => 'width:100%; max-width:128px; border-radius:50% !important'));
		?>
</div>

</div>

<?php $this->Html->scriptStart(array('inline' => false)); ?>

$('#summernote-reply').summernote({ 
	height: 100,
	toolbar: [
		// [groupName, [list of button]]
		['style', ['bold', 'italic', 'underline', 'clear']],
		//['fontsize', ['fontsize']],
		['color', ['color']],
		['para', ['ul', 'ol', 'paragraph']],
		//['height', ['height']]
	]
});

<?php $this->Html->scriptEnd(); ?>

