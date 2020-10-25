<?php
	
	//$this->assign('title', 'LE TUE ZOLLE');
	$this->assign('title', 'MERCATO LIBERO'); // 2018-07: dovunque titolo fisso "mercato libero
	$this->assign('breadcrumb', $this->App->breadcrumb(
		array(
			'Home' => '#'
		),
		__('le tue zolle')
	));
	
?>

<?php echo $this->Html->script('mangio/ml.1-1', array('inline' => FALSE));?>

<?php $opProvv = $this->Session->read('operazioniProvvisorie');?>

<div class="panel panel-default">
	<div class="panel-body">
		<i class="text-orange fa fa-phone-square"></i> <?php echo __("Se riscontri delle imprecisioni contattaci al %s", array('<a href="tel:'.__('+390692917616').'">'.__('06.9291.7616').'</a>'));?>
	</div>
</div>

	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-zolle">
				<?php echo ucfirst(strtolower(__('le tue zolle')));?>
			</h2>
		</div>
	</div>

<!-- conferma / abbandona modifiche -->
<div class="finalizza-modifiche-container">
<?php if(sizeof($opProvv) > 0):?>
<?php echo $this->element('mangio/finalizza_modifiche');?>
<?php endif;?>
</div>
<!-- /conferma / abbandona modifiche -->

<!-- settimane consegna -->
<div id="consegne-container">
<?php foreach($consegne as $s) echo $s;?>
</div>
<!-- /settimane consegna -->

<!-- conferma / abbandona modifiche -->
<div class="finalizza-modifiche-container">
<?php if(sizeof($opProvv) > 0):?>
<?php echo $this->element('mangio/finalizza_modifiche');?>
<?php endif;?>
</div>
<!-- /conferma / abbandona modifiche -->
