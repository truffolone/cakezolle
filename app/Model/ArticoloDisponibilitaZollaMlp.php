<?php
    
class ArticoloDisponibilitaZollaMlp extends AppModel 
{
	public $useDbConfig = 'zolla';
	
	public $useTable = 'articoli_mlp_disponibilita';

	/**
	 * la tabella su zolla ha una chiave multipla (id_articolo, data_consegna), devo usare una query diretta
	 * (cake 2.x non è in grado di gestire chiavi multiple)
	 */
	public function getNewOrUpdated($lastUpdateTime) {
		
		if(empty($lastUpdateTime)) $lastUpdateTime = date('Y-m-d');
		
		$db = $this->getDataSource();
		// su zolla viene usato un solo campo (modified) che funge sia da created sia da modified
		$res = $db->fetchAll("SELECT * FROM ".$this->useTable." WHERE modified > ?", array($lastUpdateTime));
		
		return $res;
		
	}
	
}
  
