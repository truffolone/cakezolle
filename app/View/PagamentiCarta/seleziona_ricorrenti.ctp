<?php
	$this->assign('title', 'Spese ricorrenti');
	$this->assign('subtitle', 'Seleziona ricorrenti - Carte di Credito');

?>  

<div class="alert alert-info">
	<i class="fa fa-info-circle"></i> Clicca su una riga per selezionare/deselezionare la relativa spesa
</div>

<b>Legenda</b>: <span class="badge" style="background:#888">Spesa deselezionata</span>

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Seleziona ricorrenti - Carte di Credito
	</span>
	</div>
</h4> 

<div class="row">
	<div class="col-md-6">
		<?php echo $this->Html->link('Esegui pagamenti ricorrenti', array('action' => 'esegui_ricorrenti'), array('class' => 'btn btn-sm btn-success'));?>
	</div>
	<div class="col-md-6">
		<?php echo $this->Html->link('Abbandona', '/', array('class' => 'btn btn-sm btn-danger pull-right'));?>
	</div>
</div>

<br/>
<div class="row">
	<div class="col-md-12">
		<?php echo $this->Html->link('Seleziona tutti', array('action' => 'seleziona_ricorrenti', 0), array('class' => 'btn btn-sm btn-default'));?>
		<?php echo $this->Html->link('Deseleziona tutti', array('action' => 'seleziona_ricorrenti', 1), array('class' => 'btn btn-sm btn-default'));?>
	</div>
</div>

<?php echo $this->element('dt_addebiti', array(
	'titolo' => 'Seleziona spese ricorrenti - Carte di Credito',
	'id' => 'addebiti',
	'tipo' => ADDEBITI_CARTE_PAGABILI,
	'toggleable' => TRUE
));?>

<div class="row">
	<div class="col-md-6">
		<?php echo $this->Html->link('Esegui pagamenti ricorrenti', array('action' => 'esegui_ricorrenti'), array('class' => 'btn btn-sm btn-success'));?>
	</div>
	<div class="col-md-6">
		<?php echo $this->Html->link('Abbandona', '/', array('class' => 'btn btn-sm btn-danger pull-right'));?>
	</div>
</div>

<div id="modal-progress-toggle" class="alert alert-warning" style="display:none; position:fixed; bottom: 0">
	Attendere prego, operazione in corso ...
</div>
