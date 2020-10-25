<?php echo $this->Html->script('bootstrap-wysiwyg.min', array('inline' => false));?> 
<?php echo $this->Html->script('jquery.hotkeys.min', array('inline' => false));?>
<?php echo $this->Html->script('bootbox.min', array('inline' => false));?>

<?php 
	$this->assign('title', 'Contratti');
	$this->assign('subtitle', 'chiudi');
?> 

<h4 class="header blue">Chiusura contratto <?php echo $this->request->data['Contratto']['id'];?></h4>

<div class="alert alert-warning">
	<p>
		NOTA: attualmente la gestione dei contratti avviene automaticamente durante la sincronizzazione con zolla,
		quindi non è necessario usare questa sezione
	</p>
</div>

<?php echo $this->Form->create('Contratto');?>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right">Note</label>
	<div class="col-sm-9">
		<?php echo $this->Form->input('id', array('type' => 'hidden'));?>
		<?php echo $this->Form->input('data_chiusura', array('type' => 'hidden', 'default' => date('Y-m-d H:i:s')));?>
		<?php echo $this->Form->input('note', array('label' => false, 'type' => 'textarea', 'class' => 'wysiwyg-editor', 'style' => 'width:100%'));?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right"></label>
	<div class="col-sm-9">
		<?php echo $this->Form->submit('Chiudi contratto', array('class' => 'form-submit btn btn-info'));?>
	</div>
</div>

<?php echo $this->Form->end();?>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
jQuery(function($) {
	$('#ContrattoNote').ace_wysiwyg({
		toolbar:
		[
			'font',
			null,
			'fontSize',
			null,
			{name:'bold', className:'btn-info'},
			{name:'italic', className:'btn-info'},
			{name:'strikethrough', className:'btn-info'},
			{name:'underline', className:'btn-info'},
			null,
			{name:'insertunorderedlist', className:'btn-success'},
			{name:'insertorderedlist', className:'btn-success'},
			{name:'outdent', className:'btn-purple'},
			{name:'indent', className:'btn-purple'},
			null,
			{name:'justifyleft', className:'btn-primary'},
			{name:'justifycenter', className:'btn-primary'},
			{name:'justifyright', className:'btn-primary'},
			{name:'justifyfull', className:'btn-inverse'},
			null,
			{name:'createLink', className:'btn-pink'},
			{name:'unlink', className:'btn-pink'},
			null,
			null,
			null,
			'foreColor',
			null,
			{name:'undo', className:'btn-grey'},
			{name:'redo', className:'btn-grey'}
		],
		'wysiwyg': {
			fileUploadError: false
		}
	}).prev().addClass('wysiwyg-style2');
});

<?php $this->Html->scriptEnd(); ?>
