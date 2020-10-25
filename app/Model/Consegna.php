<?php

App::uses('AppModel', 'Model', 'CakeSession');

class Consegna extends AppModel {

	public $useTable = false;
	
	/**
	 * usato per ottenere i dettagli delle spese in consegna
	 */
	private function getTipiSpeseDict($consegne) {
		$tipiSpeseIds = [];
		foreach($consegne as $consegna) {
			$tipiSpeseIds = array_merge($tipiSpeseIds, $consegna['spese_non_sli']);
		}
		$TipoSpesa = ClassRegistry::init('TipoSpesa');
		$tipiSpese = $TipoSpesa->find('all', [
			'conditions' => [
				'TIPO_SPESA' => array_unique($tipiSpeseIds)
			],
			'recursive' => -1
		]);
		$tipiSpeseDict = [];
		foreach($tipiSpese as $t) {
			$tipiSpeseDict[ $t['TipoSpesa']['TIPO_SPESA'] ] = $t;
		}
		return $tipiSpeseDict;
	}
	
	/**
	 * usato per ottenere i dettagli degli articoli in consegna
	 */
	private function getArticoliDict($af, $ml) {
		$articoli_ids = [];
		foreach($af as $data => $records) {
			$articoli_ids = array_merge($articoli_ids, array_map(function($a){
				return $a['ID_ARTICOLI'];
			}, $records));
		}
		$articoli_ids = array_merge($articoli_ids, array_map(function($r){
			return $r['Ml']['ID_ARTICOLO'];
		}, $ml));
		$Articolo = ClassRegistry::init('Articolo');
		return $Articolo->getMap(array_unique($articoli_ids), $doNotCheckAvailability=true);
	}
	
	/**
	 * verifica che gli acquisti in qualunque data siano conformi alle regole di zolle
	 */
	function checkValidataOperazioniDaConfermare() {
		$consegne = $this->getConsegneCompletePerNumSettimane(4); // 4 settimane solo per sicurezza che ci siano tutte le date necessarie ...
		// se in una consegna non ho spese non-sli devo avere almeno un certo importo di articoli di mercato libero
		// indipendentemente dall'eventuale importo di articoli fissi
		$soglia = str_replace(',', '.', Configure::read('soglia_ml_in_euro'));
		foreach($consegne as $data => $consegna) {
			if( empty($consegna['spese']) && 
				$consegna['totali']['subtotaleMl'] > 0 && // mandatory, devo fare il controllo solo se ho comprato qualcosa!
				$consegna['totali']['subtotaleMl'] < $soglia ) {
				ClassRegistry::init('LogEntry')->logWarning("THRESHOLD", __("La spesa in data %s ha solo prodotti di Mercato Libero. Per procedere con le modifice l'importo totale deve essere maggiore di ". number_format($soglia, 2, ',', '') ." Euro", array(date('d/m/Y', strtotime($data)))));
				throw new Exception(__("La spesa in data %s ha solo prodotti di Mercato Libero. Per procedere con le modifice l'importo totale deve essere maggiore di ". number_format($soglia, 2, ',', '') ." Euro", array(date('d/m/Y', strtotime($data)))));
			}
		}
	}
	
	/**
	 * 
	 */
	function getConsegneCompletePerNumSettimane($num) {
		$consegne = $this->getProssimePerNumSettimane($num);
		$articoliFissi = $this->getProssimiArticoliFissiPerNumSettimane($num);
		$Ml = ClassRegistry::init('Ml');
		$recordMlAttivi = $Ml->getRecordAttivi();
		$recordMlDaConfermare = $Ml->getOperazioniDaConfermare();
		
		$tipiSpeseDict = $this->getTipiSpeseDict($consegne);
		$articoliDict = $this->getArticoliDict($articoliFissi, array_merge($recordMlAttivi, $recordMlDaConfermare));

		// crea array associativo per le consegne
		$dateConsegne = [];
		foreach($consegne as $consegna) {
			
			// se ci sono piu' spese dello stesso tipo raggruppale
			$speseNonSli = [];
			foreach($consegna['spese_non_sli'] as $s) {
				if(!isset($speseNonSli[$s])) {
					$speseNonSli[$s] = [
						'tipo' => $s,
						'qty' => 0
					];
				}
				$speseNonSli[$s]['qty']++;
			}
			
			$dateConsegne[ $consegna['date'] ] = [
				'spese' => array_map(function($s) use ($tipiSpeseDict) {
					if( !isset($tipiSpeseDict[ $s['tipo'] ]) ) {
						ClassRegistry::init('LogEntry')->logError("", "Tipo spesa {$s['tipo']} sconosciuto");
						throw new InternalErrorException("Tipo spesa {$s['tipo']} sconosciuto");
					}
					return [
						'tipo' => $s['tipo'],
						'dettagli' => $tipiSpeseDict[$s['tipo']],
						'qty' => $s['qty']
					];
				}, array_values($speseNonSli)),
				'af' => isset($articoliFissi[ $consegna['date'] ]) ? 
					array_map(function($articoloFisso) use ($articoliDict) {
						if(!isset($articoliDict[ $articoloFisso['ID_ARTICOLI'] ])) {
							ClassRegistry::init('LogEntry')->logError("", "Articolo {$articoloFisso['ID_ARTICOLI']} sconosciuto o non disponibile");
							throw new InternalErrorException("Articolo {$articoloFisso['ID_ARTICOLI']} sconosciuto o non disponibile");
						}
						return [
							'record' => $articoloFisso,
							'articolo' => $articoliDict[ $articoloFisso['ID_ARTICOLI'] ]
						];
					}, $articoliFissi[ $consegna['date'] ]) : 
					[],
				'ml' => [
					'attivi' => [],
					'da_confermare' => []
				]
			];
		}
		
		// aggiungi ml nelle date corrette
		foreach($recordMlAttivi as $r) {
			$r = $r['Ml'];
			if(isset($dateConsegne[ $r['DATA'] ])) {
				if(!isset($articoliDict[ $r['ID_ARTICOLO'] ])) {
					ClassRegistry::init('LogEntry')->logError("", "Articolo {$r['ID_ARTICOLO']} sconosciuto o non disponibile");
					throw new InternalErrorException("Articolo {$r['ID_ARTICOLO']} sconosciuto o non disponibile");
				}
				$dateConsegne[ $r['DATA'] ]['ml']['attivi'][] = [
					'record' => $r,
					'articolo' => $articoliDict[ $r['ID_ARTICOLO'] ]
				];
				usort($dateConsegne[ $r['DATA'] ]['ml']['attivi'], function($a, $b){
					$nA = $a['articolo']['Articolo']['nomeCompleto'];
					$nB = $b['articolo']['Articolo']['nomeCompleto'];
					if ($nA == $nB) {
						return 0;
					}
					return ($nA < $nB) ? -1 : 1; 
				});
			}
			else {
				ClassRegistry::init('LogEntry')->logError("", "Nessuna consegna disponibile per {$r['DATA']}");
				throw new InternalErrorException("Nessuna consegna disponibile per {$r['DATA']}"); // non dovrebbe mai succedere ...
			}
		}
		foreach($recordMlDaConfermare as $r) {
			$r = $r['Ml'];
			if(isset($dateConsegne[ $r['DATA'] ])) {
				if(!isset($articoliDict[ $r['ID_ARTICOLO'] ])) {
					ClassRegistry::init('LogEntry')->logError("", "Articolo {$r['ID_ARTICOLO']} sconosciuto o non disponibile");
					throw new InternalErrorException("Articolo {$r['ID_ARTICOLO']} sconosciuto o non disponibile");
				}
				$dateConsegne[ $r['DATA'] ]['ml']['da_confermare'][] = [
					'record' => $r,
					'articolo' => $articoliDict[ $r['ID_ARTICOLO'] ]
				];
			}
			else {
				ClassRegistry::init('LogEntry')->logError("", "Nessuna consegna disponibile per {$r['DATA']}");
				throw new InternalErrorException("Nessuna consegna disponibile per {$r['DATA']}"); // non dovrebbe mai succedere ...
			}
		}
		
		// post-processing (logica di gennaio 2019): se in una data consegna ci sono solo spese S_LI (= nessuna spesa non sli)
		// eventuali articoli fissi non vanno presi in considerazione (scompaiono)
		foreach($dateConsegne as $data => $consegna) {
			if(empty($consegna['spese'])) {
				$dateConsegne[$data]['af'] = [];
			}
		}
		
		// calcoli i totali di ogni consegna
		foreach($dateConsegne as $data => $consegna) {
			
			$subtotaleSpese = 0.0;
			$subtotaleAf = 0.0;
			$subtotaleMl = 0.0;
			
			$costoSpese = array_map(function($spesa) {
				return $spesa['dettagli']['TipoSpesa']['PREZZO'];
			}, $consegna['spese']);
			foreach($costoSpese as $costoSpesa) {
				$subtotaleSpese += $costoSpesa;
			}
			
			$costoArticoliFissi = array_map(function($af) {
				if( $af['articolo']['Articolo']['quantita_minima'] > 0 ) { // ci sono articoli fissi per cui non posso calcolarlo
					$qta = intval((float)$af['record']['QUANTITA']/$af['articolo']['Articolo']['quantita_minima']);		
					return $qta * $af['articolo']['Articolo']['prezzo_porzione'];
				}
				return 0;
			}, $consegna['af']);
			foreach($costoArticoliFissi as $costoArticoloFisso) {
				$subtotaleAf += $costoArticoloFisso;
			}
			
			$costoMls = array_map(function($r){
				return $r['record']['PREZZO'];
			}, $consegna['ml']['attivi']);
			foreach($costoMls as $costoMl) {
				$subtotaleMl += $costoMl;
			}
			
			$dateConsegne[$data]['totali'] = [
				'subtotaleSpese' => $subtotaleSpese,
				'subtotaleAf' => $subtotaleAf,
				'subtotaleMl' => $subtotaleMl,
				'totale' => $subtotaleSpese + $subtotaleAf + $subtotaleMl
			];
		}
		
		return $dateConsegne;
		
	}
	
	/**
	 * 
	 */
	function getDataProssima() {
		$prossimeConsegne = $this->getProssimePerNumSettimane(4); // mi tengo largo per sicurezza;
		if(empty($prossimeConsegne)) return null;
		return $prossimeConsegne[0]['date'];
	}
	
	/**
	 * 
	 */
	function getProssimiArticoliFissiPerNumSettimane($num) {
		$Af = ClassRegistry::init('AcquistoArticoloFissoZolla');
		$Cliente = ClassRegistry::init('Cliente');
		$Indirizzo = ClassRegistry::init('Indirizzo');
		$prossimiAf = $Af->getProssimi();
		
		$from = time();
		$to = time() + $num*7*24*60*60;
		
		$dateToProssimiAf = [];
		
		$giornoConsegnaCliente = $Cliente->getGiornoConsegna();
		
		foreach($prossimiAf as $prossimoAf) {
			$prossimoAf = $prossimoAf['AcquistoArticoloFissoZolla'];
			$giornoConsegnaAf = $giornoConsegnaCliente;
			if(empty($giornoConsegnaAf)) {
				ClassRegistry::init('LogEntry')->logError("", "Nessun giorno di consegna disponibile per l'articolo fisso", [
					'articolo' => $prossimoAf
				]);
				throw new InternalErrorException("Nessun giorno di consegna disponibile per l'articolo fisso");	
			}
			$dataInizio = $prossimoAf['DATA_INIZIO'];
			$dataFine = empty($prossimoAf['DATA_FINE']) ? '2500-01-01' : $prossimoAf['DATA_FINE'];
			
			$fromAf = strtotime($dataInizio) > $from ? strtotime($dataInizio) : $from;
			$toAf = strtotime($dataFine) < $to ? strtotime($dataFine) : $to;
			 
			$dateConsegnaAf = $this->filterDatesByPeriodicita(
				$this->getDatesForWeekDayFromTo($giornoConsegnaAf, $fromAf, $toAf),
				$prossimoAf['PERIODICITA']
			);
			foreach($dateConsegnaAf as $dataConsegnaAf) {
				if(!isset($dateToProssimiAf[$dataConsegnaAf])) {
					$dateToProssimiAf[$dataConsegnaAf] = [];
				}
				$dateToProssimiAf[$dataConsegnaAf][] = $prossimoAf;
			}
		}
		return $dateToProssimiAf;
	}
	
	/**
	 * restituisce le consegne previste per le prossime n settimane (inclusa la settimana corrente)
	 * 
	 */
	function getProssimePerNumSettimane($num) {
		$SpesaZolla = ClassRegistry::init('SpesaZolla');
		
		$Cliente = ClassRegistry::init('Cliente');
		$Indirizzo = ClassRegistry::init('Indirizzo');
		$prossimeSpese = $SpesaZolla->getProssime();
		
		
		$from = time();
		$to = time() + $num*7*24*60*60;
		
		$dateToProssimeSpese = [];
		
		$giornoConsegnaCliente = $Cliente->getGiornoConsegna();
		foreach($prossimeSpese as $prossimaSpesa) {
			$prossimaSpesa = $prossimaSpesa['SpesaZolla'];
			$giornoConsegnaSpesa = empty($prossimaSpesa['GIORNO_CONSEGNA']) ? 
				$giornoConsegnaCliente : 
				$prossimaSpesa['GIORNO_CONSEGNA'];
			if(empty($giornoConsegnaSpesa)) {
				$indirizzo = $prossimaSpesa['ID_CLIENTE_INDIRIZZO'] ? $Indirizzo->findById($prossimaSpesa['ID_CLIENTE_INDIRIZZO']) : null;
				if(empty($indirizzo) || empty($indirizzo['Indirizzo']['GIORNO_CONSEGNA'])) {
					ClassRegistry::init('LogEntry')->logError("", "Nessun giorno di consegna disponibile per la spesa", [
						'spesa' => $prossimaSpesa
					]);
					throw new InternalErrorException("Nessun giorno di consegna disponibile per la spesa");
				}
				
			}
			$dataInizio = $prossimaSpesa['DATA_INIZIO'];
			$dataFine = empty($prossimaSpesa['DATA_FINE']) ? '2500-01-01' : $prossimaSpesa['DATA_FINE'];
			
			$fromSpesa = strtotime($dataInizio) > $from ? strtotime($dataInizio) : $from;
			$toSpesa = strtotime($dataFine) < $to ? strtotime($dataFine) : $to;
			 
			$dateConsegnaSpesa = $this->filterDatesByPeriodicita(
				$this->getDatesForWeekDayFromTo($giornoConsegnaSpesa, $fromSpesa, $toSpesa),
				$prossimaSpesa['PERIODICITA']
			);
			foreach($dateConsegnaSpesa as $dataConsegnaSpesa) {
				if(!isset($dateToProssimeSpese[$dataConsegnaSpesa])) {
					$dateToProssimeSpese[$dataConsegnaSpesa] = [];
				}
				$dateToProssimeSpese[$dataConsegnaSpesa][] = [
					'scatola_id' => $prossimaSpesa['ID_CLIENTE_TIPO_SPESA'],
					'tipo_spesa' => $prossimaSpesa['TIPO_SPESA']
				];
			}
		}
		
		$prossimeConsegne = [];
		foreach($dateToProssimeSpese as $date => $prossimeSpeseAtGivenDate) {
			$scatolaIdForMl = null;
			// ottieni la lista delle spese non S_LI in questa consegna
			$tipiSpese = [];
			foreach($prossimeSpeseAtGivenDate as $spesa) {
				if( strpos($spesa['tipo_spesa'], 'S_LI') !== 0 ) {
					$tipiSpese[] = $spesa['tipo_spesa'];
				}
			}
			// determina la scatola
			if(!empty($prossimeSpeseAtGivenDate)) {
				if(sizeof($prossimeSpeseAtGivenDate) == 1) {
					$scatolaIdForMl = $prossimeSpeseAtGivenDate[0]['scatola_id'];
				}
				else {
					// c'è più di una scatola, significa che possono esserci:
					// - S_LI
					// - S_LI* (di sistema da non usare)
					// - NON S_LI
					// Scegli dando priorità a NON S_LI, altrimenti scegli S_LI
					foreach($prossimeSpeseAtGivenDate as $spesa) {
						if( strpos($spesa['tipo_spesa'], 'S_LI') !== 0 ) {
							$scatolaIdForMl = $spesa['scatola_id'];
							break;
						}
					}
					if(empty($scatolaIdForMl)) {
						// scegli S_LI
						foreach($prossimeSpeseAtGivenDate as $spesa) {
							if($spesa['tipo_spesa'] == 'S_LI') {
								$scatolaIdForMl = $spesa['scatola_id'];
								break;
							}
						}
					}
				}
			}
			$prossimeConsegne[] = [
				'date' => $date,
				'scatola_id_for_ml' => $scatolaIdForMl, // puo' anche essere null in questa fase (es. per i clienti senza operativita')
				'spese_non_sli' => $tipiSpese
			];
		}
		
		usort($prossimeConsegne, function($a, $b){
			if($a['date'] == $b['date']) return 0;
			return $a['date'] < $b['date'] ? -1 : 1;
		});
		
		return $prossimeConsegne;
	}
	
	/**
	 * 
	 */
	private function filterDatesByPeriodicita($dates, $periodicita) {
		$periodicita = trim(strtolower($periodicita));
		$filteredDates = [];
		foreach($dates as $date) {
			$isValid = false;
			if($periodicita == '12345') {
				$isValid = true;
			}
			elseif($periodicita == 'pa' || $periodicita == '24') {
				$isValid = date('W', strtotime($date)) % 2 == 0;
			}
			elseif($periodicita == 'di' || $periodicita == '135') {
				$isValid = date('W', strtotime($date)) % 2 == 1;
			}
			else { // specifiche settimane impostate
				$settimane = explode(",", $periodicita);
				$isValid = in_array( date('W', strtotime($date)), $settimane );
			}
			
			if($isValid) {
				$filteredDates[] = $date;
			}
		}
		return $filteredDates;
	}
	
	/**
	 * 
	 */
	private function getDatesForWeekDayFromTo($dayName, $fromTimestamp, $toTimestamp) {
		$n = $this->weekDayNameToWeedDayNum($dayName);
		$dates = [];
		$fromN = date('N', $fromTimestamp);
		$currTimestamp = $fromTimestamp;
		if($n != $fromN) {
			$currTimestamp += 24*60*60*( $n > $fromN ? $n-$fromN : 7+$n-$fromN );
		}
		// altimenti fromTimestamp e' gia' data corretta
		while($currTimestamp <= $toTimestamp) {
			$dates[] = date('Y-m-d', $currTimestamp);
			$currTimestamp += 7*24*60*60;
		}
		return $dates;
	}
	
	/**
	 * 
	 */
	private function weekDayNameToWeedDayNum($dayName) {
		$days['lun'] = 1;
		$days['mar'] = 2;
		$days['mer'] = 3;
		$days['gio'] = 4;
		$days['ven'] = 5;
		$days['sab'] = 6;
		$days['dom'] = 7;

		$dayName = strtolower($dayName);
		if( !isset($days[$dayName]) ) {
			ClassRegistry::init('LogEntry')->logError("", "Giorno consegna $dayName sconosciuto");
			throw new InternalErrorException("Giorno consegna $dayName sconosciuto");
		}
		return $days[$dayName];
	}
	
}

 
