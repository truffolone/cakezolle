<?php

App::uses('Mlv', 'Model');

class Mlp extends Mlv {

	/**
	 * 
	 */
	function getType() {
		return 'MLP';
	}
	
	/**
	 * 
	 */
	function getAgent() {
		return 'MLP';
	}
	
	/**
	 * 
	 */
	public function isTabVisible() {
		$Articolo = ClassRegistry::init('Articolo');
		$isMlpAperto = !empty( $this->getDateAcquisto() );
		$articoliMlpEffDisponibili = $Articolo->getArticoliEffDisponibili('MLP');
		return $isMlpAperto || !empty($articoliMlpEffDisponibili); // faccio vedere se aperto oppure se ci sono articoli con disponibilita' (il cliente puo' "sbirciare")
	}

	
}

 
