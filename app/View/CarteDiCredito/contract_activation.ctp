<?php
	
	$this->assign('title', 'ATTIVAZIONE CARTA');
	
?>

	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-profilo">
				<?php echo ucfirst(strtolower(__('attivazione carta')));?>
			</h2>
		</div>
	</div>

<div class="alert alert-info">
	<p>Gentile <?php echo $carta['Cliente']['displayName'];?>,</p>
	<p>
		a seguito di un problema tecnico non è stato possibile attivare automaticamente il servizio pagamenti ricorrenti.
	</p>
	<p>
		Per completare l'attivazione ti invitiamo ad inserire il PAN (numero cifrato della carta di credito) composto dalle 
		<b>PRIME 6 CIFRE</b> e dalle <b>ULTIME 4 CIFRE</b> della tua carta, unitamente
		alla scadenza della carta stessa
	</p>
	<p>Grazie</p>
</div>

<?php echo $this->Form->create('CartaDiCredito',array(
	'url' => Router::url(array('action' => 'contract_activation', $ticket['ContractActivationTicket']['ticket']), true)
	));
?>

<div class="row">
	<div class="col-md-6">
		<?php echo $this->Form->input('token_1', array('required' => TRUE, 'placeholder' => 'XXXXXX', 'label' => 'Prime 6 cifre', 'maxlength' => 6, 'class' => 'form-control'));?>
	</div>
	<div class="col-md-6">
		<?php echo $this->Form->input('token_2', array('required' => TRUE, 'placeholder' => 'XXXX', 'label' => 'Ultime 4 cifre', 'maxlength' => 4, 'class' => 'form-control'));?>
	</div>
</div>

<br/>

<div class="row">
	<div class="col-md-6">
		<?php echo $this->Form->input('mese_scadenza', array('required' => TRUE, 'class' => 'form-control', 'type' => 'date','dateFormat' => 'M', 'minMonth' => '1', 'maxMonth' => '12', 'monthNames' => false));?>
	</div>
	<div class="col-md-6">
		<?php echo $this->Form->input('anno_scadenza', array('required' => TRUE, 'class' => 'form-control', 'type' => 'date','dateFormat' => 'Y', 'minYear' => date('Y'), 'maxYear' => '2050'));?>
	</div>
</div>

<br/>

<?php echo $this->Form->end(array('label' => 'Registra carta', 'class' => 'btn bkg-orange white'));

