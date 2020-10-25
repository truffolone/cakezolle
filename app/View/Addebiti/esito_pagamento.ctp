<?php
	
	$this->assign('title', 'CONTRATTO CARTA DI CREDITO');
	
?>

	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-profilo">
				<?php echo ucfirst(strtolower(__('Esito pagamento di attivazione')));?>
			</h2>
		</div>
	</div>


<?php if(empty($res)) :?>

	<div class="alert alert-danger">
		<p>Il pagamento non è stato eseguito o si riferisce ad una transazione non presente in archivio.</p>
		<p>
			Si prega di contattare l'assistenza clienti all'indirizzo
			
			<p><a href="mailto:<?php echo __("mangio@zolle.it");?>"><i class="fa fa-envelope"></i> <?php echo __("mangio@zolle.it");?></a></p>
				
			<p><?php echo __("o contattarci al numero di telefono");?></p>
			
			<p><a href="tel:<?php echo __("+390692917616");?>"><i class="fa fa-phone-square"></i> <?php echo __("06/92917616");?></a></p>
		
			<p>
				<?php if(!empty($res['response_details'])):?>
				Dettagli pagamento<br/>
				<?php echo $res['response_details'];?>
				<?php endif;?>
			</p>
		</p>
	</div>

<?php else:?>

	<?php if($res['esito'] == 'OK'):?>
	
		<div class="alert alert-success">Pagamento eseguito con successo !</div>
	
	<?php elseif($res['esito'] == 'KO'):?>

		<div class="alert alert-warning">
			Gentile cliente, la carta di credito che hai utilizzato non ha autorizzato l'addebito. Ti invitiamo a procedere nuovamente con l'operazione dopo aver contattato l'assistenza clienti della tua banca per risolvere il problema
		</div>
		
	<?php else:?>
	
		<div class="alert alert-danger">
			Pagamento non avvenuto. Contattare il servizio clienti per ulteriori dettagli
			<br/>
			<p>
				<?php if(!empty($res['response_details'])):?>
				Dettagli pagamento<br/>
				<?php echo $res['response_details'];?>
				<?php endif;?>
			</p>
		</div>

	<?php endif;?>
	
<?php endif;?>
