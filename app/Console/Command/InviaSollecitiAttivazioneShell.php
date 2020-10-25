<?php

App::uses('CakeEmail', 'Network/Email');
// devo caricarli esattamente come in AppController per potervi accedere!
Configure::load('tempistiche/sollecito_attivazione_metodi_pagamento', 'default'); 

class InviaSollecitiAttivazioneShell extends AppShell {
    
    public $uses = array(
		'Cliente',
		'AutorizzazioneRid',
		'CartaDiCredito',
		'Reminder'
	);
	
	public function main() {
	
		$metodiPagamentoDaNotificare = $this->Cliente->getMetodiPagamentoDaNotificare();
		$ClientiUtil = new ClientiUtilComponent(new ComponentCollection);
		$contatoreDiTest = 0;
		foreach($metodiPagamentoDaNotificare as $id_cliente => $m) {
			$contatoreDiTest++;
			$cliente = $this->Cliente->findById($id_cliente);
			// ottieni i recapiti a cui inviare il messaggio
			$recapiti = $ClientiUtil->getRecapitiPerTipo($cliente['Recapito'], 'A');
			$atLeastOneEmailSuccessfullySent = false;
			foreach($recapiti as $recapito) {
				// invia mail di sollecito
				$Email = new CakeEmail('mandrillapp');
				$Email->from(array('mangio@zolle.it' => 'Le Zolle'));
				$Email->to('andreadiaco@gmail.com'/*$recapito*/);
				$Email->setHeaders(array(
					'X-MC-Metadata' => '{"id_cliente": "'.$cliente['Cliente']['id'].'", "id_cliente_fatturazione": "'.$cliente['Cliente']['ID_CLIENTE_FATTURAZIONE'].'"}'
				));
				$Email->emailFormat('html');
				if($m['tipo'] == 'carta') {
					$Email->subject(__("Le Zolle - attivazione pagamento con carta di credito"));
					$Email->template('sollecito_attivazione_carta', 'default');
					$Email->viewVars(array(
						'nomeCliente' => $cliente['Cliente']['displayName'],
						'created' => date('d/m/Y', strtotime($m['created'])),
						'urlContratto' => 'https://mangio.zolle.it/carte_di_credito/contratto/'.$m['metodo']['id_contratto']
					));
				}
				else {
					$Email->subject(__("Le Zolle - attivazione pagamento con SEPA"));
					$Email->template('sollecito_attivazione_rid', 'default');
					$Email->viewVars(array(
						'nomeCliente' => $cliente['Cliente']['displayName'],
						'created' => date('d/m/Y', strtotime($m['created'])),
						'urlContratto' => 'https://mangio.zolle.it/autorizzazioni_rid/contratto/'.$m['metodo']['id_contratto_rid']
					));
				}
				
				if(INVIA_MAIL) {
					$sent = $contatoreDiTest > 10 ? true : $Email->send();
					$atLeastOneEmailSuccessfullySent |= $sent;
					if( $sent ) {
						// salva il reminder inviato
						$this->Reminder->create(); // saving in a loop ... (outer loop of the process)
						$this->Reminder->save([
							'recapito' => $recapito,
							'tipo_metodo_pagamento_id' => $m['tipo'] == 'carta' ? CARTA : RID,
							'metodo_pagamento_id' => $m['metodo']['id'],
						]);
					}
				}				
			}

			if($atLeastOneEmailSuccessfullySent) { // aggiorna il numero di solleciti inviati
				if($m['tipo'] == 'carta') {
					$this->CartaDiCredito->save([
						'id' => $m['metodo']['id'],
						'data_ultimo_sollecito_attivazione' => date('Y-m-d H:i:s'),
						'num_solleciti_inviati' => $m['metodo']['num_solleciti_inviati'] + 1
					]);
				}
				else {
					$this->AutorizzazioneRid->save([
						'id' => $m['metodo']['id'],
						'data_ultimo_sollecito_attivazione' => date('Y-m-d H:i:s'),
						'num_solleciti_inviati' => $m['metodo']['num_solleciti_inviati'] + 1
					]);
				}
			}
		}
		
	}
    
    
}
 
