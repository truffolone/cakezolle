<?php 
	$this->assign('title', 'Metodi di pagamento');
	$this->assign('subtitle', 'Carte di Credito');
?> 

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		Carta di Credito | Cliente <?php echo $carta['Cliente']['id'];?> - <?php echo $carta['Cliente']['displayName'];?>
	</span>
	</div>
</h4>

<table class="table table-striped table-bordered table-hover">
	<tbody>
		<tr>
			<td><span class="grey">Cliente</span></td>
			<td><?php echo $this->Html->link($this->MetodoPagamento->getClienteDisplayStr($carta['Cliente']), array('controller' => 'clienti', 'action' => 'view', $carta['Cliente']['id']));?></td>
		</tr>
		<tr>
			<td><span class="grey">ID Contratto</span></td>
			<td><?php echo $carta['CartaDiCredito']['id_contratto'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Numero Carta</span></td>
			<td><?php echo $carta['CartaDiCredito']['pan'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Scadenza</span></td>
			<td><?php echo $carta['CartaDiCredito']['scadenza_pan'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Data invio contratto carta</span></td>
			<td><?php echo $carta['CartaDiCredito']['sent'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Data attivazione contratto carta</span></td>
			<td><?php echo $carta['CartaDiCredito']['signed'];?></td>
		</tr>
		<tr class="bkg-blue">
			<td><span class="grey"><b>Stato</b></span></td>
			<td><b><?php echo $this->MetodoPagamento->getStato(CARTA, $carta['CartaDiCredito']);?></b></td>
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
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'carte_di_credito', 'action' => 'confirm_delete', $carta['CartaDiCredito']['id']), array('class' => 'btn btn-danger btn-xs btn-block', 'escape' => false));?></td>
		</tr>
		<?php if( empty($carta['CartaDiCredito']['signed']) ):?>
		<!-- // METODO NON PIÙ USABILE SU ADYEN
		<tr>
			<td> 
				<span class="grey">Invia procedura manuale di attivazione Carta</span>
				<?php if(!empty($carta['ContractActivationTicket'])):?>
					<br/>
					<span class="red">(NOTA: è già stata inviata una procedura manuale. Procedendo ne verrà creata una nuova)</span>
				<?php endif;?>
			</td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-envelope bigger-120"></i>', array('controller' => 'carte_di_credito', 'action' => 'invio_procedura_attivazione_manuale', $carta['CartaDiCredito']['id']), array('class' => 'btn btn-info btn-xs btn-block', 'escape' => false));?></td>
		</tr>-->
		<tr>
			<td><span class="grey">Re-invia email di attivazione</span></td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-envelope bigger-120"></i>', array('controller' => 'carte_di_credito', 'action' => 'invia_mail_attivazione', $carta['CartaDiCredito']['id']), array('class' => 'btn btn-info btn-xs btn-block', 'escape' => false));?></td>
		</tr>
		<!--<tr>
			<td><span class="grey">Invia email di sollecito</span></td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-envelope bigger-120"></i>', array('controller' => 'carte_di_credito', 'action' => 'invia_mail_sollecito', $carta['CartaDiCredito']['id']), array('class' => 'btn btn-info btn-xs btn-block', 'escape' => false));?></td>
		</tr>-->
		<?php endif;?>
		<?php if( !empty($carta['CartaDiCredito']['signed']) && empty($carta['CartaDiCredito']['adyen_psp_reference']) ):?>
		<tr>
			<td><span class="grey">Invia mail di richiesta ri-attivazione contratto per passaggio ad Adyen</span></td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-envelope bigger-120"></i>', array('controller' => 'carte_di_credito', 'action' => 'invia_mail_passaggio_adyen', $carta['CartaDiCredito']['id']), array('class' => 'btn btn-info btn-xs btn-block', 'escape' => false));?></td>
		</tr>
		<?php endif;?>
	</tbody>
</table>
