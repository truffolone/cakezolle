<?php
	// raggruppa gli articoli in colonne per la visualizzazione
	$col0 = array();
	$col1 = array();
	for($i=0;$i<sizeof($articoli);$i++) {
		if($i%2 == 0) $col0[] = $articoli[$i];
		else $col1[] = $articoli[$i];
	}
	
	$searchKeyword = $this->Session->read('searchKeyword');
?> 

<?php echo $this->Html->script('mangio/ml.1-1', array('inline' => FALSE));?>

<?php $loggedUser = $this->Session->read('Auth.User');?>

<?php if( empty($articoli) ):?>

	<div class="text-center">
		<?php echo $searchKeyword == null ? 
			__('Attualmente non ci sono articoli disponibili in questa categoria (potrebbero tornare disponibili in futuro)') :
			__("Nessun articolo trovato con questa ricerca")
		;?>
	</div>

<?php endif;?>


<!--<div class="container-fluid">-->
	
	<?php for($i=0;$i<sizeof($col0);$i++):?>
	<div class="row">
		<div class="col-md-6" id="box-articolo-<?=$col0[$i]['Articolo']['id']?>">
			<?php echo $this->element('mangio/box_articolo', array('articolo' => $col0[$i], 'user' => $loggedUser));?>
		</div>
		<div class="col-md-6" id="<?= isset($col1[$i]) ? 'box-articolo-'.$col1[$i]['Articolo']['id'] : "";?>">
			<?php if(isset($col1[$i])):?>
				<?php echo $this->element('mangio/box_articolo', array('articolo' => $col1[$i], 'user' => $loggedUser));?>
			<?php endif;?>
		</div>
	</div>
	<?php endfor;?>
	
<!--</div>-->
