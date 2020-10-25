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
		<label>Banca</label>
		<div><?php echo $rid['AutorizzazioneRid']['banca_debitore'];?></div>
		<label>Agenzia</label>
		<div><?php echo $rid['AutorizzazioneRid']['agenzia_debitore'];?></div>
		
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
		<label>Check digit</label>
		<div><?php echo $rid['AutorizzazioneRid']['check_digit'];?></div>
	</div>
	
	<div class="col-md-1">
		<br/><label>CIN</label>
		<div><?php echo $rid['AutorizzazioneRid']['cin'];?></div>
	</div>
	
	<div class="col-md-2">
		<br/><label>ABI</label>
		<div><?php echo $rid['AutorizzazioneRid']['abi'];?></div>
	</div>
	
	<div class="col-md-2">
		<br/><label>CAB</label>
		<div><?php echo $rid['AutorizzazioneRid']['cab'];?></div>
	</div>
	
	<div class="col-md-5">
		<br/><label>Conto Corrente</label>
		<div><?php echo $rid['AutorizzazioneRid']['conto_corrente'];?></div>
	</div>

</div>

<br/><br/>

<div class="row">

	<div class="col-md-6">
		<span>Sottoscrittore del Modulo</span>
		<hr/>
		<br/><label>Nome sottoscrittore</label>
		<div><?php echo $rid['AutorizzazioneRid']['nome_sottoscrittore'];?></div>
		<br/><label>Indirizzo sottoscrittore</label>
		<div><?php echo $rid['AutorizzazioneRid']['indirizzo_sottoscrittore'];?></div>
		<br/><label>Località sottoscrittore</label>
		<div><?php echo $rid['AutorizzazioneRid']['localita_sottoscrittore'];?></div>
		<br/><label>CAP sottoscrittore</label>
		<div><?php echo $rid['AutorizzazioneRid']['cap_sottoscrittore'];?></div>
		<br/><label>CF sottoscrittore</label>
		<div><?php echo $rid['AutorizzazioneRid']['codice_fiscale_sottoscrittore'];?></div>
	</div>

	<div class="col-md-6">
		<span>Intestatario del Conto</span>
		<hr/>
		<br/><label>Anagrafica intestatario</label>
		<div><?php echo $rid['AutorizzazioneRid']['anagrafica_intestatario'];?></div>
		<br/><label>CF intestatario</label>
		<div><?php echo $rid['AutorizzazioneRid']['codice_fiscale_intestatario'];?></div>
		<br/><label>PIVA intestatario</label>
		<div><?php echo $rid['AutorizzazioneRid']['partita_iva_intestatario'];?></div>
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
			echo isset($rid['AutorizzazioneRid']['opzione_addebito']) ? $options[ $rid['AutorizzazioneRid']['opzione_addebito'] ] : 'non specificato'; 
		?>
		</div>
		<br/>
		<p>
			"Le parti hanno la facoltà di recedere in ogni momento dal presente accordo, con un preavviso pari a quello previsto nel contratto di conto corrente per il recesso da quest'ultimo rapporto, da darsi mediante comunicazione scritta.
Il sottoscrittore prende atto che sono applicate le condizioni già indicate nel contratto di conto corrente, in precedenza sottoscritto tra le parti, o comunque rese pubbliche presso gli sportelli della banca e tempo per tempo vigenti.
Per quanto non espressamente previsto dalle presenti disposizioni, sono applicabili le ""Norme che regolano i conti correnti di corrispondenza e servizi connessi"" a suo tempo sottoscritte dalle parti, che formano parte integrante del presente accordo."
		</p>
	</div>

</div> 
