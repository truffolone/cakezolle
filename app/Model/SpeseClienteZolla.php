<?php
    
class SpeseClienteZolla extends AppModel 
{
	public $useDbConfig = 'zolla';
	
	public $useTable = 'spese_cliente';

	public $primaryKey = 'ID';
	
	/**
	 * 
	 */
	public function checkSpeseNonAncoraInLavorazione() {
		return; // niente da fare (il controllo era per la modifica di spese e af)
		/*$Cliente = ClassRegistry::init('Cliente');
		//concordato con Andrea Diaco: verifico semplicemente che non ci siano record moltiplicati con data >= adesso
		$num = $this->find('count', array('conditions' => array(
			'SpeseClienteZolla.ID_CLIENTE' => $Cliente->getClienteId(),
			'SpeseClienteZolla.DATA >=' => date('Y-m-d')
		)));
		if($num > 0) {
			throw new Exception( __("La spesa di alcune settimane non può essere modificata in quanto già in lavorazione. Per modificare la spesa contattare l'Assistenza Clienti") );						
		}*/
	}
	
}
  
