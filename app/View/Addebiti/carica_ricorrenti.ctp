<?php
	$this->assign('title', 'Spese ricorrenti');
	$this->assign('subtitle', 'Carica spese ricorrenti');
?>  

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Caricamento spese ricorrenti da Excel
	</span>
	</div>
</h4>

<?php echo $this->Form->create('Addebito', array('class' => 'form-horizontal', 'type' => 'file'));?>

<div class="row">
	<div class="col-md-9 text-center alert alert-info">
		<span><i class="fa fa-info-circle"></i> Struttura del file in ingresso</span>
		<br/><br/>
		<table class="table">
			<tr>
				<td>A</td>
				<td>B</td>
				<td>C</td>
				<td>D</td>
			</tr>
			<tr>
				<td><b>ID Cliente</b></td>
				<td><i>non elaborata</i></td>
				<td><b>Importo</b></td>
				<td><i>non elaborata</i></td>
			</tr>
		</table>
		
		<p><small><b>La prima riga del file NON viene processata</b></small></p>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right">Mese esazione</label>
	<div class="col-sm-1">
		<?php echo $this->Form->input('mese', array('type' => 'select', 'label' => false, 'class' => 'form-control', 'options' => array(1 => '01', 2 => '02', 3 => '03', 4 => '04', 5 => '05', 6 => '06', 7 => '07', 8 => '08', 9 => '09', 10 => '10', 11 => '11', 12 => '12',)));?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right">Anno esazione</label>
	<div class="col-sm-1">
		<?php
			$years = array();
			foreach( range(2015, 2050) as $y ) $years[ $y ] = $y;
		?>
		<?php echo $this->Form->input('anno', array('type' => 'select', 'label' => false, 'class' => 'form-control', 'options' => $years, 'default' => date('Y')));?>
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
		<?php echo $this->Form->submit('Carica file', array('class' => 'form-submit btn btn-info'));?>
	</div>
</div>

<?php echo $this->Form->end();?>
