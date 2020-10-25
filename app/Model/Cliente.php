<?php

App::uses('CakeSession', 'Model/Datasource');

class Cliente extends AppModel {

	public $actsAs = array('Containable');

	/*public $virtualFields = array(
		'displayName' => 'CONCAT(Cliente.COGNOME, " ", Cliente.NOME)'
	);*/
	
	// uso il modello con alias differenti, devo creare i virtual fields via costruttore
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['displayName'] = sprintf(
			'CONCAT(%s.COGNOME, " ", %s.NOME)', $this->alias, $this->alias
		);
	}
	
	/**
	 * 
	 */
	public function getClienteId() {
		$user = CakeSession::read('Auth.User');
		if($user == null) {
			throw new UnauthorizedException();
		}
		if($user['cliente_id'] == null) {
			throw new UnauthorizedException();
		}
		return $user['cliente_id'];
	}
	
	/**
	 * 
	 */
	public function getGiornoConsegna() {
		$giornoConsegna = CakeSession::read('clienteGiornoConsegna');
		if(empty($giornoConsegna)) {
			$cliente_id = $this->getClienteId();
			$Indirizzo = ClassRegistry::init('Indirizzo');
			$indirizzoPrincipale = $Indirizzo->find('first', [
				'conditions' => [
					'Indirizzo.PRINCIPALE' => 'SI',
					'Indirizzo.cliente_id' => $cliente_id
				]
			]);
			if(empty($indirizzoPrincipale)) {
				throw new InternalErrorException("Nessun indirizzo principale trovato");
			}
			$giornoConsegna = $indirizzoPrincipale['Indirizzo']['GIORNO_CONSEGNA'];
			CakeSession::write('clienteGiornoConsegna', $giornoConsegna);
		}
		return $giornoConsegna;
	}

	/**
	 * 
	 */
	public function getMetodiPagamentoDaNotificare($report=false) {
		$db = $this->getDataSource();
		// leggi tutti i clienti che devono essere notificati:
		// - si notifica solo l'ultimo metodo di pagamento creato (se carta o sepa) ma non attivo (anche se ce n'è più di uno non attivo
		//   si notifica sempre e solo l'ultimo indipendentemente se carta o sepa)
		// - si notifica indipendentemente da che ci sia un metodo di pagamento attivo oppure no
		// - se non ancora stato notificato in precedenza si notifica se sono passate almeno x ore dalla creazione
		// - se già stato notificato in precedenza si notifica se sono passate almeno y ore dal precedente sollecito, per un max di n solleciti 
		
		// ottieni l'ultimo metodo carta di ogni cliente
		$resCarte = $db->fetchAll("SELECT c.id, m.id, m.created, m.id_contratto, m.signed, m.data_disattivazione, m.data_ultimo_sollecito_attivazione, m.num_solleciti_inviati
			FROM clienti AS c
			INNER JOIN carte_di_credito AS m
			ON m.id = (
				SELECT id 
				FROM carte_di_credito AS m
				WHERE m.cliente_id = c.id 
				AND m.created > '2019-12-01'
				ORDER BY m.created DESC
				LIMIT 1
			)
			ORDER BY c.id
		");

		// ottieni l'ultimo metodo sepa di ogni cliente
		$resSepa = $db->fetchAll("SELECT c.id, m.id, m.created, m.id_contratto_rid, m.rid_activated, m.data_disattivazione, m.data_ultimo_sollecito_attivazione, m.num_solleciti_inviati
			FROM clienti AS c
			INNER JOIN autorizzazioni_rid AS m
			ON m.id = (
				SELECT id 
				FROM autorizzazioni_rid AS m
				WHERE m.cliente_id = c.id 
				AND m.created > '2019-12-01'
				ORDER BY m.created DESC
				LIMIT 1
			)
			ORDER BY c.id
		");

		// ottieni l'ultimo metodo bonifico di ogni cliente
		$resBonifico = $db->fetchAll("SELECT c.id, m.id, m.created
			FROM clienti AS c
			INNER JOIN bonifici AS m
			ON m.id = (
				SELECT id 
				FROM bonifici AS m
				WHERE m.cliente_id = c.id
				AND m.created > '2019-12-01' 
				ORDER BY m.created DESC
				LIMIT 1
			)
			ORDER BY c.id
		");

		// ottieni l'ultimo metodo contanti di ogni cliente
		$resContanti = $db->fetchAll("SELECT c.id, m.id, m.created
			FROM clienti AS c
			INNER JOIN contanti AS m
			ON m.id = (
				SELECT id 
				FROM contanti AS m
				WHERE m.cliente_id = c.id 
				AND m.created > '2019-12-01'
				ORDER BY m.created DESC
				LIMIT 1
			)
			ORDER BY c.id
		");

		// ottieni l'ultimo metodo legale di ogni cliente
		$resLegale = $db->fetchAll("SELECT c.id, m.id, m.created
			FROM clienti AS c
			INNER JOIN procedure_legali AS m
			ON m.id = (
				SELECT id 
				FROM procedure_legali AS m
				WHERE m.cliente_id = c.id 
				AND m.created > '2019-12-01'
				ORDER BY m.created DESC
				LIMIT 1
			)
			ORDER BY c.id
		");

		$res = [
			'carta' => $resCarte,
			'sepa' => $resSepa,
			'bonifico' => $resBonifico,
			'contanti' => $resContanti,
			'legale' => $resLegale
		];
		// determina per ogni cliente l'ultimo metodo di pagamento creato
		$metodiPagamento = [];
		foreach($res as $tipo => $rr) {
			foreach($rr as $r) {
				$cliente_id = $r['c']['id'];
				if( !isset($metodiPagamento[$cliente_id]) ) {
					$metodiPagamento[$cliente_id] = [
						'tipo' => $tipo,
						'created' => $r['m']['created'],
						'metodo' => $r['m']
					];
				}
				else { // sostituisci se più recente del corrente
					if( $r['m']['created'] > $metodiPagamento[$cliente_id]['created'] ) {
						$metodiPagamento[$cliente_id] = [
							'tipo' => $tipo,
							'created' => $r['m']['created'],
							'metodo' => $r['m']
						];
					}
				}
			}
		}
		
		// ora metodiPagamento contiene l'ultimo pagamento creato per ogni cliente.
		// Se Carta o Sepa e non ancora attivo (e non disabilitato) rappresenta:
		// - la base dati per il report
		// - la base pagamenti potenzialmente da notificare (se non notificato di recente)
		$metodiPagamentoNotificabili = [];
		foreach($metodiPagamento as $cliente_id => $m) {
			if( $m['tipo'] == 'carta' ) {
				if($m['metodo']['signed'] == null && $m['metodo']['data_disattivazione'] == null) {
					$metodiPagamentoNotificabili[$cliente_id] = $m;
				}
			}
			else if( $m['tipo'] == 'sepa' ) {
				if($m['metodo']['rid_activated'] == null && $m['metodo']['data_disattivazione'] == null) {
					$metodiPagamentoNotificabili[$cliente_id] = $m;
				}
			}
			// altrimenti l'ultimo metodo non è ne carta ne sepa, non mi interessa
		}
		
		if($report) {
			ksort($metodiPagamentoNotificabili); // ordina per id cliente (per il report)
			return $metodiPagamentoNotificabili;
		}

		// solo a questo punto verifico se effettivamente il metodo di pagamento è da notificare oppure no:
		// NON posso farlo dentro alla query altrimenti se un cliente ha più di un metodo non attivo, ogni volta che ne notifico
		// uno se la volta dopo non è ancora passato un tempo suff. per ri-notificare prenderei per errore un altro (che essendo
		// più vecchio NON deve essere notificato!)

		// altrimenti individua in base alle threshold quelli da notificare
		$metodiPagamentoDaNotificare = [];
		foreach($metodiPagamentoNotificabili as $cliente_id => $m) {
			$toBeNotified = false;
			if( $m['tipo'] == 'carta' ) {
				$thresh1 = Configure::read('zolle.credit_card.not_active.first_reminder_after_hours');
				$thresh2 = Configure::read('zolle.credit_card.not_active.next_reminders_every_hours');
				$thresh3 = Configure::read('zolle.credit_card.not_active.max_reminders_num');
			}
			else {
				$thresh1 = Configure::read('zolle.sepa.not_active.first_reminder_after_hours');
				$thresh2 = Configure::read('zolle.sepa.not_active.next_reminders_every_hours');
				$thresh3 = Configure::read('zolle.sepa.not_active.max_reminders_num');
			}
				 
			if(
				(
					$m['metodo']['data_ultimo_sollecito_attivazione'] == null 
					&& 
					strtotime($m['metodo']['created']) + $thresh1*3600 < time()
				)
				||
				(
					$m['metodo']['data_ultimo_sollecito_attivazione'] != null 
					&& 
					strtotime($m['metodo']['data_ultimo_sollecito_attivazione']) + $thresh2*3600 < time()
					&&
					$m['metodo']['num_solleciti_inviati'] < $thresh3
				)
			) {
				$toBeNotified = true;
			}
	
			if($toBeNotified) {
				$metodiPagamentoDaNotificare[$cliente_id] = $m;
 			}
		}
		ksort($metodiPagamentoDaNotificare); 
		return $metodiPagamentoDaNotificare;
		
		/*AND (
			(
				cc.data_ultimo_sollecito_attivazione IS NULL 
				AND 
				DATE_ADD(cc.created, INTERVAL :timeout_primo_sollecito HOUR) <= CURRENT_DATE()
			)
			OR
			(
				cc.data_ultimo_sollecito_attivazione IS NOT NULL 
				AND 
				DATE_ADD(cc.data_ultimo_sollecito_attivazione, INTERVAL :timeout_altri_solleciti HOUR) <= CURRENT_DATE()
				AND
				cc.num_solleciti_inviati < :max_solleciti
			)
		)*/

		
	}
	
	/**
	 * 
	 */
	public function disattivaAltriMetodiPagamento($cliente_id, $exclude) {
		$CartaModel = ClassRegistry::init('CartaDiCredito');
		$RidModel = ClassRegistry::init('AutorizzazioneRid');
		$BonificoModel = ClassRegistry::init('Bonifico');
		$ContanteModel = ClassRegistry::init('Contante');
		$LegaleModel = ClassRegistry::init('ProceduraLegale');
		
		$dbo1 = $CartaModel->getDataSource();
		$dbo2 = $RidModel->getDataSource();
		$dbo3 = $BonificoModel->getDataSource();
		$dbo4 = $ContanteModel->getDataSource();
		$dbo5 = $LegaleModel->getDataSource();
		
		$dbo1->begin();
		$dbo2->begin();
		$dbo3->begin();
		$dbo4->begin();
		$dbo5->begin();
		
		$transaction_ok = true;
		
		$date = '"'.date('Y-m-d H:i:s').'"'; // non mi è chiaro il perchè ma senza wrap con apici non funziona!
		
		$transaction_ok &= $CartaModel->updateAll([
			'CartaDiCredito.data_disattivazione' => $date
		], [
			'CartaDiCredito.data_disattivazione' => null,
			'CartaDiCredito.cliente_id' => $cliente_id,
			'CartaDiCredito.id <>' => isset($exclude['carta_id']) ? $exclude['carta_id'] : -1
		]);
		$transaction_ok &= $RidModel->updateAll([
			'AutorizzazioneRid.data_disattivazione' => $date
		], [
			'AutorizzazioneRid.data_disattivazione' => null,
			'AutorizzazioneRid.cliente_id' => $cliente_id,
			'AutorizzazioneRid.id <>' => isset($exclude['rid_id']) ? $exclude['rid_id'] : -1
		]);
		$transaction_ok &= $BonificoModel->updateAll([
			'Bonifico.data_disattivazione' => $date
		], [
			'Bonifico.data_disattivazione' => null,
			'Bonifico.cliente_id' => $cliente_id,
			'Bonifico.id <>' => isset($exclude['bonifico_id']) ? $exclude['bonifico_id'] : -1
		]);
		$transaction_ok &= $ContanteModel->updateAll([
			'Contante.data_disattivazione' => $date
		], [
			'Contante.data_disattivazione' => null,
			'Contante.cliente_id' => $cliente_id,
			'Contante.id <>' => isset($exclude['contante_id']) ? $exclude['contante_id'] : -1
		]);
		$transaction_ok &= $LegaleModel->updateAll([
			'ProceduraLegale.data_disattivazione' => $date
		], [
			'ProceduraLegale.data_disattivazione' => null,
			'ProceduraLegale.cliente_id' => $cliente_id,
			'ProceduraLegale.id <>' => isset($exclude['legale_id']) ? $exclude['legale_id'] : -1
		]);
		
		if($transaction_ok) {
			$dbo1->commit();
			$dbo2->commit();
			$dbo3->commit();
			$dbo4->commit();
			$dbo5->commit();
			return true;
		}
		else {
			$dbo1->rollback();
			$dbo2->rollback();
			$dbo3->rollback();
			$dbo4->rollback();
			$dbo5->rollback();
			return false;
		}
	}
	
	/**
	 * usato per gestire con un alert nell'area riservata la richiesta di ri-attivazione su adyen
	 */
	public function contrattoDaRiattivareSuAdyenToSession() {
		$user = CakeSession::read('Auth.User');
		if(empty($user) || empty($user['cliente_id'])) CakeSession::write('contrattoDaRiattivare', null);
		else {
			$res = $this->query('SELECT id_contratto FROM carte_di_credito 
				INNER JOIN clienti 
				ON carte_di_credito.id = clienti.metodo_pagamento_attivo_id
				AND carte_di_credito.signed IS NOT NULL
				AND carte_di_credito.adyen_psp_reference IS NULL
				WHERE clienti.id = '.$user['cliente_id'].' AND clienti.tipo_metodo_pagamento_attivo_id = 1');
			if(empty($res)) {
				CakeSession::write('contrattoDaRiattivare', null);
			}
			else {
				CakeSession::write('contrattoDaRiattivare', $res[0]['carte_di_credito']['id_contratto']);
			}
		}
	}

	public $hasMany = array(
		'User' => array( // in realtà ne ha uno solo ....
			'className' => 'User',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'ContrattoPerCuiRiceveSpesa' => array(
			'className' => 'Contratto',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'ContrattoPerCuiRiceveFattura' => array(
			'className' => 'Contratto',
			'foreignKey' => 'cliente_fatturazione_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Recapito' => array(
			'className' => 'Recapito',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Indirizzo' => array(
			'className' => 'Indirizzo',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Addebito' => array(
			'className' => 'Addebito',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => array('created DESC', 'anno DESC', 'mese DESC'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'CartaDiCredito' => array(
			'className' => 'CartaDiCredito',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'AutorizzazioneRid' => array(
			'className' => 'AutorizzazioneRid',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Bonifico' => array(
			'className' => 'Bonifico',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Contante' => array(
			'className' => 'Contante',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'ProceduraLegale' => array(
			'className' => 'ProceduraLegale',
			'foreignKey' => 'cliente_id',
			'dependent' => true,
			//'type' => 'inner'
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
	
	
	/**
	 * aggiorna/inserisce un cliente (e tutti i modelli associati) da zolla
	 */
	public function syncConZolla($id) {
		
		$ClienteZolla = ClassRegistry::init('ClienteZolla');
		$cliente = $ClienteZolla->find('first', array(
			'conditions' => array('ID_CLIENTE' => $id),
			'contains' => array('RecapitoZolla', 'IndirizzoZolla')
		));
		// converti i modelli (che hanno tutti suffiso 'Zolla') in modelli locali e converti gli id
		// cliente
		$cliente['Cliente'] = $cliente['ClienteZolla'];
		unset($cliente['ClienteZolla']);
		$cliente['Cliente']['id'] = $cliente['Cliente']['ID_CLIENTE'];
		unset($cliente['Cliente']['ID_CLIENTE']);
		// recapiti
		$cliente['Recapito'] = $cliente['RecapitoZolla'];
		unset($cliente['RecapitoZolla']);
		for($i=0;$i<sizeof($cliente['Recapito']);$i++) {
			$cliente['Recapito'][$i]['id'] = $cliente['Recapito'][$i]['ID_CLIENTE_RECAPITO'];
			unset($cliente['Recapito'][$i]['ID_CLIENTE_RECAPITO']);
			$cliente['Recapito'][$i]['cliente_id'] = $cliente['Recapito'][$i]['ID_CLIENTE'];
			unset($cliente['Recapito'][$i]['ID_CLIENTE']);
		}
		// indirizzi
		$cliente['Indirizzo'] = $cliente['IndirizzoZolla'];
		unset($cliente['IndirizzoZolla']);
		for($i=0;$i<sizeof($cliente['Indirizzo']);$i++) {
			$cliente['Indirizzo'][$i]['id'] = $cliente['Indirizzo'][$i]['ID_INDIRIZZO'];
			unset($cliente['Indirizzo'][$i]['ID_INDIRIZZO']);
			$cliente['Indirizzo'][$i]['cliente_id'] = $cliente['Indirizzo'][$i]['ID_CLIENTE'];
			unset($cliente['Indirizzo'][$i]['ID_CLIENTE']);
		}
		
		return $this->saveAll($cliente);
	}
	
}

 
