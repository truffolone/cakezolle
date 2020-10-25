<?php 
	$this->assign('title', 'Spese');
	$this->assign('subtitle', 'Scheda');
?> 

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Spesa <?php echo $a['Addebito']['id'];?> | Cliente <?php echo $a['Cliente']['id'];?> - <?php echo $a['Cliente']['displayName'];?>
	</span>
	</div>
</h4>

<?php if($a['Addebito']['blocked']):?>
<div class="alert alert-danger">
	<p>
		La spesa risulta bloccata perchè non è stato possibile ottenere l'esito dell'ultimo tentativo di pagamento
	</p>
	<p>
		Impostare l'esito dell'ultimo tentativo di pagamento cliccando <?php echo $this->Html->link('qui', array('controller' => 'pagamenti_carta', 'action' => 'view', $a['PagamentoCarta'][0]["id"]));?> 
	</p>
</div>
<?php endif;?>

<table class="table table-striped table-bordered table-hover">
	<tbody>
		<tr>
			<td><span class="grey">Cliente</span></td>
			<td><?php echo $this->Html->link($this->MetodoPagamento->getClienteDisplayStr($a['Cliente']), array('controller' => 'clienti', 'action' => 'view', $a['Cliente']['id']));?></td>
		</tr>
		<tr>
			<td><span class="grey">Tipo</span></td>
			<td><?php 
				switch($a['Addebito']['type']) {
					case PRIMO_PAGAMENTO:
						echo 'PRIMO PAGAMENTO CARTA';
						break;
					case RICORRENTE:
						echo 'RICORRENTE';
						break;
					case RICORRENTE_NON_CONFERMATO:
						echo 'DA CONFERMARE (spesa caricata a sistema ma non ancora confermata)';
						break;
					default:
						echo 'N.D.';
				}
			?></td>
		</tr>
		<tr>
			<td><span class="grey">Importo</span></td>
			<td>
				<?php echo $this->Form->create('Addebito');?>
				<?php echo $this->Form->input('importo', array('type' => 'text', 'label' => false, 'value' => $this->Number->currency($a['Addebito']['importo'], ''), 'class' => 'form-control'));?>
				<?php echo $this->Form->submit('Aggiorna importo', array('class' => 'btn btn-warning btn-xs'));?>
				<?php echo $this->Form->end();?>
			</td>
		</tr>
		<tr>
			<td><span class="grey">Periodo</span></td>
			<td><?php
				if( empty($a['Addebito']['mese']) ) echo 'N.D.';
				elseif( empty($a['Addebito']['anno']) ) echo 'N.D.';
				else echo $a['Addebito']['mese'].'/'.$a['Addebito']['anno'];
			?></td>
		</tr>
		<tr>
			<td><span class="grey">Metodo di pagamento</span></td>
			<td><?php 
				$tipo_addebito = 'n.d';
				if( !empty($a['Addebito']['carta_id']) ) {
					$tipo_addebito = 'CARTA';
					switch($a['Addebito']['last_payment_ok']) {
						case -1:
							$stato = '<span class="yellow"><b>Nessun pagamento eseguito</b></span>';
							break;
						case 0:
							$stato = '<span class="red"><b>KO</b></span>';
							break;
						case 1:
							$stato = '<span class="green"><b>OK</b></span>';
							break;
						default:
							$stato = '';
					}
					
					if($a['Addebito']['blocked']) $stato = '<span class="red"><b>BLOCCATA</b></span>';
				}
				else {
					$stato = 'n.d.';
					
					if( !empty($a['Addebito']['rid_id']) ) $tipo_addebito = 'RID';
					elseif( !empty($a['Addebito']['bonifico_id']) ) $tipo_addebito = 'BONIFICO';
					elseif( !empty($a['Addebito']['contante_id']) ) $tipo_addebito = 'CONTANTI';
					elseif( !empty($a['Addebito']['legale_id']) ) $tipo_addebito = 'PROCEDURA LEGALE';
				}
				
				echo $tipo_addebito;
			?></td>
		</tr>
		<tr>
			<td><span class="grey">Stato</span></td>
			<td><?php echo $stato;?></td>
		</tr>
		<tr>
			<td><span class="grey">Attiva</span></td>
			<td><?php echo $a['Addebito']['active'] ? 'SI' : 'NO';?></td>
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
			<td><span class="grey">Cancella la spesa</span></td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'addebiti', 'action' => 'confirm_delete', $a['Addebito']['id']), array('class' => 'btn btn-danger btn-xs btn-block', 'escape' => false));?></td>
		</tr>
		
		<?php if(!empty($a['Addebito']['carta_id']) && $a['Addebito']['last_payment_ok'] != 1 && $a['Addebito']['active'] && empty($a['Addebito']['blocked']) ):?>
		<?php if( $a['Addebito']['type'] == RICORRENTE ):?>
		<tr>
			<td>
				<span class="grey">Esegui il pagamento della spesa</span>
			</td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-money bigger-120"></i>', array('controller' => 'pagamenti_carta', 'action' => 'paga', $a['Addebito']['id']), array('class' => 'btn btn-info btn-xs btn-block', 'escape' => false));?></td>
		</tr>
		<?php endif;?>
		<?php endif;?>
		
	</tbody>
</table>

<?php if( sizeof($a['PagamentoCarta']) > 0 ):?>

<h5 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		<i class="fa fa-money"></i> Pagamenti Carta
	</span>
	</div>
</h5>

<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th>ID</th>
			<th>ID Transazione</th>
			<th>Data creazione</th>
			<th>Esito</th>
			<th>Azioni</th>
		</tr>
	</thead>
	<tbody>
		
		<?php foreach($a['PagamentoCarta'] as $p):?>
		<tr>
			<td><?php echo $p['id'];?></td>
			<td><?php echo $p['transaction_id'];?></td>
			<td><?php echo $p['created'];?></td>
			<td><?php echo empty($p['esito_banca']) ? 'n.d.' : $p['esito_banca'];?></td>
			<td>
				<a title="visualizza pagamento carta" href="<?php echo Router::url(array('controller' => 'pagamenti_carta', 'action' => 'view', $p["id"]));?>" class="btn btn-xs btn-info">
					<i class="ace-icon fa fa-info-circle bigger-120"></i>
				</a>
			</td>
		</tr>
		<?php endforeach;?>
		
	</tbody>
</table>

<?php endif;?>
