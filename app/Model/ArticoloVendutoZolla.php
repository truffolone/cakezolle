<?php
    
class ArticoloVendutoZolla extends AppModel 
{
	public $useDbConfig = 'zolla';
	
	public $useTable = 'articoli_ml_venduto';

	/**
	 * la tabella su zolla ha una chiave multipla (id_articolo, data_consegna, id_cliente), devo usare una query diretta
	 * (cake 2.x non è in grado di gestire chiavi multiple)
	 * 
	 * sovrascrivo il metodo di cake
	 */
	public function saveAll($data = null, $options = array()) {
		
		// prepara i dati mettendoli nel corretto ordine
		$bindings = array();
		$values = array();
		foreach($data as $d) {
			$values[] = '(?,?,?,?,?,?)';
			$bindings[] = isset($d['id_articolo']) ? $d['id_articolo'] : 0;
			$bindings[] = isset($d['data_consegna']) ? $d['data_consegna'] : '2000-01-01';
			$bindings[] = isset($d['id_cliente']) ? $d['id_cliente'] : 0;
			$bindings[] = isset($d['numero_porzioni']) ? $d['numero_porzioni'] : 0;
			$bindings[] = 1; // su zolla 'finalizzato' è non null
			$bindings[] = isset($d['agent']) ? $d['agent'] : 'AR';
		}
		
		$db = $this->getDataSource();
		if( $db->execute('INSERT INTO '.$this->useTable.'
			(ID_ARTICOLO, DATA_CONSEGNA, ID_CLIENTE, NUMERO_PORZIONI, FINALIZZATO, AGENT) 
			VALUES '.implode(',',$values).'
			ON DUPLICATE KEY UPDATE 
			NUMERO_PORZIONI = VALUES(NUMERO_PORZIONI)', array(), $bindings) ) {
			return true;
		}
		return false;
		
	}
	
}
  
