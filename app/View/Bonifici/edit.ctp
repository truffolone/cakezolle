<?php 
	$this->assign('title', 'Metodi di pagamento');
	$this->assign('subtitle', 'Bonifico');
?> 

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Bonifico | Cliente <?php echo $b['Cliente']['id'];?> - <?php echo $b['Cliente']['displayName'];?>
	</span>
	</div>
</h4>

<table class="table table-striped table-bordered table-hover">
	<tbody>
		<tr>
			<td><span class="grey">Cliente</span></td>
			<td><?php echo $this->Html->link($this->MetodoPagamento->getClienteDisplayStr($b['Cliente']), array('controller' => 'clienti', 'action' => 'view', $b['Cliente']['id']));?></td>
		</tr>
		<tr>
			<td><span class="grey">Data creazione</span></td>
			<td><?php echo $b['Bonifico']['created'];?></td>
		</tr>
		<tr class="bkg-blue">
			<td><span class="grey"><b>Stato</b></span></td>
			<td><b><?php echo $this->MetodoPagamento->getStato(BONIFICO, $b['Bonifico']);?></b></td>
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
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'bonifici', 'action' => 'confirm_delete', $b['Bonifico']['id']), array('class' => 'btn btn-danger btn-xs btn-block', 'escape' => false));?></td>
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


<?php echo $this->Form->create('Bonifico', array(
	'url' => Router::url(array('controller' => 'bonifici', 'action' => 'edit', $b['Bonifico']['id']), true),
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
