<?php
    
App::uses('CakeSession', 'Model/Datasource');    
    
class SpesaZolla extends AppModel 
{
	public $useDbConfig = 'zolla';
	
	public $useTable = 'clienti_tipo_spesa';

    public $primaryKey = 'ID_CLIENTE_TIPO_SPESA';

	public $actsAs = array('Containable');

	public $belongsTo = array(
		'ClienteZolla' => array(
			'className' => 'ClienteZolla',
			'foreignKey' => 'ID_CLIENTE'
		),
		'TipoSpesaZolla' => array(
			'className' => 'TipoSpesaZolla',
			'foreignKey' => 'TIPO_SPESA'
		),
		'IndirizzoZolla' => array(
			'className' => 'IndirizzoZolla',
			'foreignKey' => 'ID_CLIENTE_INDIRIZZO'
		)
	);
	
	
	public function getProssime($reset=false) {
		$Cliente = ClassRegistry::init('Cliente');
		if($reset) {
			CakeSession::write('prossime_spese', null);
		}
		$prossime_spese = CakeSession::read('prossime_spese');
		if($prossime_spese == null) {
			$mondayThisWeek = date('Y-m-d', strtotime('Monday this week'));
			$prossime_spese = $this->find('all', [
				'recursive' => -1,
				'conditions' => [
					'ID_CLIENTE' => $Cliente->getClienteId(),
					//associo tutte le prossime spese e quella di questa settimana (quelle precedenti non mi interessano!) perchè eventuali modifiche sono relative a tutte le spese future
					//DATA_INIZIO: SEMPRE lunedi della prima settimana di consegna
					//DATA_FINE: SEMPRE venerdi dell'ultima settimana di consegna
					//se spesa annullata : DATA_FINE = DATA_INIZIO 
					//NOTA: NON inserisco come condizione DATA_FINE > DATA_INIZIO perchè DEVO ottenere anche le consegne annullate in modo da sapere quali
					//sono le settimane sospese che possono essere riattivate
					'OR' => [
						//spese attualmente attive (indipendentemente dalla loro data di inizio)
						[
							'SpesaZolla.DATA_FINE' => null
						],
						[
							'SpesaZolla.DATA_FINE' => '' //per gestire qualsiasi caso (ce ne sono!)
						],
						//spese chiuse relative a consegne future
						[
							'SpesaZolla.DATA_FINE >=' =>  $mondayThisWeek, //uso >= e non solo > per sapere se settimana corrente è sospesa
						]
					 ]
				],
				'order' => 'SpesaZolla.DATA_INIZIO'
			]);
		}
		CakeSession::write('prossime_spese', $prossime_spese);
		return $prossime_spese;
	}
} 
