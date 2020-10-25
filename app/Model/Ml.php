<?php

App::uses('CakeSession', 'Model/Datasource');
  
class Ml extends AppModel 
{
	public $useTable = 'mercato_libero';

	public $actsAs = array('Containable');
	
	// usata in consegne controller per confronti vari
	public function getRecordMap($records) {
		$recordMap = [];
		foreach($records as $r) {
			$r = $r['Ml'];
			$key = "{$r['record_type']}-{$r['ID_ARTICOLO']}-{$r['DATA']}";
			$recordMap[$key] = $r;
		}
		return $recordMap;
	}
	
	/**
	 * semplicemente sono tutti i record con ml_id = null, non finalizzati e attivi raggruppati per:
	 * - data
	 * - id_articolo
	 * - tipo
	 * con somma della quantita' != 0
	 */
	public function getOperazioniDaConfermare() {
		$Cliente = ClassRegistry::init('Cliente');
		$db = $this->getDataSource();
		$res = $this->find('all', [
			'fields' => [
				'record_type', 
				'SUM(ml_id) AS ID',
				'ID_CLIENTE',
				'ID_SCATOLA',
				'ID_ARTICOLO',
				'DATA',
				'PRODOTTO',
				'AZIENDA',
				'TECNICA',
				'CONFEZIONE',
				'UDM',
				'ROUND(SUM(QUANTITA), 3) AS QUANTITA',
				'ROUND(SUM(PREZZO), 2) AS PREZZO',
				'PREZZO_VENDITA',
				'IVA',
				'AGENTE',
				'SUM(qty) AS qty'
			],
			'conditions' => [
				'ID_CLIENTE' => $Cliente->getClienteId(),
				'ml_id' => null,
				'is_finalized' => 0,
				'is_active' => 1,
				'record_type' => ['MLV', 'MLP'] // importante! Senza a livello di ml non ci sarebbero problemi (risalvo record non modifiati) ma salverei erronemante del venduto! 
			],
			'group' => ['DATA', 'ID_ARTICOLO', 'record_type'],
			'having' => [
				'SUM(qty) <> 0',
				'SUM(qty) IS NOT NULL'
			]
		]);
		foreach($res as $key => $value) {
			foreach(['ID', 'QUANTITA', 'PREZZO', 'qty'] as $key2) {
				$res[$key]['Ml'][$key2] = $res[$key][0][$key2];
			}
		}
		// l'having non sembra funzionare a dovere ... rimuovo (anche) via codice i record non da confermare
		foreach($res as $key => $value) {
			if( $res[$key]['Ml']['qty'] == null || $res[$key]['Ml']['qty'] == 0 ) {
				unset($res[$key]);
			}
		}
		$res = array_values($res);
		return $res;
	}
	
	/**
	 * restituisce i record da scrivere su zolla.
	 * I record da finalizzare sono tutti i record attivi (sia generati su area riservata, sia proveniente da zolla,
	 * quindi indipendentemente dal valore di ml_id)
	 * Per semplicita' non mi preoccupo se sovrascrivo dei record senza modifiche (sia perche' non succede nulla sia
	 * perche' invoco solo se ci sono modificare da fare)
	 */
	public function getRecordAttivi($onlyMlvAndMlp=false) {
		
		$Cliente = ClassRegistry::init('Cliente');
		$db = $this->getDataSource();
		// NOTA il barbatrucco su ml_id: in questo modo sono sicuro, indipendentemente da quale record viene restituito
		// dal group by, di avere il corretto valore dell'id se sto modificando un record gia' presente su zolla (i record
		// generati sull'area riservata non hanno ml_id)
		
		$conditions = [
			'ID_CLIENTE' => $Cliente->getClienteId(),
			'is_finalized' => 0,
			'is_active' => 1,
		];
		if($onlyMlvAndMlp) { // sto invocando ad es. in fase di finalizzazione (devo escludere tutto cio' che non e' MLV o MLP
			$conditions['record_type'] = ['MLV', 'MLP'];
		}
		// altrimenti sto invocando il metodo per leggere tutti i record da visualizzare (quindi voglio vedere anche le integrazioni)
		
		$res = $this->find('all', [
			'fields' => [
				'record_type', 
				'SUM(ml_id) AS ID',
				'ID_CLIENTE',
				'ID_SCATOLA',
				'ID_ARTICOLO',
				'DATA',
				'PRODOTTO',
				'AZIENDA',
				'TECNICA',
				'CONFEZIONE',
				'UDM',
				'ROUND(SUM(QUANTITA), 3) AS QUANTITA',
				'ROUND(SUM(PREZZO), 2) AS PREZZO',
				'PREZZO_VENDITA',
				'IVA',
				'AGENTE',
				'SUM(qty) AS qty'
			],
			'conditions' => $conditions,
			'group' => ['DATA', 'ID_ARTICOLO', 'record_type'],
		]);
		foreach($res as $key => $value) {
			foreach(['ID', 'QUANTITA', 'PREZZO', 'qty'] as $key2) {
				$res[$key]['Ml'][$key2] = $res[$key][0][$key2];
			}
		}
		return $res;
	}

	/**
	 * prepara la tabella locale di mercato libero per una nuova "sessione" di acquisti
	 */
	public function syncWithZolla() {
		$Cliente = ClassRegistry::init('Cliente');
		$MlZolla = ClassRegistry::init('AcquistoMercatoLiberoZolla');
		$Articolo = ClassRegistry::init('Articolo');
		//$Articolo = ClassRegistry::init('Articolo');
		$db1 = $this->getDataSource();
		$db1->begin();
		$transactionOk = true;
		
		try {
			// ottieni tutti i record futuri
			$records = $MlZolla->find('all', [
				'conditions' => [ //associo tutti i prossimi acquisti (quelli precedenti non mi interessano!) perchè eventuali modifiche sono relative a tutte le spese future
					'AcquistoMercatoLiberoZolla.ID_CLIENTE' => $Cliente->getClienteId(),
					'AcquistoMercatoLiberoZolla.DATA >=' => date('Y-m-d'),
				],
				'order' => 'AcquistoMercatoLiberoZolla.DATA',
			]);
			// disattiva i record passati
			$transactionOk = $transactionOk && $this->updateAll([
				'Ml.is_active' => 0
			], [
				'Ml.ID_CLIENTE' => $Cliente->getClienteId()
			]);
			// salva i nuovi record (se ce ne sono)
			if(!empty($records)) {
				// piu' semplice avere una mappa degli articoli che non fare 1000 join ...
				$articoliMap = $Articolo->getMap(array_map(function($r){
					return $r['AcquistoMercatoLiberoZolla']['ID_ARTICOLO'];
				}, $records));
				$recordsToSave = [];
				foreach($records as $r) {
					$r = $r['AcquistoMercatoLiberoZolla'];
					if(!isset($articoliMap[$r['ID_ARTICOLO']])) {
						ClassRegistry::init('LogEntry')->logError("", "Articolo {$r['ID_ARTICOLO']} non trovato");
						throw new InternalErrorException("Articolo {$r['ID_ARTICOLO']} non trovato");
					}
					
					$articolo = $articoliMap[$r['ID_ARTICOLO']];
					
					$qty = 0; // se non posso calcolare la quantità del carrello!!!
					if(!empty($articolo['Articolo']['quantita_minima'])) {
						$qty = intval((float)$r['QUANTITA']/$articolo['Articolo']['quantita_minima']);
					}
					
					$id = $r['ID'];
					unset($r['ID']);
					
					$recordsToSave[] = $r + [
						'is_finalized' => 0,
						'is_active' => 1,
						'record_type' => $this->getRecordType($r),
						'ml_id' => $id,
						'qty' => $qty,
					];
				}
				$transactionOk = $transactionOk && $this->saveMany($recordsToSave);
				
			}
		}
		catch(Exception $e) {
			$db1->rollback();
			ClassRegistry::init('LogEntry')->logError("", $e->getMessage());
			throw $e;
		}

		if($transactionOk) {
			$db1->commit();
		}
		else {
			$db1->rollback();
			throw new InternalErrorException("Si e' verificato un errore durante la lettura del ML da zolla");
		}
	}
	
	/**
	 * 
	 */
	private function getRecordType($zollaRecord) {
		if(empty($zollaRecord['AGENTE'])) {
			return "ZOLLA";
		}
		else {
			if($zollaRecord['AGENTE'] == "AR") {
				return "MLV";
			}
			elseif($zollaRecord['AGENTE'] == 'MLP') {
				return "MLP";
			}
			return $zollaRecord['AGENTE']; // uso come tipo l'agent (sconosciuto) originale
		}
	}
} 
