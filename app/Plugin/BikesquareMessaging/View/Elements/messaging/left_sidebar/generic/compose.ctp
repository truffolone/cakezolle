<?php
	echo $this->Js->set('default_people', $default_people);
?>

<div id="select-rcpts-shadow" class="messaging-overlay-shadow messaging-overlay-100">
</div>
<div id="select-rcpts-container" class="messaging-overlay-container messaging-overlay-200">


				<div class="mail-box-header">
					<div class="pull-right tooltip-demo">
						<a href="#" id="close-select-rcpts" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="<?php echo __('Return');?>"><i class="fa fa-times"></i> <?php echo __("Return");?></a>
					</div>
					<h2>
						<?php echo __('Select People');?>
					</h2>
					<h3><?php echo __("New conversation");?></h3>
					
				</div>
						   
				<div class="mail-box">

					<div class="mail-body">
						
						
						<div id="generic-compose-rcpts-container">	
							
							<?php foreach($people as $group => $peopleInGroup):?>
								<?php if( sizeof($peopleInGroup) > 0 ):?>
								<span class="text-primary"><?php echo $group;?></span>
								<select id="<?php echo $group;?>-compose-select" class="admin-compose-select chosen-select" multiple="multiple">
									<?php foreach($peopleInGroup as $uid => $name):?>
									<option value="<?php echo $uid;?>"><?php echo $name;?></option>
									<?php endforeach;?>
								</select>
								<div class="space-25"></div>
								<?php endif;?>
							<?php endforeach;?>
						

						
					</div>

					<div class="space-5"></div>     
                    <a id="new-conversation" class="btn btn-block btn-primary compose-mail hidden" href="#"><?php echo __('New conversation');?> <span id="new-conversation-num-rcpts"></span></a>

				</div> <!-- mail-body -->
			</div> <!-- mail-box -->		
</div> <!-- /select-rcpts-container -->
