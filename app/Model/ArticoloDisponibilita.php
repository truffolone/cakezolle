<?php

class ArticoloDisponibilita extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'articoli_disponibilita';
	
	/**
	 * funzione usata nel processo di sync disponibilità
	 */
	public function getLastUpdateTime() {
	
		$db = $this->getDataSource();
		// su zolla viene usato un solo campo (modified) che funge sia da created sia da modified
		$res = $db->fetchAll("SELECT modified FROM ".$this->useTable." ORDER BY modified DESC LIMIT 1");
		if(empty($res)) return null;
		if(empty($res[0][$this->useTable])) return null;
		
		return $res[0][$this->useTable]['modified'];
	
	}
	
	
}

 
