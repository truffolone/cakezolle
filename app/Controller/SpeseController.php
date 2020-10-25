<?php

class SpeseController extends AppController 
{
	public $name = 'Spese';

	//public $uses = array('Spesa', 'TipoSpesa');

	public function beforeFilter() 
	{
		parent::beforeFilter();

		$this->layout = "mangio";
		
		$this->Auth->allow('info');
	}
	
	
	/**
	 * Restituisce il dettaglio del tipo spesa specificato per il cliente corrente alla data della prossima consegna
	 * 
	 * NOTA: NON lavorando su spese_cliente ma su clienti_tipo_spesa per necessità (perchè devo visualizzare le spese
	 * per 4 settimane) non so quali scatole corrispondano alle mie spese, pertanto per individuare il contenuto delle spese
	 * mi devo basare sul tipo spesa anzichè su ID_SCATOLA.  
	 * 
	 * TUtto questo però non è un problema anzi:
	 * - se un cliente ha più zolle dello stesso tipo è un caso rarissimo, quindi anche se si ripete (perchè leggo scatole
	 * diverso dello stesso tipo) fa lo stesso.  
	 * In realtà se ci sono più zolle dello stesso tipo in teoria le raggruppo con una qtà > 1 quindi problema risolto
	 * 
	 * - evito il problema di cosa visualizzare nel caso in cui fisicamente una zolla sia stata divisa in più scatole: leggendo
	 * il tipo di zolla leggo effettivamente cosa verrà consegnato per quel tipo zolla (che è coerente con la visualizzazione
	 * sull'area riservata di un record per ogni tipo zolla)
	 */
	public function info($tipoSpesa) {
		
		$cliente = $this->Session->read('cliente');
		$this->loadModel('SpesaClienteZolla');
		$db = $this->SpesaClienteZolla->getDataSource();
		$res = $db->query('CALL AR_prossime_spese(?)', [$cliente['Cliente']['ID_CLIENTE']]);
		$dettagli = [
			'SPESA' => [],
			'SOSTITUZIONE' => [],
			'ARTICOLI ESCLUSI' => [],
			'AGGIUNTE FISSE' => []
		];
		$tipiRiga = array_keys($dettagli);
		foreach($res as $r) { // raggruppa per ID_ARTICOLO Nel caso ci siano più zolle dello stesso tipo
			$tipoRiga = $r['z']['TIPO_RIGA'];
			if( !in_array($tipoRiga, $tipiRiga) ) continue;
			
			$id_articolo = $r['z']['ID_ARTICOLO'];
			if( !isset($dettagli[$tipoRiga][$id_articolo]) ) {
				$dettagli[$tipoRiga][$id_articolo] = array(
					'PRODOTTO' => $r['p']['NOME'],
					'QUANTITA' => (float) str_replace(',', '.', $r['z']['quantita']),
					'UDM' => $r['z']['UDM'],
				);
			}
			else { // incrementa la quantità
				$incr = (float) str_replace(',', '.', $r['z']['quantita']);
				$dettagli[$tipoRiga][$id_articolo]['QUANTITA'] += $incr;
			}
		}
		
		$view = new View($this, false);
		$content = $view->element('mangio/info_spesa', array(
			'dettagli' => $dettagli
		));
			
		$this->set('res', array(
			'success' => true,
			'content' => $content
		));
		$this->set('_serialize', 'res');

	}
	
} 
