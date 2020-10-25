<?php
	$config['mangio_layout'] = 'standard'; // 'standard' o 'natale'

	$config['soglia_ml_in_euro'] = 5.0;

	/**
	 * 
	 */
	$config['articoli']['soglia_disponibilita'] = array(
		/**
		 * Modalità di visualizzazione 1:
		 * - se disponibilità > 0 l'articolo è acquistabile con l'apposito pulsante del carrello
		 * - se disponibiltà = 0 l'articolo non è più acquistabile (scompare il pulsante del carrello)
		 */
		1 => 0, // non viene usato
		/**
		 * Modalità di visualizzazione 2:
		 * - se disponibilità > 0 l'articolo è acquistabile con l'apposito pulsante del carrello
		 * - se disponibiltà = 0 l'articolo non è più acquistabile (scompare il pulsante del carrello)
		 * - se la disponibilità è al di sotto di una certa soglia (configurabile all'interno dello stesso file) 
		 *   viene indicata tramite apposita label la dicitura “disponibilità limitata”
		 * 
		 */
		2 => 10,
		/**
		 * Modalità di visualizzazione 3:
		 * - se disponibilità > 0 l'articolo è acquistabile con l'apposito pulsante del carrello
		 * - se disponibiltà = 0 l'articolo non è più acquistabile (scompare il pulsante del carrello)
		 * - se la disponibilità è al di sotto di una certa soglia (configurabile all'interno dello stesso file) viene indicata
		 *   tramite apposita label la dicitura “n° articoli rimanenti” con l'indicazione del numero di articoli rimanenti
		 */
		3 => 15
	);
	/**
	 * 
	 */
	$config['articoli']['tipo_visualizzazione_disponibilita'] = 2; 
	/**
	 * 
	 */
	$config['articoli']['nascondi_esauriti'] = false; // true o false 
	/**
	 * 
	 */
	$config['aggiorna_venduto']['auth_code'] = '46yow3247';
	
	/**
	*  'normal' : visualizzazione normale
    *  'quick':  visualizzazione con quick list
	*/
	$config['shopping_type'] = array(
        'mlv' => 'normal',
        'mlp' => 'quick'
    );
