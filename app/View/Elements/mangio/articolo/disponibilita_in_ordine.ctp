<?php

	/**
	 * A DIFFERENZA DI QUANTO AVVIENE IN SCHEDE E BOX ARTICOLO, NEL RIEPILOGO ORDINE POTREBBE SUCCEDERE
	 * CHE L'UTENTE SIA RIMASTO MOLTO TEMPO SU QUELLA PAGINA E NEL MENTRE LA DISPONIBILITÀ SIA CAMBIATA PERCHÈ
	 * QUALCHE ALTRO CLIENTE HA FINALIZZATO IL SUO ORDINE.
	 * SE LA DISPONIBILITÀ È INFERIORE DELLA QUANTITÀ A CARRELLO (PUÒ SUCCEDERE SOLO NEL CASO CITATO, IN TUTTI GLI
	 * ALTRI CASI NON POSSO METTERE NEL CARRELLO PIÙ DELLA QUANTITÀ DISPONIBILE) DEVO VISUALIZZARE
	 * IL NUMERO EFFETTIVO DI ARTICOLI DISPONIBILI PER PERMETTERE AL CLIENTE DI AGGIORNARE LA QUANTITÀ IN MODO 
	 * CORRETTO
	 */
	 
	// leggi la modalità di visualizzazione globale
	$viewmode = Configure::read('articoli.tipo_visualizzazione_disponibilita');
	$soglie = Configure::read('articoli.soglia_disponibilita');
	
	// nel caso della disponibilità in ordine la visualizzazione (showAs) dipende dalla settimana che sto visualizzando
	// -> 'showAs' è specificato dal chiamante
	
	$disponibilita = '';
	switch($viewmode) {
		case 1:
			$disponibilita = ''; // questa modalità non prevede nessuna info
			break;
		case 2:
			if( isset($soglie[2]) && $soglie[2] > $articolo['Articolo'][$showAs]['porzioni_disponibili'] ) {
				$disponibilita = __('Disponibilità limitata');
			}
			break;
		case 3:
			if( isset($soglie[3]) && $soglie[3] > $articolo['Articolo'][$showAs]['porzioni_disponibili'] ) {
				if($articolo['Articolo'][$showAs]['porzioni_disponibili'] == 1) {
					$disponibilita = __('1 articolo rimanente');
				}
				else {
					$disponibilita = __('%s articoli rimanenti', array($articolo['Articolo'][$showAs]['porzioni_disponibili']));
				}
			}
			break;
	}
?>

<?php if( isset($recordML) && ($recordML['AGENTE'] != 'AR' && $recordML['AGENTE'] != 'MLP')):?>
	<?php // nulla da visualizzare, record da fattoria -->?>
<?php else:?>

	<?php if($articolo['Articolo'][$showAs]['porzioni_disponibili'] > 0):?>

		<div class="<?php echo $showAs == 'mlv' ? 'testoML' : 'testoMLP';?>"><?php echo $disponibilita;?></div>	

	<?php elseif($articolo['Articolo'][$showAs]['porzioni_disponibili'] == 0):?>

		<div><?php 
			// nel riepilogo ordine se ho comprato tutta la disponibilità semplicemente non visualizzo nulla, altrimenti
			// "non disponibile" qui genera confusione
			//echo __('Non disponibile');
		?></div>
		
	<?php else:?>

		<?php
			// un altro cliente nel mentre ha finalizzato il proprio ordine o la disponibilita' e' stata ridotta per qualche motivo
			// (o entrambi) visualizza la quantità effettivamente disponibile per consentire al cliente di correggere il suo ordine
			$effettiva_disp = $articolo['Articolo'][$showAs]['porzioni_eff_disponibili'];
		?>
		<?php if($effettiva_disp > 0):?>
			<div style="color:red">Solo <?php echo $effettiva_disp.' '.($effettiva_disp == 1 ? 'disponibile' : 'disponibili');?></div>
		<?php else:?>
			<div style="color:red"><?php echo __('Siamo spiacenti, questo articolo non è più disponibile');?></div>
		<?php endif;?>

	<?php endif;?>

<?php endif;?>
