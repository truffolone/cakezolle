<?php echo $this->Html->script('BikesquareMessaging.bootstrap-colorpicker.min', array('inline' => false));?>
<?php echo $this->Html->css('BikesquareMessaging.colorpicker/css/bootstrap-colorpicker.min');?>

<div class="messagingtags form">

	<div class="row">
		<div class="col-md-12">
			<div class="page-header">
				<h1><?php echo __('Modifica tag messaging'); ?></h1>
			</div>
		</div>
	</div>



	<div class="row">
		<div class="col-md-3">
			<div class="actions">
				<div class="panel panel-default">
					<div class="panel-heading"><?php echo __("Actions");?></div>
						<div class="panel-body">
							<ul class="nav nav-pills nav-stacked">

								<li><?php echo $this->Form->postLink('<span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;'.__('Cancella'), array('action' => 'delete', $this->Form->value('Messagingtag.id')), array('escape' => false), __('Are you sure you want to delete # %s?', $this->Form->value('Messagingtag.id'))); ?></li>
								<li><?php echo $this->Html->link('<span class="glyphicon glyphicon-list"></span>&nbsp;&nbsp;'.__('Elenco tag'), array('action' => 'index'), array('escape' => false)); ?></li>
							</ul>
						</div>
					</div>
				</div>			
		</div><!-- end col md 3 -->
		<div class="col-md-9">
			<?php echo $this->Form->create('Messagingtag', array('role' => 'form')); ?>

				<div class="form-group">
					<?php echo $this->Form->input('id', array('class' => 'form-control', 'placeholder' => 'Id'));?>
				</div>
				<div class="form-group">
					<?php echo $this->Form->input('name', array('class' => 'form-control', 'placeholder' => 'Name', 'label' => 'Name *'));?>
				</div>
				<div class="form-group">
					<?php echo $this->Form->input('color', array('class' => 'form-control', 'placeholder' => 'color', 'id' => 'color', 'label' => 'Color *', 'after' => 'specificare il colore utilizzando il widget a disposizione'));?>
				</div>
				<div class="form-group">
					<?php echo $this->Form->input('UsedBy.UsedBy', array(
						'class' => 'form-control chosen-select', 
						'label' => __('Ruoli che possono usare questo tag'), 
						'type' => 'select',
						'multiple' => true,
						'options' => $user_groups,
					));?>
				</div>
				<div class="form-group">
					<?php echo $this->Form->input('ViewedBy.ViewedBy', array(
						'class' => 'form-control chosen-select', 
						'label' => __('Ruoli che possono vedere questo tag'), 
						'type' => 'select',
						'multiple' => true,
						'options' => $user_groups,
					));?>
				</div>
				<div class="form-group">
					<?php echo $this->Form->submit(__('Submit'), array('class' => 'btn btn-default')); ?>
				</div>

			<?php echo $this->Form->end() ?>

		</div><!-- end col md 12 -->
	</div><!-- end row -->
</div>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
$(function(){
	$('#color').colorpicker({
		format: 'hex',
	});
	
	$('.chosen-select').chosen();
});
<?php $this->Html->scriptEnd();?>
