<?php
	$this->assign('title', 'Spese ricorrenti');
	$this->assign('subtitle', 'Conferma caricamento spese ricorrenti');
?>  

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Conferma caricamento spese ricorrenti
	</span>
	</div>
</h4> 

<div class="row">
	
	<div class="col-md-6">
		<?php 
			echo $this->Form->create('Addebito');
			echo $this->Form->input('op', array('type' => 'hidden', 'value' => CONFERMA));
			echo $this->Form->end(array('label' => 'Conferma caricamento', 'class' => 'btn btn-success btn-sm'));
		?>
	</div>
	
	<div class="col-md-6">
		<?php 
			echo $this->Form->create('Addebito');
			echo $this->Form->input('op', array('type' => 'hidden', 'value' => ABBANDONA));
			echo $this->Form->end(array('label' => 'Abbandona', 'class' => 'btn btn-danger btn-sm pull-right'));
		?>
	</div>
	
</div>

<?php echo $this->element('dt_addebiti', array(
	'titolo' => 'Spese ricorrenti che saranno caricate a sistema',
	'id' => 'addebiti',
	'tipo' => ADDEBITI_CARICATI_DA_CONFERMARE,
));?>

<br/>
<div class="row">
	
	<div class="col-md-6">
		<?php 
			echo $this->Form->create('Addebito');
			echo $this->Form->input('op', array('type' => 'hidden', 'value' => CONFERMA));
			echo $this->Form->end(array('label' => 'Conferma caricamento', 'class' => 'btn btn-success btn-sm'));
		?>
	</div>
	
	<div class="col-md-6">
		<?php 
			echo $this->Form->create('Addebito');
			echo $this->Form->input('op', array('type' => 'hidden', 'value' => ABBANDONA));
			echo $this->Form->end(array('label' => 'Abbandona', 'class' => 'btn btn-danger btn-sm pull-right'));
		?>
	</div>
	
</div>
