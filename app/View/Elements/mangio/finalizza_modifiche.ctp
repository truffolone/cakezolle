<div class="container-fluid">
<div class="row consegne-actions-container jumbotron">
	<div class="col-xs-6">
		<a href="<?php echo Router::url(array('controller' => 'consegne', 'action' => 'finalizza'));?>" class="btn white bkg-green finalizza-modifiche"><i class="fa fa-check-circle"></i> <?php echo __("conferma le modifiche");?></a> 
	</div>
	<div class="col-xs-6 text-right">
		<a href="<?php echo Router::url(array('controller' => 'consegne', 'action' => 'reset'));?>" class="btn white bkg-green abbandona-modifiche"><i class="fa fa-trash"></i> <?php echo __("abbandona le modifiche");?></a> 
	</div>
	<br/><br/>
</div>
</div>
