<?php
	$this->assign('title', 'Spese ricorrenti');
	$this->assign('subtitle', 'Seleziona ricorrenti - RID');

?>  

<div class="alert alert-info">
	<i class="fa fa-info-circle"></i> Clicca su una riga per selezionare/deselezionare la relativa spesa
</div>

<b>Legenda</b>: <span class="badge" style="background:#888">Spesa deselezionata</span>

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Seleziona ricorrenti - RID
	</span>
	</div>
</h4> 

<div class="row">
	<?php echo $this->Form->create('PagamentoRid', array('url' => Router::url(array('controller' => 'pagamenti_rid', 'action' => 'genera_flusso'), true)));?>
	<div class="col-md-2">
		<?php echo $this->Form->input('data_scadenza', array('required' => true, 'type' => 'text', 'class' => 'form-control', 'label' => false, 'placeholder' => 'Data scadenza'));?>
	</div>
	<div class="col-md-6">
		<?php echo $this->Form->submit('Genera flusso RID', array('class' => 'btn btn-sm btn-success'));?>
	</div>
	<?php echo $this->Form->end();?>
	<div class="col-md-4">
		<?php echo $this->Html->link('Abbandona', '/', array('class' => 'btn btn-sm btn-danger pull-right'));?>
	</div>
</div>

<?php echo $this->element('dt_addebiti', array(
	'titolo' => 'Seleziona spese ricorrenti - RID',
	'id' => 'addebiti',
	'tipo' => ADDEBITI_RID_PAGABILI,
	'toggleable' => TRUE
));?>

<div id="modal-progress-toggle" class="alert alert-warning" style="display:none; position:fixed; bottom: 0">
	Attendere prego, operazione in corso ...
</div>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
jQuery(function($) {
			
				$( "#PagamentoRidDataScadenza" ).datepicker({
					showOtherMonths: true,
					selectOtherMonths: false,
					dateFormat: "dd/mm/yy",
					//isRTL:true,
			
					
					/*
					changeMonth: true,
					changeYear: true,
					
					showButtonPanel: true,
					beforeShow: function() {
						//change button colors
						var datepicker = $(this).datepicker( "widget" );
						setTimeout(function(){
							var buttons = datepicker.find('.ui-datepicker-buttonpane')
							.find('button');
							buttons.eq(0).addClass('btn btn-xs');
							buttons.eq(1).addClass('btn btn-xs btn-success');
							buttons.wrapInner('<span class="bigger-110" />');
						}, 0);
					}
			*/
				});
});
				
<?php $this->Html->scriptEnd(); ?>
