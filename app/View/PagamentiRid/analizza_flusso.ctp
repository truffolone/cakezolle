<?php
	$this->assign('title', 'Spese ricorrenti');
	$this->assign('subtitle', 'Analizza flusso RID');
?>  

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Analizza flusso RID
	</span>
	</div>
</h4>

<?php echo $this->Form->create('PagamentoRid', array('class' => 'form-horizontal', 'type' => 'file'));?>

<div class="row">
	<div class="col-md-9 text-center alert alert-info">
		<span><i class="fa fa-info-circle"></i> Caricare il flusso RID da analizzare</span>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right">File</label>
	<div class="col-sm-3">
		<?php echo $this->Form->input('file', array('type' => 'file', 'label' => false, 'class' => 'col-xs-10 col-sm-5 form-control', 'after' => 'Dimensione massima: <b>'.$upload_max_filesize.'</b>'));?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right"></label>
	<div class="col-sm-9">
		<?php echo $this->Form->submit('Analizza flusso', array('class' => 'form-submit btn btn-info'));?>
	</div>
</div>

<?php echo $this->Form->end();?>

<?php if(isset($disposizioni)):?>

	<h4 class="text-primary">Elenco disposizioni trovate</h4>

	<table class="table table-striped table-bordered table-hover">
		
		<thead>
			<tr>
				<th>Progressivo</th>
				<th>Cliente</th>
			</tr>
		</thead>
		
		<tbody>
			<?php foreach($disposizioni as $d):?>
			<tr>
				<td>
					<?php echo $d['progressivo'];?>
				</td>
				<td>
					<?php echo $d['descrizione'];?>
				</td>
			</tr>
			<?php endforeach;?>
		</tbody>
		
	</table>

<?php endif;?>
