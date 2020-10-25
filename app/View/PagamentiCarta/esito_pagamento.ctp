<?php
	
	$this->assign('title', 'ATTIVAZIONE SERVIZIO PAGAMENTI RICORRENTI');
	
?>

<?php
	if($res == 'SCONOSCIUTO') {
		$message = 'Il pagamento si riferisce ad una transazione non presente in archivio. Contattare l\'assistenza clienti';
	}
	else {
		if($res['esito'] == 'OK')
		{
			$message = "Pagamento eseguito con successo!";	
		}
		else if($res['esito'] == 'KO')
		{
			$message = "Gentile cliente, la carta di credito che hai utilizzato non ha autorizzato l'addebito. Ti invitiamo a procedere nuovamente con l'operazione dopo aver contattato l'assistenza clienti della tua banca per risolvere il problema";
		}
		else
		{
			$message = "Pagamento non avvenuto. Contattare il servizio clienti per ulteriori dettagli<BR>Dettagli pagamento:<BR>".$res['response_details'];
		}
	}
?>

<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-profilo">
				<?php echo ucfirst(strtolower(__('Esito pagamento')));?>
			</h2>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<?php echo $message;?>
		</div>
	</div>
</div>
