<?php
	$env = $this->Session->read('env');
	$searchKeyword = $this->Session->read('searchKeyword');
?>

<!-- form di ricerca -->
<div class="row">
	<div class="col-xs-12">
		<?php echo $this->Form->create('Prodotto', array('url' => array('controller' => 'articoli', 'action' => 'index')));?>
		<div class="input-group">
			<?php echo $this->Form->input('DESCRIZIONE', array(
				'placeholder' => __('Parola chiave ...'),
				'value' => $searchKeyword,
				'class' => 'form-control',
				'type' => 'text',
				'label' => false
			));?>
			<span class="input-group-btn">
				<button class="btn btn-default" type="submit"><?php echo __('Cerca');?></button>
			</span>
			<span class="input-group-btn">
				<?php echo $this->Html->link(__('Reset'), array('controller' => 'categorie_web', 'action' => 'index', '?' => array(
					strtolower($env) => 1
				)), array(
					'class' => 'btn btn-default'
				));?>
			</span>
		</div><!-- /input-group -->
		<?php echo $this->Form->end();?>
	</div>
</div>
<!-- /form di ricerca -->
<br/> 
