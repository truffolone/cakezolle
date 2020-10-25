<?php

class PagamentiRidController extends AppController {

	var $name = "PagamentiRid";
	
	public $components = array('SepaUtil');
	
	public $uses = array('Addebito');

	public function beforeFilter() {
		
		parent::beforeFilter();
		
	}
	
	/**
	 * seleziona le spese ricorrenti che devono essere processate con carta di credito
	 */
	public function seleziona_ricorrenti() {
		
		// mandatory.  Quando visito la pagina resetto tutti gli excluded che sono valori temporanei
		$this->Addebito->updateAll(
			array('Addebito.excluded' => NULL),
			array('Addebito.id >' => 0)
		);
		
		// controllo di sicurezza: verifica che non ci siano spese attive per precedenti periodi di esazione
		// (che altrimenti verrebbero ripagate!!!)
		$addebitoPiuRecente = $this->Addebito->find('first', array(
			'conditions' => array('type' => RICORRENTE),
			'order' => array('Addebito.anno DESC', 'Addebito.mese DESC')
		));
		$meseEsazione = $addebitoPiuRecente['Addebito']['mese'];
		$annoEsazione = $addebitoPiuRecente['Addebito']['anno'];
		// verifica se esistono degli addebiti attivi con periodo di esazione diverso da quello corrente
		$num = $this->Addebito->find('count', array(
			'conditions' => array(
				'type' => RICORRENTE,
				'active' => 1,
				'OR' => array(
					'mese <>' => $meseEsazione,
					'anno <>' => $annoEsazione
				)
			)
		));
		if($num > 0) {
			$this->Session->setFlash("Attenzione: sono presenti a sistema <b>{$num}</b> spese attive relative a periodi di esazione diversi da quello attuale (<b>{$meseEsazione}/{$annoEsazione}</b>). Correggere il problema per procedere", 'flash_error');
			$this->redirect('/');
		}
		
		// tutto ok, visualizza all'utente il periodo per cui stanno per passare i pagamenti
		$this->Session->setFlash("<b>Attenzione</b>: sta per essere generato il flusso per le spese del periodo <b>{$meseEsazione}/{$annoEsazione}</b>", 'flash_ok');
				
	}
	
	/**
	 * 
	 */
	public function genera_flusso() {
		
		if( $this->request->is('post') ) {
			
			$tokens = explode("/", $this->request->data['PagamentoRid']['data_scadenza']);
		
			$dataScadenza = $tokens[2].'-'.$tokens[1].'-'.$tokens[0]; // formato: AAAA-mm-dd
		
			// ottieni la lista di tutte gli addebiti da pagare (ovvero quelli pagabili e non esclusi)
			$options['conditions']['active'] = 1;
			$options['conditions']['type'] = RICORRENTE;
			$options['conditions']['excluded'] = NULL;
			$options['joins'] = array(
				array(
					'table' => 'autorizzazioni_rid',
					'alias' => 'r',
					'type' => 'INNER',
					'conditions' => array(
						'Addebito.rid_id = r.id',
						'r.rid_activated <>' => NULL,
						'r.data_disattivazione' => NULL
					)
				),
			);
			$options['contain'] = array(
				'AutorizzazioneRid',
				'Cliente' => array(
					'Addebito' => array(
						'conditions' => array(
							'type' => 2,
							'rid_id <>' => null
						)
					)
				)
			);
			
			$addebiti = $this->Addebito->find('all', $options);
			
			$addebitiFRST = array();
			$addebitiRCUR = array();
			foreach($addebiti as $a) {
				if( empty($a['Cliente']['Addebito']) ) continue; // come sei arrivato qui???
				
				if( sizeof($a['Cliente']['Addebito']) == 1 ) {
					// cliente nuovo (c'è solo l'addebito rid corrente) -> FRST
					$addebitiFRST[] = $a;
				}
				else {
					$addebitiRCUR[] = $a;
				}
			}
			
			$flussoSEPA = $this->SepaUtil->getFlussoSEPA('Distinta'.uniqid(), $dataScadenza, $addebitiFRST, $addebitiRCUR);
			
			$this->response->body($flussoSEPA);
			$this->response->type('xml');

			//Optionally force file download
			$this->response->download('flusso_'.$dataScadenza.'.xml');

			// NOTA: PER UNO STRANO ERRORE METTE UN TOT NUMERO (5-6 CARATTERI) ERRATI IN TESTA AL FILE, DA CORREGGERE!

			// Return response object to prevent controller from trying to render
			// a view
			return $this->response;
		}
		
	}
	
	/**
	 * 
	 */
	public function analizza_flusso() {
		
		$upload_max_filesize = ini_get('upload_max_filesize');
		$upload_max_filesize = substr($upload_max_filesize, 0, strlen($upload_max_filesize)-1).' '.substr($upload_max_filesize, strlen($upload_max_filesize)-1).'B';
		$this->set('upload_max_filesize', $upload_max_filesize);
		
		if ($this->request->is('post') || $this->request->is('put')) {

			$f = $this->request->data['PagamentoRid']['file'];

			// validate upload
			switch($f['error']) {
				case 0: // UPLOAD_ERR_OK
					break;
				case 1: // UPLOAD_ERR_INI_SIZE
					$this->Session->setFlash('Il file caricato eccede la massima dimensione consentita dal server. Contattare l\'amministratore del sistema per aumentare il limite disponibile', 'flash_error');
					return;
				case 2: // UPLOAD_ERR_FORM_SIZE
					$this->Session->setFlash('Il file caricato eccede la massima dimensione consentita dal form. Contattare l\'amministratore del sistema per aumentare il limite disponibile', 'flash_error');
					return;
				case 3: // UPLOAD_ERR_PARTIAL
					$this->Session->setFlash('Il file è stato caricato solo parzialmente. Si prega di riprovare. Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;
				case 4: // UPLOAD_ERR_NO_FILE
					$this->Session->setFlash('Nessun file caricato. Selezionare un file prima di procedere con il caricamento', 'flash_error');
					return;
				case 6: // UPLOAD_ERR_NO_TMP_DIR
					$this->Session->setFlash('Errore interno (6). Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;
				case 7: // UPLOAD_ERR_CANT_WRITE 
					$this->Session->setFlash('Errore interno (7). Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;
				case 8: // UPLOAD_ERR_EXTENSION
					$this->Session->setFlash('Errore interno (8). Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;
				default:
					$this->Session->setFlash('Errore interno ('.$f['error'].'). Se il problema persiste contattare l\'amministratore del sistema', 'flash_error');
					return;		
			}
			
			$flussoRID = file_get_contents($f['tmp_name']);
			// rimuovi record di testa
			$flussoRID = substr($flussoRID, 120);
			// rimuovi record di coda
			$flussoRID = substr($flussoRID, 0, strlen($flussoRID)-120);
			// separa le disposizioni
			$currOffset = 0;
			$disposizioni = array();
			while($currOffset < strlen($flussoRID)) {
				$disposizione = substr($flussoRID, $currOffset, 960);
				// estrai le info di interesse
				$disposizione = substr($disposizione, 4*120, 120); // record 30
				$disposizione = substr($disposizione, 2); // rimuovo filler e tipo record
				$progressivo = substr($disposizione, 0, 7);
				$descrizione = substr($disposizione, 7);
				$disposizioni[] = array(
					'progressivo' => $progressivo,
					'descrizione' => $descrizione
				);
				$currOffset += 960;
			}
			
			$this->set('disposizioni', $disposizioni);
		}
	}
	
}
