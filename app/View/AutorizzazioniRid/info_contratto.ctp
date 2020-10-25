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
		<div class="col-md-12 alert alert-info">
			Gentile Cliente, la procedura di registrazione dell'autorizzazione di addebito RID è stata completata correttamente
			<br><br>
			Ti invitiamo a controllare i dati inseriti nel modulo sottostante:
			<br>
			<ul>
			<li>- nel caso in cui i dati immessi siano inesatti puoi procedere nuovamente con l'inserimento di una nuova autorizzazione cliccando
			sul pulsante "modifica i dati immessi"</li>
			<li>- se i dati immessi sono corretti ti invitiamo a cliccare sul pulsante "visualizza formato stampa". Stampa il modulo, firmalo in calce ed invialo via mail a: <a href="mailto:mangio@zolle.it">mangio@zolle.it</a></li>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<?php echo $this->Html->link('modifica i dati immessi', array('action' => 'contratto', $rid['AutorizzazioneRid']['id_contratto_rid']), array('class' => 'btn btn-sm bkg-orange white'));?>
		</div>
		
		<div class="col-md-6 text-right">
			<?php echo $this->Html->link('visualizza formato stampa', array('action' => 'stampa', $rid['AutorizzazioneRid']['id_contratto_rid']), array('class' => 'btn btn-sm bkg-orange white', 'target' => '_blank'));?>
		</div>
	</div>

	<?php echo $this->element(
		'autorizzazione_rid',
		array(
			'rid' => $rid
		)
	);?>
