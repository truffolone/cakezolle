<?php echo $this->element('BikesquareMessaging.jsCss');?>


            <div class="col-md-12 animated fadeInRight no-padding">
				
				<div>
				
					<div class="mail-box">

						<div class="mail-body">

							<?php echo $this->Form->create('Conversation', array(
								'id' => 'SendMsgForm',
								'url' => Router::url(array('controller' => 'conversations', 'action' => 'update', $conversation['Conversation']['id']), true),
								'class' => 'form-horizontal'
							));?>
							
							<?php echo $this->Form->input('id', array('type' => 'hidden'));?>
							
								<div class="form-group">
									<label class="col-sm-2 control-label">To:</label>
									<div class="col-sm-10">
										<div style="margin-top:5px">
										<?php
											$users = array();
											foreach($conversation['Participant'] as $p) {
												if(!$is_admin && !in_array($p[USER_ROLE_KEY], array(ROLE_ADMIN, ROLE_BAM))) continue;
												if($p['id'] == $user['id']) continue;
												$users[] = '<span class="label label-default"><big>'.$p['username'].'</big></span>'; // usato come display name
											}
											
											echo implode('&nbsp;', $users);
										?>
										</div>
									</div>
								</div>
								<div class="form-group">
									
									<label class="col-sm-2 control-label"><?php echo __('Subject:');?></label>
									<div class="col-sm-10">
										<?php echo $this->Form->input('subject', array('type' => 'text', 'label' => false, 'placeholder' => '', 'class' => 'form-control msg-subject'));?>
									</div>
								</div>
								
								<?php if(!empty($conversation['Attivita']['id'])):?>
								<div class="form-group">
									
									<label class="col-sm-2 control-label"><?php echo __('Contact:');?></label>
									<div class="col-sm-10">
										<div style="margin-top:5px">
											<?php echo $conversation['Attivita']['name'];?>
										</div>
									</div>
								</div>
								<?php endif;?>
								
								<div class="form-group">
									
									<label class="col-sm-2 control-label"><?php echo __('Tags:');?></label>
									<div class="col-sm-10">
										<?php echo $this->Form->input('Tag.Tag', array('type' => 'select', 'label' => false, 'multiple' => true, 'class' => 'form-control chosen-select', 'options' => $usable_tags));?>
									</div>
								</div>
		
							<div class="mail-body text-right tooltip-demo">
							
								<a href="<?php echo Router::url(array('controller' => 'messages', 'action' => 'index'));?>" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Cancel"><i class="fa fa-times"></i> <?php echo __("Cancel");?></a>
								<?php echo $this->Form->submit(__("Save"), array('class' => 'btn btn-sm btn-primary', 'escape' => false, 'div' => false));?>
							</div>		
							
							<?php echo $this->Form->end();?>
						
						</div>
						
						
						<div class="clearfix"></div>

					</div>

				
				</div>
				
            </div>
            
            
        </div>
     </div>
  </div>
</div>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
$(document).ready(function(){
	$('.chosen-select').chosen();
});
<?php $this->Html->scriptEnd(); ?>

           

