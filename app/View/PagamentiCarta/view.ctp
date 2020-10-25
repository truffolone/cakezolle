<?php 
	$this->assign('title', 'Pagamenti Carta');
	$this->assign('subtitle', 'Scheda');
?> 

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Pagamento <?php echo $p['PagamentoCarta']['id'];?> | Spesa <?php echo $p['Addebito']['id'];?> | Cliente <?php echo $p['Addebito']['Cliente']['id'];?> - <?php echo $p['Addebito']['Cliente']['displayName'];?>
	</span>
	</div>
</h4>

<?php if( empty($p['PagamentoCarta']['esito_banca']) ):?> 
<?php echo $this->Form->create('PagamentoCarta');?>
<?php endif;?>

<table class="table table-striped table-bordered table-hover">
	<tbody>
		<tr>
			<td><span class="grey">Spesa</span></td>
			<td><?php echo $this->Html->link($p['Addebito']['id'], array('controller' => 'addebiti', 'action' => 'view', $p['Addebito']['id']));?></td>
		</tr>
		<tr>
			<td><span class="grey">Cliente</span></td>
			<td><?php echo $this->Html->link($p['Addebito']['Cliente']['id'].' '.$p['Addebito']['Cliente']['displayName'], array('controller' => 'clienti', 'action' => 'view', $p['Addebito']['Cliente']['id']));?></td>
		</tr>
		<tr>
			<td><span class="grey">ID Transazione</span></td>
			<td><?php echo $p['PagamentoCarta']['transaction_id'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Esito</span></td>
			<td><?php 
				if( empty($p['PagamentoCarta']['esito_banca']) ) {
					$options = array(
						'' => 'sconosciuto',
						'KO' => 'KO',
						'OK' => 'OK',
					);
					echo $this->Form->input('id', array('type' => 'hidden'));
					echo $this->Form->input('saldo_id', array('type' => 'hidden'));
					echo $this->Form->input('esito_banca', array('label' => false, 'class' => 'form-control', 'options' => $options));
					echo '<br/>';
					echo $this->Form->submit('Aggiorna', array('class' => 'btn btn-primary btn-xs'));
				}
				else echo $p['PagamentoCarta']['esito_banca'];
			?></td>
		</tr>
		<tr>
			<td><span class="grey">Dettagli richiesta</span></td>
			<td><?php echo $p['PagamentoCarta']['request_details'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Errori richiesta</span></td>
			<td><?php echo $p['PagamentoCarta']['request_errors'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Dettagli risposta</span></td>
			<td><?php echo $p['PagamentoCarta']['response_details'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Errori risposta</span></td>
			<td><?php echo $p['PagamentoCarta']['response_errors'];?></td>
		</tr>
	</tbody>
</table> 

<?php if( empty($p['PagamentoCarta']['esito_banca']) ):?> 
<?php echo $this->Form->end();?>
<?php endif;?>
