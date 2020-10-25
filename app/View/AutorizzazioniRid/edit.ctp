<?php 
	$this->assign('title', 'Metodi di pagamento');
	$this->assign('subtitle', 'RID');
?> 

<h4 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		RID | Cliente <?php echo $rid['Cliente']['id'];?> - <?php echo $rid['Cliente']['displayName'];?>
	</span>
	</div>
</h4>

<table class="table table-striped table-bordered table-hover">
	<tbody>
		<tr>
			<td><span class="grey">Cliente</span></td>
			<td><?php echo $this->Html->link($this->MetodoPagamento->getClienteDisplayStr($rid['Cliente']), array('controller' => 'clienti', 'action' => 'view', $rid['Cliente']['id']));?></td>
		</tr>
		<tr>
			<td><span class="grey">Data invio contratto</span></td>
			<td><?php echo $rid['AutorizzazioneRid']['rid_sent'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Data compilazione contratto</span></td>
			<td><?php echo $rid['AutorizzazioneRid']['rid_filled'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Data attivazione contratto</span></td>
			<td><?php echo $rid['AutorizzazioneRid']['rid_activated'];?></td>
		</tr>
		<tr class="bkg-blue">
			<td><span class="grey"><b>Stato</b></span></td>
			<td><b><?php echo $this->MetodoPagamento->getStato(RID, $rid['AutorizzazioneRid']);?></b></td>
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
		<?php if( empty($rid['AutorizzazioneRid']['rid_activated']) ):?>
		<tr>
			<td><span class="grey">Registra attivazione RID</span></td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-check bigger-120"></i>', array('controller' => 'autorizzazioni_rid', 'action' => 'registra_attivazione', $rid['AutorizzazioneRid']['id']), array('class' => 'btn btn-success btn-xs btn-block', 'escape' => false));?></td>
		</tr>
		<?php endif;?>
		<tr>
			<td><span class="grey">Cancella il metodo di pagamento</span></td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'autorizzazioni_rid', 'action' => 'confirm_delete', $rid['AutorizzazioneRid']['id']), array('class' => 'btn btn-danger btn-xs btn-block', 'escape' => false));?></td>
		</tr>
		<?php if( empty($rid['AutorizzazioneRid']['rid_filled']) ):?>
		<tr>
			<td><span class="grey">Re-invia email di attivazione</span></td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-envelope bigger-120"></i>', array('controller' => 'autorizzazioni_rid', 'action' => 'invia_mail_attivazione', $rid['AutorizzazioneRid']['id']), array('class' => 'btn btn-info btn-xs btn-block', 'escape' => false));?></td>
		</tr>
		<!--<tr>
			<td><span class="grey">Invia email di sollecito</span></td>
			<td><?php echo $this->Html->link('<i class="ace-icon fa fa-envelope bigger-120"></i>', array('controller' => 'autorizzazioni_rid', 'action' => 'invia_mail_sollecito', $rid['AutorizzazioneRid']['id']), array('class' => 'btn btn-info btn-xs btn-block', 'escape' => false));?></td>
		</tr>-->
		<?php endif;?>
	</tbody>
</table>



<h5 class="header blue">
	<div class="row">
	<span class="col-sm-8">
		<i class="fa fa-cog"></i> Modifica autorizzazione RID
	</span>
	</div>
</h5>


				<?php echo $this->Form->create('AutorizzazioneRid', array(
					'url' => Router::url(array('controller' => 'autorizzazioni_rid', 'action' => 'edit', $rid['AutorizzazioneRid']['id']), true),
				));?>
				<?php echo $this->Form->input('id', array('type' => 'hidden'));?>
				<?php echo $this->Form->input('id_contratto_rid', array('type' => 'hidden'));?>
						
				<h3 class="titolo-profilo2">RID - Autorizzazione permanente di addebito in C/C</h3>
						
				<div class="row">
					
					<!--azienda creditrice-->
					<div class="col-md-6">
								
						<span>Azienda Creditrice</span>
						<hr/>
						<span><b>Le Zolle srl</b></span>
						
						<br/>
						<br/>
						<br/>
								
						<span>Coordinate dell'Azienda Creditrice</span>
						<hr/>
						<div class="row">
							<div class="col-xs-4">
								<div>Cod. Azienda SIA</div>
								<div><b>AWPQS</b></div>
							</div>
							<div class="col-xs-4">
								<div>(*)</div>
								<div><b>4</b></div>
							</div>
							<div class="col-xs-4">
								<div>Codice cliente</div>
								<div><b><?php echo $rid['Cliente']['id'];?></b></div>
							</div>
						</div>
								
					</div>
							
					<!--banca del debitore-->
					<div class="col-md-6">
							
						<span>Banca del Debitore</span>
						<hr/>
						<?php echo $this->Form->input('banca_debitore', array('label' => 'Banca', 'class' => 'form-control'));?>
						<?php echo $this->Form->input('agenzia_debitore', array('label' => 'Agenzia', 'class' => 'form-control'));?>
						
					</div>
							
				</div>

				<br/><br/>

				<!--coordinate bancarie del conto da addebitare-->
				<div class="row">

					<div class="col-md-12">
						<span>Coordinate bancarie del conto da addebitare</span>
						<hr/>
					</div>

					<div class="col-md-1">
						<label>Cod Paese</label>
						<div>IT</div>
					</div>
					
					<div class="col-md-1">
						<?php echo $this->Form->input('check_digit', array('class' => 'form-control', 'size' => 2));?>
					</div>
					
					<div class="col-md-1">
						<?php echo $this->Form->input('cin', array('class' => 'form-control', 'size' => 1, 'label' => '<br>CIN'));?>
					</div>
					
					<div class="col-md-2">
						<?php echo $this->Form->input('abi', array('class' => 'form-control', 'size' => 5, 'label' => '<br>ABI'));?>
					</div>
					
					<div class="col-md-2">
						<?php echo $this->Form->input('cab', array('class' => 'form-control', 'size' => 5, 'label' => '<br>CAB'));?>
					</div>
					
					<div class="col-md-5">
						<?php echo $this->Form->input('conto_corrente', array('class' => 'form-control', 'size' => 12, 'label' => '<br>Conto Corrente'));?>
					</div>

				</div>

				<br/><br/>

				<div class="row">

					<div class="col-md-6">
						<span>Sottoscrittore del Modulo</span>
						<hr/>
						<?php echo $this->Form->input('nome_sottoscrittore', array('class' => 'form-control'));?>
						<?php echo $this->Form->input('indirizzo_sottoscrittore', array('class' => 'form-control'));?>
						<?php echo $this->Form->input('localita_sottoscrittore', array('class' => 'form-control'));?>
						<?php echo $this->Form->input('cap_sottoscrittore', array('class' => 'form-control'));?>
						<?php echo $this->Form->input('codice_fiscale_sottoscrittore', array('class' => 'form-control'));?>
					</div>

					<div class="col-md-6">
						<span>Intestatario del Conto</span>
						<hr/>
						<?php echo $this->Form->input('anagrafica_intestatario', array('class' => 'form-control'));?>
						<?php echo $this->Form->input('codice_fiscale_intestatario', array('class' => 'form-control'));?>
						<?php echo $this->Form->input('partita_iva_intestatario', array('class' => 'form-control'));?>
					</div>

				</div>

				<br/><br/>

				<div class="row">

					<div class="well col-md-12">
					
						<div class="text-center">
							<b>ADESIONE</b>
						</div>
						<br/>
						<br/>
						<p>
							Il sottoscritto autorizza la banca a margine ad addebitare sul c/c indicato, nella data di scadenza dell'obbligazione o data prorogata d'iniziativa del creditore (ferma restando la valuta originaria concordata) tutti gli ordini di incasso elettronici inviati dall'azienda e contrassegnati con le coordinate dell'azienda creditrice su riportate (o aggiornate di iniziativa dall'azienda) a condizione che vi siano disponibilità sufficienti e senza necessità per la banca di inviare la relativa contabile di addebito.
						</p>
							
						<div id="opzione-addebito" class="text-center">
						<?php
							$options = array(1 => 'data scadenza o data prorogata dal creditore',2 => 'ovvero 5 giorni lavorativi dopo la data di scadenza o data prorogata dal creditore');
							$attributes = array('legend' => false, 'class' => 'form-control'); //nessun valore di default (l'opzione non viene necessariamente valorizzata dal cliente)
							echo $this->Form->radio('opzione_addebito',$options,$attributes);	
						?>
						</div>
						<br/>
						<p>
							"Le parti hanno la facoltà di recedere in ogni momento dal presente accordo, con un preavviso pari a quello previsto nel contratto di conto corrente per il recesso da quest'ultimo rapporto, da darsi mediante comunicazione scritta.
				Il sottoscrittore prende atto che sono applicate le condizioni già indicate nel contratto di conto corrente, in precedenza sottoscritto tra le parti, o comunque rese pubbliche presso gli sportelli della banca e tempo per tempo vigenti.
				Per quanto non espressamente previsto dalle presenti disposizioni, sono applicabili le ""Norme che regolano i conti correnti di corrispondenza e servizi connessi"" a suo tempo sottoscritte dalle parti, che formano parte integrante del presente accordo."
						</p>
					</div>
					
					<br/>
					<?php echo $this->Form->submit('Procedi', array('class' => 'btn bkg-orange white btn-block'));?>

				</div>

				<?php echo $this->Form->end();?>


