<?php
	$env = $this->Session->read('env');
	$this->assign('title', 'MERCATO LIBERO');
	$this->assign('breadcrumb', $this->App->breadcrumb(
		array(
			'Home' => '#',
		),
		$env == 'MLV' ? __('Mercato Libero') : __('Prenotazione Articoli')
	));

	$env = $this->Session->read('env');
	$MlEnv = ClassRegistry::init(ucfirst(strtolower($env)));
	$giornoConsegna = $MlEnv->getCurrGiornoConsegna();
	if(empty($giornoConsegna)) { // nn dovrebbero mai succedere
		$categorieWeb = [];
	}
	else {
		$categorieWeb = ClassRegistry::init('CategoriaWeb')->getAll($env, $giornoConsegna);
	}
	
	// raggruppa le categorie in colonne per la visualizzazione
	$col0 = array();
	$col1 = array();
	for($i=0;$i<sizeof($categorieWeb);$i++) {
		if($i%2 == 0) $col0[] = $categorieWeb[$i];
		else $col1[] = $categorieWeb[$i];
	}
?> 

<?php echo $this->element('mangio/cerca_articoli');?>

<div class="container-fluid">
	
	<?php for($i=0;$i<sizeof($col0);$i++):?>
	<div class="row">
		<div class="col-md-6">
			<?php echo $this->element('mangio/box_categoria', array('categoria' => $col0[$i]));?>
		</div>
		<div class="col-md-6">
			<?php if(isset($col1[$i])):?>
				<?php echo $this->element('mangio/box_categoria', array('categoria' => $col1[$i]));?>
			<?php endif;?>
		</div>
	</div>
	<?php endfor;?>
	
</div>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
$(function(){
	$('.box-categoria').click(function(e){
		e.preventDefault();
		window.location.href = $('.link', $(this)).attr('href');
	});
});
<?php $this->Html->scriptEnd(); ?>


<?php if($displayAdyenReminder === true): //visualizza esplicitamente il reminder?>
<?php $this->Html->scriptStart(array('inline' => false)); ?>
$(function(){
	$('#reminder-adyen-modal').modal('show');
});
<?php $this->Html->scriptEnd(); ?>
<?php endif;?>
