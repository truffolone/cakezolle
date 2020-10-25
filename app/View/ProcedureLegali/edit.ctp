<?php 
	$this->assign('title', 'Metodi di pagamento');
	$this->assign('subtitle', 'Procedura legale');
?> 

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Procedura legale | Cliente <?php echo $p['Cliente']['id'];?> - <?php echo $p['Cliente']['displayName'];?>
	</span>
	</div>
</h4>

<table class="table table-striped table-bordered table-hover">
	<tbody>
		<tr>
			<td><span class="grey">Cliente</span></td>
			<td><?php echo $this->Html->link($this->MetodoPagamento->getClienteDisplayStr($p['Cliente']), array('controller' => 'clienti', 'action' => 'view', $p['Cliente']['id']));?></td>
		</tr>
		<tr>
			<td><span class="grey">Data creazione</span></td>
			<td><?php echo $p['ProceduraLegale']['created'];?></td>
		</tr>
		<tr class="bkg-blue">
			<td><span class="grey"><b>Stato</b></span></td>
			<td><b><?php echo $this->MetodoPagamento->getStato(PROCEDURA_LEGALE, $p['ProceduraLegale']);?></b></td>
		</tr>
	</tbody>
</table> 



<h5 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		<i class="fa fa-cog"></i> Operazioni disponibili
	</span>
	</div>
</h5>

<table class="table table-bordered table-hover">
	<tbody>
		<tr>
			<td><span class="grey">Cancella il metodo di pagamento</span></td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'procedure_legali', 'action' => 'confirm_delete', $p['ProceduraLegale']['id']), array('class' => 'btn btn-danger btn-xs btn-block', 'escape' => false));?></td>
		</tr>
	</tbody>
</table>



<h5 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		<i class="fa fa-cog"></i> Note
	</span>
	</div>
</h5>


<?php echo $this->Form->create('ProceduraLegale', array(
	'url' => Router::url(array('controller' => 'procedure_legali', 'action' => 'edit', $p['ProceduraLegale']['id']), true),
));?>
<?php echo $this->Form->input('id', array('type' => 'hidden'));?>
<?php echo $this->Form->input('note', array('label' => false, 'type' => 'textarea', 'class' => 'wysiwyg-editor', 'style' => 'width:100%'));?>
<?php echo $this->Form->submit('Salva note', array('class' => 'btn bkg-orange white'));?>

<?php echo $this->Form->end();?>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
jQuery(function($) {
	$('.wysiwyg-editor').ace_wysiwyg({
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
