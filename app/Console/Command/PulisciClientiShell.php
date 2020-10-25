<?php

class PulisciClientiShell extends AppShell {
    
    public $uses = array(
		'Cliente',
		'Addebito',
		'AutorizzazioneRid',
		'Bonifico',
		'CartaDiCredito',
		'Contante',
		'ProceduraLegale'
	);
	
	public function main() {
		
		$cliente_id = $this->args && !empty($this->args) ? $this->args[0] : null;
	
		$db = $this->Addebito->getDataSource();
		// leggi i clienti e l'ultimo addebito che hanno avuto
		$res = $db->fetchAll("SELECT 
			c.id, 
			c.NOME, 
			c.COGNOME, 
			c.tipo_metodo_pagamento_attivo_id, 
			c.metodo_pagamento_attivo_id, 
			a.anno, 
			a.mese
			FROM clienti AS c
			LEFT JOIN addebiti AS a
			ON a.cliente_id = c.id AND 
			a.id = (
				SELECT id
				FROM addebiti AS a2
				WHERE a2.cliente_id = c.id AND
				a2.type = 2
				ORDER BY anno DESC, mese DESC
				LIMIT 1
			)
			ORDER BY c.id");
		// leggi per ogni cliente il numero di carte non completate e NON disattivate
		$res2 = $db->fetchAll("SELECT 
			c.id, 
			COUNT(cc.id) AS num
			FROM clienti AS c
			LEFT JOIN carte_di_credito AS cc
			ON cc.cliente_id = c.id
			AND cc.signed IS NULL
			AND cc.data_disattivazione IS NULL
			GROUP BY c.id
			ORDER BY c.id");
		// leggi per ogni cliente il numero di sepa non completati e NON disattivati
		$res3 = $db->fetchAll("SELECT 
			c.id, 
			COUNT(ar.id) AS num
			FROM clienti AS c
			LEFT JOIN autorizzazioni_rid AS ar
			ON ar.cliente_id = c.id
			AND ar.rid_activated IS NULL
			AND ar.data_disattivazione IS NULL
			GROUP BY c.id
			ORDER BY c.id");
		
		// aggiungi l'info ad ogni cliente
		for($i=0;$i<sizeof($res);$i++) {
			$res[$i]['c']['carte_attive_non_complete'] = $res2[$i][0]['num'];
			$res[$i]['c']['sepa_attivi_non_completi'] = $res3[$i][0]['num'];
		}
		
		if($cliente_id) {
			// limita il processo al solo id specificato
			for($i=0;$i<sizeof($res);$i++) {
				if($res[$i]['c']['id'] == $cliente_id) {
					$res = [$res[$i]];
					break;
				}
			}
		}
		
		// estrai tutti i clienti non più attivi (con un debito assente o più vecchio di un anno)
		$clienti_non_attivi = [];
		$clienti_non_attivi_ids = [];
		$clienti_attivi = [];
		$clienti_attivi_ids = [];
		foreach($res as $r) {
			/*if(empty($r['a']) || empty($r['a']['anno'])) {
				$clienti_non_attivi[] = $r['c'];
				$clienti_non_attivi_ids[] = $r['c']['id'];
			}
			else {
				$anno_mese = $r['a']['anno'] . (strlen($r['a']['mese']) == 1 ? '0' : '') . $r['a']['mese'];
				if($anno_mese <= (date('Y')-1).date('m')) {
					$clienti_non_attivi[] = $r['c'];
					$clienti_non_attivi_ids[] = $r['c']['id'];
				}
				else { // cliente attivo
					$clienti_attivi[] = $r['c'];
					$clienti_attivi_ids[] = $r['c']['id'];
				}
			}*/
			// 2020-03-02: li considero tutti attivi
			$clienti_attivi[] = $r['c'];
			$clienti_attivi_ids[] = $r['c']['id'];
		}
		
		$currDate = date('Y-m-d H:i:s');
		
		// disattiva tutti i metodi di pagamento associati ai clienti non attivi
		if(!empty($clienti_non_attivi_ids)) { // se passo uno specifico id può essere vuoto
			if(sizeof($clienti_non_attivi_ids) == 1) {
				$clienti_non_attivi_ids = $clienti_non_attivi_ids[0]; // altrimenti se array a singolo valore da errore sql perchè cake crea la query con = ma con parentesi
			}
			$this->AutorizzazioneRid->updateAll([
				'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
			], [
				'cliente_id' => $clienti_non_attivi_ids
			]);
			$this->Bonifico->updateAll([
				'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
			], [
				'cliente_id' => $clienti_non_attivi_ids
			]);
			$this->CartaDiCredito->updateAll([
				'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
			], [
				'cliente_id' => $clienti_non_attivi_ids
			]);
			$this->Contante->updateAll([
				'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
			], [
				'cliente_id' => $clienti_non_attivi_ids
			]);
			$this->ProceduraLegale->updateAll([
				'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
			], [
				'cliente_id' => $clienti_non_attivi_ids
			]);
			// resetta il metodo di pagamento per tali clienti
			$this->Cliente->updateAll([
				'tipo_metodo_pagamento_attivo_id' => null,
				'metodo_pagamento_attivo_id' => null
			], [
				'Cliente.id' => $clienti_non_attivi_ids
			]);
		}
		
		foreach($clienti_attivi as $k => $c) {
			$metodo_in_attesa = null;
			// gestisci i metodi di pagamento non attivi
			if(intval($c['carte_attive_non_complete']) + intval($c['sepa_attivi_non_completi']) >= 1) {
				// c'è almeno un metodo di pagamento da rimuovere (deve essercene solo uno, l'ultimo sia che si
				// tratti di sepa sia che si tratti di carta)
				// 1. ottieni tutti i metodi carta creati ma non ancora attivi e non disattivatyi
				$carte_non_attive = $this->CartaDiCredito->find('all', [
					'recursive' => -1,
					'conditions' => [
						'cliente_id' => $c['id'],
						'signed' => null,
						'data_disattivazione' => null
					],
					'order' => 'created DESC'
				]);
				// 2. ottieni tutti i metodi sepa creati ma non ancora attivi
				$sepa_non_attivi = $this->AutorizzazioneRid->find('all', [
					'recursive' => -1,
					'conditions' => [
						'cliente_id' => $c['id'],
						'rid_activated' => null,
						'data_disattivazione' => null
					],
					'order' => 'created DESC'
				]);
				$metodi_non_attivi = [];
				foreach($carte_non_attive as $metodo_non_attivo) {
					$metodo_non_attivo = $metodo_non_attivo['CartaDiCredito'];
					$metodo_non_attivo['metodo'] = 'carta';
					$metodi_non_attivi[] = $metodo_non_attivo; 
				} 
				foreach($sepa_non_attivi as $metodo_non_attivo) {
					$metodo_non_attivo = $metodo_non_attivo['AutorizzazioneRid'];
					$metodo_non_attivo['metodo'] = 'sepa';
					$metodi_non_attivi[] = $metodo_non_attivo; 
				}
				// ordina per data di creazione DECRESCENTE
				usort($metodi_non_attivi, function($a, $b) {
					if ($a['created'] == $b['created']) {
						return 0;
					}
					return ($a['created'] > $b['created']) ? -1 : 1;
				});
				// rimuovi il primo elemento (l'unico che deve rimanere) ma solo se pi recente di 30 gg
				if( strtotime($metodi_non_attivi[0]['created']) >= time() - 30*24*60*60 ) {
					$metodo_in_attesa = array_shift($metodi_non_attivi);
				}
				// tutti gli altri vanno disattivati
				$cardIdsToDelete = [];
				$sepaIdsToDelete = [];
				foreach($metodi_non_attivi as $m) {
					if($m['metodo'] == 'carta') {
						$cardIdsToDelete[] = $m['id'];
					}
					else {
						$sepaIdsToDelete[] = $m['id'];
					}
				}
				if(!empty($cardIdsToDelete)) {
					$this->CartaDiCredito->updateAll([
						'CartaDiCredito.data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
					],[
						'CartaDiCredito.id' => $cardIdsToDelete
					]);
				}
				if(!empty($sepaIdsToDelete)) {
					$this->AutorizzazioneRid->updateAll([
						'AutorizzazioneRid.data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
					], [
						'AutorizzazioneRid.id' => $sepaIdsToDelete
					]);
				}
			}
			// gestisci i metodi di pagamento che sono stati attivati:
			// - quello corrente rimane tale
			// - tutti gli altri vengono archiviati
			
			$this->CartaDiCredito
				->updateAll([
					'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
				], [
					'CartaDiCredito.cliente_id' => $c['id'],
					'AND' => [
						['CartaDiCredito.id <>' => $c['tipo_metodo_pagamento_attivo_id'] == 1 ? $c['metodo_pagamento_attivo_id'] : 0],
						['CartaDiCredito.id <>' => $metodo_in_attesa && $metodo_in_attesa['metodo'] == 'carta' ? $metodo_in_attesa['id'] : -1]
					]
				]);
			$this->AutorizzazioneRid
				->updateAll([
					'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
				], [
					'AutorizzazioneRid.cliente_id' => $c['id'],
					'AND' => [
						['AutorizzazioneRid.id <>' => $c['tipo_metodo_pagamento_attivo_id'] == 2 ? $c['metodo_pagamento_attivo_id'] : 0],
						['AutorizzazioneRid.id <>' => $metodo_in_attesa && $metodo_in_attesa['metodo'] == 'sepa' ? $metodo_in_attesa['id'] : -1]
					]
				]);
			$this->Bonifico
				->updateAll([
					'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
				], [
					'Bonifico.cliente_id' => $c['id'],
					'Bonifico.id <>' => $c['tipo_metodo_pagamento_attivo_id'] == 3 ? $c['metodo_pagamento_attivo_id'] : 0
				]);
			$this->Contante
				->updateAll([
					'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
				], [
					'Contante.cliente_id' => $c['id'],
					'Contante.id <>' => $c['tipo_metodo_pagamento_attivo_id'] == 4 ? $c['metodo_pagamento_attivo_id'] : 0
				]);
			$this->ProceduraLegale
				->updateAll([
					'data_disattivazione' => "'".$currDate."'" // sql error if not wrapped with apices!
				], [
					'ProceduraLegale.cliente_id' => $c['id'],
					'ProceduraLegale.id <>' => $c['tipo_metodo_pagamento_attivo_id'] == 5 ? $c['metodo_pagamento_attivo_id'] : 0
				]);
		}
    }
    
    
}
 
