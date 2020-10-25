<?php

class DestinatariNewsletterController extends AppController 
{
	var $name = 'DestinatariNewsletter';

	var $uses = array('Newsletter', 'DestinatarioNewsletter');

	function beforeFilter() {
		parent::beforeFilter();
	}	

	function index($id_newsletter, $type=null) {
		$conditions['id_newsletter'] = $id_newsletter;
		if(isset($type)) {
			if($type != 'sent' && $type != 'unsent') $type = 'sent';
			
			if($type == 'sent') $conditions['sent <>'] = NULL;
			else $conditions['sent'] = NULL;
		}

		$destinatari = $this->DestinatarioNewsletter->find('all', array(
			'conditions' => $conditions,
			'order' => array('Newsletter.created DESC')
		));
		
		$this->set('destinatari', $destinatari);
		$this->set('id_newsletter', $id_newsletter);
		if(isset($type)) $this->set('type', $type);
	}

	function add() {	
		if(!empty($this->data)) {
			//begin transaction
			$transaction_ok = true;

			$dbo1 = $this->Newsletter->getDataSource();
			$dbo2 = $this->DestinatarioNewsletter->getDataSource();
			$dbo1->begin($this->Newsletter);
			$dbo2->begin($this->DestinatarioNewsletter);

			$recipients = $this->data['Newsletter']['destinatari'];			
			unset($this->data['Newsletter']['destinatari']);
	
			if(!$this->Newsletter->save($this->data['Newsletter'])) $transaction_ok = false;
 
			$id_newsletter = $this->Newsletter->getLastInsertID();

			$destinatari = explode(';', $recipients);
			foreach($destinatari as $r) {
				if(!filter_var(trim($r), FILTER_VALIDATE_EMAIL)) continue; //rimuove gli indirizzi email errati eventualmente inseriti a mano
				$recapito = $this->RecapitoCliente->find('first', array('conditions' => array(
					'RecapitoCliente.RECAPITO' => $r
				)));
				//memorizza i dati del cliente per i substitution tags
				$recipientData = array(
					'NOME' => empty($recapito) ? 'cliente' : $recapito['Cliente']['NOME'].' '.$recapito['Cliente']['COGNOME']
				);
				$recipient = array(
					'id_newsletter' => $id_newsletter,
					'id_cliente' => empty($recapito) ? 0 : $recapito['RecapitoCliente']['ID_CLIENTE'], 
					'email' => trim($r),
					'data' => serialize($recipientData)
				);
				$this->DestinatarioNewsletter->create($recipient);
				if(!$this->DestinatarioNewsletter->save( $this->DestinatarioNewsletter->data )) $transaction_ok = false;
			}

			if($transaction_ok) {
				$dbo1->commit($this->Newsletter);
				$dbo2->commit($this->DestinatarioNewsletter);
				$this->Session->setFlash('Salvataggio newsletter avvenuto correttamente', 'ok');
				$this->redirect( array('action' => 'confirm_send', $id_newsletter) );
			}
			else {
				$dbo1->rollback($this->Newsletter);
				$dbo2->rollback($this->DestinatarioNewsletter);
				//set again data for form convenience
				$this->data['Newsletter']['destinatari'] = $recipients;	
				$this->Session->setFlash('Si è verificato un errore. Ripetere l\'operazione', 'error');
			}
		}
	}

	function confirm_send($id) {
		//retrieve newsletter to be sent
		$newsletter = $this->Newsletter->findById($id);
		
		//separa i destinatari a cui è stata inviata e quelli a cui non è stata inviata
		$rcpt_unset = array();
		$rcpt_sent = array();
		foreach($newsletter['DestinatarioNewsletter'] as $recipient) {
			if(empty($recipient['sent'])) $rcpt_unsent[] = $recipient;
			else $rcpt_sent[] = $recipient;
		}

		$this->set('newsletter', $newsletter);
		$this->set('sent', $rcpt_sent);
		$this->set('unsent', $rcpt_unsent);
	}

	function send($id) {
		//retrieve newsletter to be sent
		$newsletter = $this->Newsletter->findById($id);

		//TODO: manda a chi non è stata ancora inviata e metti in coda il job con la queue
	}

	function select_recipients() {
		//associative array di query il cui indice è il nome della corrispondente checkbox
		
		$query['lun'] = "SELECT RECAPITO FROM clienti_recapiti
		INNER JOIN clienti ON clienti.ID_CLIENTE = clienti_recapiti.ID_CLIENTE
		INNER JOIN clienti_indirizzi ON clienti_indirizzi.ID_CLIENTE = clienti.ID_CLIENTE
		WHERE clienti_recapiti.TIPO = 'email' AND
		clienti.STATO_CLIENTE = 'ATTIVO' AND
		clienti_indirizzi.GIORNO_CONSEGNA = 'lun'";

		$query['mar'] = "SELECT RECAPITO FROM clienti_recapiti
		INNER JOIN clienti ON clienti.ID_CLIENTE = clienti_recapiti.ID_CLIENTE
		INNER JOIN clienti_indirizzi ON clienti_indirizzi.ID_CLIENTE = clienti.ID_CLIENTE
		WHERE clienti_recapiti.TIPO = 'email' AND
		clienti.STATO_CLIENTE = 'ATTIVO' AND
		clienti_indirizzi.GIORNO_CONSEGNA = 'mar'";

		$query['mer'] = "SELECT RECAPITO FROM clienti_recapiti
		INNER JOIN clienti ON clienti.ID_CLIENTE = clienti_recapiti.ID_CLIENTE
		INNER JOIN clienti_indirizzi ON clienti_indirizzi.ID_CLIENTE = clienti.ID_CLIENTE
		WHERE clienti_recapiti.TIPO = 'email' AND
		clienti.STATO_CLIENTE = 'ATTIVO' AND
		clienti_indirizzi.GIORNO_CONSEGNA = 'mer'";

		$query['gio'] = "SELECT RECAPITO FROM clienti_recapiti
		INNER JOIN clienti ON clienti.ID_CLIENTE = clienti_recapiti.ID_CLIENTE
		INNER JOIN clienti_indirizzi ON clienti_indirizzi.ID_CLIENTE = clienti.ID_CLIENTE
		WHERE clienti_recapiti.TIPO = 'email' AND
		clienti.STATO_CLIENTE = 'ATTIVO' AND
		clienti_indirizzi.GIORNO_CONSEGNA = 'gio'";

		$query['ven'] = "SELECT RECAPITO FROM clienti_recapiti
		INNER JOIN clienti ON clienti.ID_CLIENTE = clienti_recapiti.ID_CLIENTE
		INNER JOIN clienti_indirizzi ON clienti_indirizzi.ID_CLIENTE = clienti.ID_CLIENTE
		WHERE clienti_recapiti.TIPO = 'email' AND
		clienti.STATO_CLIENTE = 'ATTIVO' AND
		clienti_indirizzi.GIORNO_CONSEGNA = 'ven'";

		$recipients = array();

		if(!empty($this->data)) {	
			$res = array();
			$selected_queries = array_keys($this->data['Newsletter']);
			foreach($selected_queries as $selected_query) {
				$query_res = $this->Cliente->query( $query[$selected_query] );
				foreach($query_res as $r) {
					if(filter_var($r[0]['RECAPITO'], FILTER_VALIDATE_EMAIL)) {
						$res[] = $r[0]['RECAPITO'];
					}				
				}
			}
			//remove duplicates
			$res = array_unique($res);
			foreach($res as $r) {
				$a = new StdClass();
				$a->email = $r;
				$recipients[] = $a; 
			}				
			
		}

		$this->layout = 'ajax';
		$this->autoLayout = false;
		$this->autoRender = false; 
		
		$this->header('Content-Type: application/json');
		echo json_encode($recipients); 
		exit();
	}

	
}

