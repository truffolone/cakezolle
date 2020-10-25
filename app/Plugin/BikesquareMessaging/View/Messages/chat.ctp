<div class="row">

	
	<div class="col-md-12">
		
		<?php if(!empty($conversation['Conversation']['closed'])):?>
			<div class="alert alert-info">Conversazione chiusa in data <?=$conversation['Conversation']['closed'];?></div>
		<?php endif;?>
		
		<?= $this->element('BikesquareMessaging.chat', [
			'conversation' => $conversation
		]);?> 
	
	</div>

</div>


