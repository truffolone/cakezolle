<?php 
	$this->assign('title', 'Contratti');
	$this->assign('subtitle', 'Nuovo contratto');
?> 

<div class="alert alert-warning">
	<p>
		NOTA: attualmente la gestione dei contratti avviene automaticamente durante la sincronizzazione con zolla,
		quindi non è necessario usare questa sezione
	</p>
</div>

<?php echo $this->Form->create('Contratto', array('class' => 'form-horizontal', 'role' => 'form'));?>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right">ID cliente</label>
	<div class="col-sm-9">
		<?php echo $this->Form->input('cliente_id', array('type' => 'text', 'label' => false, 'class' => 'col-xs-10 col-sm-5', 'placeholder' => 'ID cliente'));?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right">ID cliente fatturazione</label>
	<div class="col-sm-9">
		<?php echo $this->Form->input('cliente_fatturazione_id', array('type' => 'text', 'label' => false, 'class' => 'col-xs-10 col-sm-5', 'placeholder' => 'ID cliente fatturazione'));?>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right">Metodo di pagamento iniziale</label>
	<div class="col-sm-9">
		<div class="col-xs-10 col-sm-5" style="padding:0">
			<?php echo $this->Form->input('metodo_pagamento', array('type' => 'select', 'label' => false, 'class' => 'form-control', 'options' => $metodi_pagamento));?>
		</div>
	</div>
</div>

<div class="form-group">
	<label class="col-sm-3 control-label no-padding-right"></label>
	<div class="col-sm-9">
		<?php echo $this->Form->submit('Inserisci', array('class' => 'form-submit btn btn-info'));?>
		<br/>
		<span class="red"><i>
			N.B. se esiste già un contratto attivo per la coppia ID Cliente - ID Cliente Fatturazione
			<br/>
			questo verrà chiuso automaticamente (può esistere un solo contratto attivo per una data coppia di clienti)
		</i></span>
	</div>
</div>

<div class="alert alert-info">
	<b>Metodo di pagamento iniziale</b>
	<br/><br/>
	E' il metodo di pagamento utilizzato da Cliente Fatturazione per pagare le spese:
	<br/><br/>
	Il metodo scelto diventa il metodo di pagamento attivo per Cliente Fatturazione e verrà utilizzato per pagare 
	le spese di tutti i contratti (sia esistenti sia nuovi) in cui paga ( = riceve fattura):
	<br/><br/>
	<ul>
		<li>
			Se il metodo scelto era già stato attivato in precedenza dal cliente, al cliente viene inviata solo un'email di 
			benvenuto con i dettagli del nuovo contratto 
		</li>
		
		<li>
			Se il metodo di pagamento scelto non è ancora disponibile per il cliente, viene inviata email di benvenuto
			con i dettagli per l'attivazione a seconda del metodo scelto
		</li>
		
	</ul>
	
</div>

<?php echo $this->Form->end();?>
