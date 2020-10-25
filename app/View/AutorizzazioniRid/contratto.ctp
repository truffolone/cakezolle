<?php
	
	$this->assign('title', 'CONTRATTO RID');
	
?>

	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-profilo">
				<?php echo ucfirst(strtolower(__('Autorizzazione di addebito automatico RID')));?>
			</h2>
		</div>
	</div>

<div class="row">
		
	<div class="col-md-6">
		<h4 class="text-orange">Azienda creditrice</h4>
		<div>
			Le Zolle S.r.l. unipersonale
			<br>
			via Giuseppe Belluzzo 55 - 00149 Roma
			<br>
			P.Iva 09848941002
		</div>
	</div>	
	
	<div class="col-md-6">
		<h4 class="text-orange">Intestatario del contratto</h4>
		
		<div class="row">
			<div class="col-xs-4">
				Codice cliente
			</div>
			<div class="col-xs-8">
				<b><?php echo $rid['Cliente']['id'];?></b>
			</div>
		</div>
		
		<div class="row">
			<div class="col-xs-4">
				Cognome e nome
			</div>
			<div class="col-xs-8">
				<b><?php echo $rid['Cliente']['displayName'];?></b>
			</div>
		</div>
		
	</div>	
		
</div>

<br/>
<br/>
<br/>

<div class="row">

	<table class="table">
	
		<tr>
			<th><small>Accetto</small></th>
			<th></th>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Il cliente, sottoscrivendo il presente contratto di attivazione del SERVIZIO PAGAMENTI RICORRENTI autorizza <b>Le Zolle s.r.l.</b> ad addebitare sul proprio conto corrente le somme dovute per gli acquisti effettuati
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				L'addebito delle somme dovute avviene su base mensile. Ogni addebito si riferisce ai prodotti ricevuti durante il mese precedente.
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Le somme non addebitate in un determinato mese di esazione vengono addebitate in modo automatico nel mese successivo, unitamente alla somma dovuta per tale mese.
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				 L'attivazione del SERVIZIO PAGAMENTI RICORRENTI comporta l’inserimento dei propri dati anagrafici e degli estremi bancari all’interno del modulo visualizzato in calce. Una volta accettate le condizioni del presente contratto è necessario effettuare la presentazione del modulo stesso presso il proprio Istituto di credito per la convalida e  la registrazione della Società Le Zolle srl come soggetto creditore di importi mensili legati alla consegna delle Zolle.
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Per il sistema SERVIZIO PAGAMENTI RICORRENTI la compilazione del modulo rappresenta l'accettazione delle condizioni contenute nel presente documento. 
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Il cliente ha facoltà di recedere dal presente servizio in ogni momento senza penalità e senza spese.  
			</small></td>
		</tr>
		
		<tr>
			<td class="text-center">
				<input type="checkbox" class="form-control accettazione" />
			</td>
			<td><small>
				Qualora il cliente desiderasse cambiare modalità di pagamento, anche solo per un periodo, è sufficiente che ne dia comunicazione scritta a Le Zolle s.r.l. tramite una mail a amministrazione@zolle.it. Tale mail deve pervenire entro la fine del mese in cui ha ricevuto i prodotti per i quali desidera cambiare modalità di pagamento.
			</small></td>
		</tr>
	
	</table>

</div>

<div class="row">
	<div class="col-md-12 text-center">
		<a id="conferma-contratto" href="#" class="btn bkg-orange white btn-block">Clicca qui per confermare l'accettazione del contratto</a>
	</div>
</div>

<?php $this->Html->scriptStart(array('inline' => false)); ?>
$(function(){
	$('#conferma-contratto').click(function(e){
		e.preventDefault();
		
		var nonCheckedNum = 0;
		$('.accettazione').each(function(){
			if( !$(this).is(':checked') ) nonCheckedNum++;
		});
		
		if( nonCheckedNum > 0 ) alert('Per procedere è necessario accettare tutte le clausole del contratto');
		else {
			$('#autorizzazione-rid-container').show();
			$(this).hide();
		}
	});
	
	<?php $contrattoAccettato = $this->Session->read('contratto_rid_accettato');?>
	<?php if($contrattoAccettato):?>
	$('.accettazione').each(function(){
		$(this).attr('checked', 'checked');
	});
	$('#autorizzazione-rid-container').show();
	$('#conferma-contratto').hide();
	<?php endif;?>
	
});
<?php $this->Html->scriptEnd();?>

<br/>


<div id="autorizzazione-rid-container" style="display:none">

<?php echo $this->Form->create('AutorizzazioneRid', array(
	'url' => Router::url(array('controller' => 'autorizzazioni_rid', 'action' => 'contratto', $rid['AutorizzazioneRid']['id_contratto_rid']), true),
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

</div> <!-- /autorizzazione rid container -->
