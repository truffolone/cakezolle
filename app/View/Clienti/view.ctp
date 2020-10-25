<?php 
	$this->assign('title', 'Clienti');
	$this->assign('subtitle', 'scheda cliente');
?> 

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Cliente <?php echo $cliente['Cliente']['id'];?> - <?php echo $cliente['Cliente']['displayName'];?>
	</span>
	<span class="col-sm-4">
		<?php echo $this->Html->link('<i class="ace-icon fa fa-shopping-cart align-top bigger-125"></i> Log Area Riservata', array('controller' => 'log_entries', 'action' => 'index', $cliente['Cliente']['id']), array('escape' => false, 'class' => 'btn btn-primary btn-sm pull-right inline', 'title' => 'Log Area Riservata'));?>
		<?php echo $this->Html->link('<i class="ace-icon fa fa-cloud-download align-top bigger-125"></i> Aggiorna', array('action' => 'aggiorna', $cliente['Cliente']['id']), array('escape' => false, 'class' => 'btn btn-primary btn-sm pull-right inline', 'title' => 'Aggiorna i dati del cliente da Zolla'));?>
	</span>
	</div>
</h4>

<table class="table table-striped table-bordered table-hover">
	<tbody>
		<tr>
			<td><span class="grey">ID</span></td>
			<td><?php echo $cliente['Cliente']['id'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Cognome</span></td>
			<td><?php echo $cliente['Cliente']['COGNOME'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Nome</span></td>
			<td><?php echo $cliente['Cliente']['NOME'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Ragione sociale</span></td>
			<td><?php echo $cliente['Cliente']['RAGIONE_SOCIALE'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Codice fiscale</span></td>
			<td><?php echo $cliente['Cliente']['CODICE_FISCALE'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Partita IVA</span></td>
			<td><?php echo $cliente['Cliente']['PARTITA_IVA'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Contatto preferito</span></td>
			<td><?php echo $cliente['Cliente']['CONTATTO_PREFERITO'];?></td>
		</tr>
		<tr>
			<td><span class="grey">RECAPITI CLIENTE</span></td>
			<td>
				<!-- TODO: su zolla non c'è ancora ma dovrebbe essere separati per tipo (amministrativo, commerciale, ecc..)-->
				<table class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th>Tipo</th>
							<th>Recapito</th>
							<th>Principale</th>
							<th>Comunicazioni</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($cliente['Recapito'] as $r):?>
						<tr>
							<td><?php echo $r['TIPO'];?></td>
							<td><?php echo $r['RECAPITO'];?></td>
							<td><?php echo $r['PRINCIPALE'];?></td>
							<td><?php echo $r['COMUNICAZIONI'];?></td>
						</tr>
						<?php endforeach;?>
					</tbody>
				</table>
				<b>Legenda Comunicazioni</b><br>A (amministrativo), C (commerciale), I (informazioni)
			</td>
		</tr>
		
	</tbody>
</table>

<div class="tabbable">
	<ul class="nav nav-tabs padding-12 tab-color-blue background-blue" id="clienteTab">
		<li class="active">
			<a data-toggle="tab" href="#contratti">Contratti</a>
		</li>

		<li>
			<a data-toggle="tab" href="#metodi-pagamento">Metodi di pagamento</a>
		</li>
		
		<li>
			<a data-toggle="tab" href="#metodi-pagamento-disattivati">Storico metodi di pagamento</a>
		</li>

		<li>
			<a data-toggle="tab" href="#spese">Spese</a>
		</li>
		
		<li>
			<a data-toggle="tab" href="#storico-contratti">Storico contratti</a>
		</li>
	</ul>

	<div class="tab-content">
		
		<div id="contratti" class="tab-pane in active">
			
		<?php if(empty($contratti_attivi) && empty($contratti_fatt_attivi)):?>
			<span class="red">Nessun contratto attivo</span>
		<?php else:?>
			<?php
				// separa i contratti per tipologia
				$contratti_propri = array();
				$contratti_come_cliente = array();
				$contratti_come_cliente_fatt = array();
				
				foreach($contratti_attivi as $c) {
					if( $c['cliente_id'] == $c['cliente_fatturazione_id'] ) {
						$contratti_propri[] = $c;
					}
					else {
						$contratti_come_cliente[] = $c;
					}
				}
				foreach($contratti_fatt_attivi as $c) {
					if( $c['cliente_id'] != $c['cliente_fatturazione_id'] ) {
						$contratti_come_cliente_fatt[] = $c;
					}
					// altrimenti non devo fare altro perchè il contratto proprio è già stato inserito con i contratti_attivi
				}
			?>
			<?php if(!empty($contratti_propri)):?>
				<?php echo $this->element('lista_contratti', array('title' => 'Contratti propri (cliente = cliente fatturazione)', 'contratti' => $contratti_propri));?>
			<?php endif;?>
			<?php if(!empty($contratti_come_cliente)):?>
				<?php echo $this->element('lista_contratti', array('title' => 'Contratti come cliente', 'contratti' => $contratti_come_cliente));?>
			<?php endif;?>
			<?php if(!empty($contratti_come_cliente_fatt)):?>
				<?php echo $this->element('lista_contratti', array('title' => 'Contratti come cliente fatturazione', 'contratti' => $contratti_come_cliente_fatt));?>
			<?php endif;?>
		<?php endif;?>
			
		</div>
		
		<div id="metodi-pagamento" class="tab-pane">
			<?php echo $this->element('metodi_pagamento', array('cliente' => $cliente));?>
			
			<div class="row">
			<div class="col-sm-4">
			<div class="widget-box">
				<div class="widget-header">
					<h5 class="widget-title">
						<i class="ace-icon fa fa-money"></i>
						Nuovo metodo di pagamento
					</h5>
				</div>

				<div class="widget-body">
					<div class="widget-main">
						<div class="clearfix">
							<?php echo $this->Form->create('Cliente', array('url' => array('action' => 'add_metodo_pagamento')));?>
							<?php echo $this->Form->input('cliente_id', array('type' => 'hidden', 'value' => $cliente['Cliente']['id']));?>
							<?php echo $this->Form->input('tipo', array('label' => false, 'options' => $metodi_pagamento));?>
							<br/>
							<?php echo $this->Form->end(array('label' => 'Aggiungi', 'class' => 'btn btn-info btn-sm'));?>
						</div>
					</div>
				</div>
			</div>
			</div>
			</div>
			
		</div>
		
		<div id="metodi-pagamento-disattivati" class="tab-pane">
			<?php echo $this->element('metodi_pagamento_disattivati', array('cliente' => $cliente));?>
			
		</div>
		
		<div id="spese" class="tab-pane">
			<?php echo $this->element('addebiti_cliente', array('addebiti' => $cliente['Addebito'], 'metodi' => $metodi_pagamento));?>
		</div>
		
		<div id="storico-contratti" class="tab-pane">
		<?php if(empty($contratti_chiusi) && empty($contratti_fatt_chiusi)):?>
			<span class="red">Nessun contratto chiuso</span>
		<?php else:?>
			<?php
				// separa i contratti per tipologia
				$contratti_propri = array();
				$contratti_come_cliente = array();
				$contratti_come_cliente_fatt = array();
				
				foreach($contratti_chiusi as $c) {
					if( $c['cliente_id'] == $c['cliente_fatturazione_id'] ) {
						$contratti_propri[] = $c;
					}
					else {
						$contratti_come_cliente[] = $c;
					}
				}
				foreach($contratti_fatt_chiusi as $c) {
					if( $c['cliente_id'] != $c['cliente_fatturazione_id'] ) {
						$contratti_come_cliente_fatt[] = $c;
					}
					// altrimenti non devo fare altro perchè il contratto proprio è già stato inserito con i contratti_attivi
				}
			?>
			<?php if(!empty($contratti_propri)):?>
				<?php echo $this->element('lista_contratti', array('title' => 'Contratti propri (cliente = cliente fatturazione)', 'contratti' => $contratti_propri));?>
			<?php endif;?>
			<?php if(!empty($contratti_come_cliente)):?>
				<?php echo $this->element('lista_contratti', array('title' => 'Contratti come cliente', 'contratti' => $contratti_come_cliente));?>
			<?php endif;?>
			<?php if(!empty($contratti_come_cliente_fatt)):?>
				<?php echo $this->element('lista_contratti', array('title' => 'Contratti come cliente fatturazione', 'contratti' => $contratti_come_cliente_fatt));?>
			<?php endif;?>
		<?php endif;?>
		</div>
											
	</div>
</div>
