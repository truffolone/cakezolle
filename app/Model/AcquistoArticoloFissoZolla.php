<?php
 
App::uses('CakeSession', 'Model/Datasource'); 
 
class AcquistoArticoloFissoZolla extends AppModel 
{
	public $useDbConfig = 'zolla';
	
	public $useTable = 'clienti_articoli_spesa';

    public $primaryKey = 'ID_CLIENTE_ARTICOLO_SPESA';

	public $actsAs = array('Containable');

	public $belongsTo = array(
		'ArticoloZolla' => array(
			'className' => 'ArticoloZolla',
			'foreignKey' => 'ID_ARTICOLI' //!!! id_articoli non id_articolo!
		),
		'ClienteZolla' => array(
			'className' => 'ClienteZolla',
			'foreignKey' => 'ID_CLIENTE'
		)
	);
	
	public function afterFind($results, $primary = false) {
		foreach ($results as $key => $val) {
			// in molti casi le quantità hanno la virgola al posto del punto. Converti
			$results[$key]['AcquistoArticoloFissoZolla']['QUANTITA'] = str_replace(',', '.', $results[$key]['AcquistoArticoloFissoZolla']['QUANTITA']);
		}
		return $results;
	}
	
	public function getProssimi($reset=false) {
		$Cliente = ClassRegistry::init('Cliente');
		if($reset) {
			CakeSession::write('prossimi_af', null);
		}
		$prossimi_af = CakeSession::read('prossimi_af');
		if($prossimi_af == null) {
			$mondayThisWeek = date('Y-m-d', strtotime('Monday this week'));
			$prossimi_af = $this->find('all', [
				'recursive' => -1,
				'conditions' => array( 
					'ID_CLIENTE' => $Cliente->getClienteId(),
					'OR' => array(
						//spese attualmente attive (indipendentemente dalla loro data di inizio)
						array(
							'AcquistoArticoloFissoZolla.DATA_FINE' => null
						),
						array(
							'AcquistoArticoloFissoZolla.DATA_FINE' => '' //per gestire qualsiasi caso (ce ne sono!)
						),
						//spese chiuse relative a consegne future
						array(
							'AcquistoArticoloFissoZolla.DATA_FINE >=' => "date('Y-m-d', strtotime('Monday this week'))" //uso >= e non solo > per sapere se settimana corrente è sospesa
						)
					)
				),
				'order' => 'AcquistoArticoloFissoZolla.DATA_INIZIO'
			]);
		}
		CakeSession::write('prossimi_af', $prossimi_af);
		return $prossimi_af;
	}
}
