<?php

	// leggi la modalità di visualizzazione globale
	$viewmode = Configure::read('articoli.tipo_visualizzazione_disponibilita');
	$soglie = Configure::read('articoli.soglia_disponibilita');
	$showAs = strtolower($articolo['Articolo']['showAs']);
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

<?php if($articolo['Articolo'][$showAs]['porzioni_disponibili'] > 0):?>

	<div class="<?php echo $showAs == 'mlp' ? 'testoMLP' : 'testoML';?>"><?php echo $disponibilita;?></div>	

<?php else:?>

	<div><?php echo __('Non disponibile');?></div>

<?php endif;?>
