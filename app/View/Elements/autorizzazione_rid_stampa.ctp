<style>
td {
	vertical-align:top;
}
</style>

<h3 class="titolo-profilo2">RID - Autorizzazione permanente di addebito in C/C</h3>
		
<table style="width:100%">
	<tr>
	<!--azienda creditrice-->
	<td>
				
		<span>Azienda Creditrice</span>
		<hr/>
		<span><b>Le Zolle srl</b></span>
		
		<br/>
		<br/>
		<br/>
				
		<span>Coordinate dell'Azienda Creditrice</span>
		<hr/>
		<table style="width:100%">
			<tr>
				<td>
					<div>Cod. Azienda SIA</div>
					<div><b>AWPQS</b></div>
				</td>
			
				<td>
					<div>(*)</div>
					<div><b>4</b></div>
				</td>
				
				<td>
					<div>Codice cliente</div>
					<div><b><?php echo $rid['Cliente']['id'];?></b></div>
				</td>
			
			</tr>
		</table>
				
	</td>
			
	<!--banca del debitore-->
	<td>
			
		<span>Banca del Debitore</span>
		<hr/>
		<label>Banca</label>
		<div><?php echo $rid['AutorizzazioneRid']['banca_debitore'];?></div>
		<label>Agenzia</label>
		<div><?php echo $rid['AutorizzazioneRid']['agenzia_debitore'];?></div>
		
	</td>
	</tr>		
</table>

<br/><br/>

<!--coordinate bancarie del conto da addebitare-->
<table style="width:100%">
	<tr>
		<td colspan="6">
			<span>Coordinate bancarie del conto da addebitare</span>
			<hr/>
		</td>
	</tr>
	
	<tr>
		<td>
			<label>Cod Paese</label>
			<div>IT</div>
		</td>
		
		<td>
			<label>Check digit</label>
			<div><?php echo $rid['AutorizzazioneRid']['check_digit'];?></div>
		</td>
		
		<td>
			<br/><label>CIN</label>
			<div><?php echo $rid['AutorizzazioneRid']['cin'];?></div>
		</td>
		
		<td>
			<br/><label>ABI</label>
			<div><?php echo $rid['AutorizzazioneRid']['abi'];?></div>
		</td>
		
		<td>
			<br/><label>CAB</label>
			<div><?php echo $rid['AutorizzazioneRid']['cab'];?></div>
		</td>
		
		<td>
			<br/><label>Conto Corrente</label>
			<div><?php echo $rid['AutorizzazioneRid']['conto_corrente'];?></div>
		</td>
	</tr>
</table>

<br/><br/>

<table style="width:100%">
	<tr>
		<td>
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
		</td>
		
		<td>
			<span>Intestatario del Conto</span>
			<hr/>
			<br/><label>Anagrafica intestatario</label>
			<div><?php echo $rid['AutorizzazioneRid']['anagrafica_intestatario'];?></div>
			<br/><label>CF intestatario</label>
			<div><?php echo $rid['AutorizzazioneRid']['codice_fiscale_intestatario'];?></div>
			<br/><label>PIVA intestatario</label>
			<div><?php echo $rid['AutorizzazioneRid']['partita_iva_intestatario'];?></div>
		</td>
	</tr>

</div>

<br/><br/>

<div class="row">

	<div class="well col-md-12">
	
		<div style="text-align:center">
			<b>ADESIONE</b>
		</div>
		<br/>
		<br/>
		<p>
			Il sottoscritto autorizza la banca a margine ad addebitare sul c/c indicato, nella data di scadenza dell'obbligazione o data prorogata d'iniziativa del creditore (ferma restando la valuta originaria concordata) tutti gli ordini di incasso elettronici inviati dall'azienda e contrassegnati con le coordinate dell'azienda creditrice su riportate (o aggiornate di iniziativa dall'azienda) a condizione che vi siano disponibilità sufficienti e senza necessità per la banca di inviare la relativa contabile di addebito.
		</p>
			
		<div id="opzione-addebito" style="text-align:center">
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
