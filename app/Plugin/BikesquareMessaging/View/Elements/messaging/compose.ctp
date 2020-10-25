<?php echo $this->Js->set('defaultConversationSubject', __('Richiesta informazioni'));?>

<?php
	$user = $this->Session->read('Auth.User');
?>

<div id="compose-shadow" class="messaging-overlay-shadow messaging-overlay-300">
</div>
<div id="compose-container" class="messaging-overlay-container messaging-overlay-400">

	<div class="mail-box-header">
		<div class="pull-right tooltip-demo">
			<a href="#" class="discard-composition btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="<?php echo __('Return');?>"><i class="fa fa-times"></i> <?php echo __("Return");?></a>
		</div>
		<h2>
			<?php echo __("New conversation");?>
		</h2>
		<?php if(isset($subtitle)):?>
			<h3><?php echo $subtitle;?></h3>
		<?php endif;?>
	</div>
			   
	<div class="mail-box">

		<div class="mail-body">

			<?php echo $this->Form->create('Conversation', array(
				'id' => 'SendMsgForm',
				'url' => Router::url(array('controller' => 'conversations', 'action' => 'edit'), true),
				'class' => 'form-horizontal'
			));?>
				<div class="form-group" id="compose-recipients">
					<label class="col-sm-2 control-label">To:</label>
					<div class="col-sm-10">
						<?php echo $this->Form->input('Participant.Participant', array('type' => 'select', 'label' => false, 'multiple' => true, 'class' => 'form-control chosen-select', 'options' => $people));?>
					</div>
				</div>
				<div class="form-group">
					
					<label class="col-sm-2 control-label"><?php echo __('Subject:');?></label>
					<div class="col-sm-10">
						<?php echo $this->Form->input('subject', array('type' => 'text', 'label' => false, 'placeholder' => '', 'class' => 'form-control msg-subject', 'value' => ''));?>
					</div>
				</div>
				
				<?php if(isset($attivita_id)):?>
					<?php echo $this->Form->input('attivita_id', array('type' => 'hidden', 'value' => $attivita_id));?>
				<?php endif;?>
				
				<div class="form-group" id="compose-tags">
					
					<label class="col-sm-2 control-label"><?php echo __('Tags:');?></label>
					<div class="col-sm-10">
						<?php echo $this->Form->input('Tag.Tag', array('type' => 'select', 'label' => false, 'multiple' => true, 'class' => 'form-control chosen-select', 'options' => $usable_tags));?>
					</div>
				</div>
				
				<?php if( isset($jobs) && !empty($jobs) ): // uso anche il check su empty per considerare ogni possibile caso ?>
					<div class="form-group">
						
						<label class="col-sm-2 control-label"><?php echo __('Jobs:');?></label>
						<div class="col-sm-10">
							<?php echo $this->Form->input('Job.Job', array('type' => 'select', 'label' => false, 'multiple' => true, 'class' => 'form-control chosen-select', 'options' => $jobs));?>
						</div>
					</div>
				<?php endif;?>
				
				<?php echo $this->Form->input('Message.0.content', array('type' => 'hidden'));?>
				
				<?php
					$link_id = uniqid(); // id temporaneo per linkare gli allegati al messaggio
				?>
				<?php echo $this->Form->input('link_id', array('id' => 'attachment-link-id-compose', 'type' => 'hidden', 'value' => $link_id));?>
					
			<?php echo $this->Form->end();?>
		
		</div>

		<div class="mail-text h-200">

			<div id="summernote-content" class="summernote">
			</div>
			<div class="clearfix"></div>
		</div>
		
		<?php 
			// solo alcuni utenti posso caricare gli attachments
			if( in_array($user[USER_ROLE_KEY], array(ROLE_ADMIN, ROLE_BAM) ) ):?>
		<div class="mail-body" style="background:#F9F8F8">
			<?php echo $this->element('messaging/form_add_attachments', array(
				'suffix' => 'compose',
				'link_id' => $link_id
			));?>
			<div id="attachments-container-compose"></div>
			<div id="existing-attachments-container-compose">
				<?php echo $this->element('messaging/message/uploaded_attachments', array(
					'suffix' => 'compose',
					'link_id' => 0, // lato server non viene usato perchè gli allegati sono già collegati a un messaggio
					'attachments' => array()
				));?>
			</div>
		</div>
		<div class="clearfix" style="background:#F9F8F8"></div>
		<?php endif;?>
		
		<div class="mail-body text-right tooltip-demo">
			
			<div id="validation-errors-compose"></div>
			
			<a href="#" class="discard-composition btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="<?php echo __('Return');?>"><i class="fa fa-times"></i> <?php echo __("Return");?></a>
			<a id="submit-new-conversation" href="#" class="btn btn-sm btn-primary" data-toggle="tooltip" data-placement="top" title="<?php echo __('Send');?>"><i class="fa fa-send"></i> <?php echo __("Send");?></a>
		</div>
		<div class="clearfix"></div>

	</div> <!-- /mail-body -->

</div> <!-- /compose-container -->

<?php $this->Html->scriptStart(array('inline' => false)); ?>
		// summernote
		$(document).ready(function(){
            /*$('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });*/


            $('.summernote').summernote({
				toolbar: [
					//[groupname, [button list]]

					['style', ['bold', 'italic', 'underline', 'clear']],
					['color', ['color']],
					['para', ['ul', 'ol', 'paragraph']],
					//['view', ['codeview']],
				]
			});
			
			// fix per il fatto che non si può impostare una min-height e si può andare in focus solo cliccando in uno spazio molto piccolo
			$('.mail-text').click(function(e){
				if( $(e.target).hasClass('note-toolbar') || $(e.target).parents('.note-toolbar').length ) return;
				$('.note-editable', $(this)).trigger('focus');
			});

        });

<?php $this->Html->scriptEnd();?>

