<?php

  
class AcquistoMercatoLiberoZolla extends AppModel 
{
	public $useDbConfig = 'zolla';
	
	public $useTable = 'ml_cliente';

	public $primaryKey = 'ID';

	public $actsAs = array('Containable');

	public $belongsTo = array(
	);
	
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			// in molti casi prezzi e quantità hanno la virgola al posto del punto. Converti
			if( isset($results[$key]['AcquistoMercatoLiberoZolla']['QUANTITA']) ) {
				$results[$key]['AcquistoMercatoLiberoZolla']['QUANTITA'] = str_replace(',', '.', $results[$key]['AcquistoMercatoLiberoZolla']['QUANTITA']);
			}
			if( isset($results[$key]['AcquistoMercatoLiberoZolla']['PREZZO']) ) {
				$results[$key]['AcquistoMercatoLiberoZolla']['PREZZO'] = str_replace(',', '.', $results[$key]['AcquistoMercatoLiberoZolla']['PREZZO']);
			}
		}
		return $results;
	}
} 
