<?php

//require_once 'components/dkim/dkim.class.php';
//require_once 'components/dkim/dkim_config.php';

class NewsletterController extends AppController 
{
	var $name = 'Newsletter';

	var $uses = array('Newsletter', 'DestinatarioNewsletter', 'Cliente', 'RecapitoCliente', 'IndirizzoCliente', 'ScatoleDettaglio');
	
	var $components = array('MyUtil', 'Email');	

	function beforeFilter() {
		parent::beforeFilter();
	}	

	function index() {
	}
	
	/**
	 * 
	 */
	public function datatables_processing() {
		
		$this->RequestHandler->setContent('json', 'application/json');

		/* Array of database columns which should be read and sent back to DataTables. Use a space where
	  	 * you want to insert a non-database field (for example a counter or static image)
	 	 */
	 	 
	 	// anche se ho creato un virtualField non posso usarlo perchè non viene impostato nelle clausole order by, quindi devo usare concat()
		$aColumns = array(
			'Newsletter.id', 
			'Newsletter.created', 
			'Newsletter.subject',
			'Newsletter.scheduled',
		);

		/* 
	 	 * Paging
	 	 */
		$sLimit = "";
		$sOffset = "";
		if ( isset( $this->request->query['iDisplayStart'] ) && $this->request->query['iDisplayLength'] != '-1' )
		{
			$sLimit = $this->request->query['iDisplayLength'];
			$sOffset = $this->request->query['iDisplayStart'];
		}

		/*
	 	 * Ordering
		 */
		$sOrder = array();
		if ( isset( $this->request->query['iSortCol_0'] ) )
		{
			for ( $i=0 ; $i<intval( $this->request->query['iSortingCols'] ) ; $i++ )
			{
				if ( $this->request->query[ 'bSortable_'.intval($this->request->query['iSortCol_'.$i]) ] == "true" )
				{
					$sOrder[] = $aColumns[ intval( $this->request->query['iSortCol_'.$i] ) ].' '.($this->request->query['sSortDir_'.$i]==='asc' ? 'ASC' : 'DESC');
				}
			}
		}
	
	
		/* 
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		$sWhere['OR'] = array();
		if ( isset($this->request->query['sSearch']) && $this->request->query['sSearch'] != "" )
		{
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere['OR'][$aColumns[$i].' LIKE'] = "%".( $this->request->query['sSearch'] )."%";
			}
		}
	
		/* Individual column filtering */
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( isset($this->request->query['bSearchable_'.$i]) && $this->request->query['bSearchable_'.$i] == "true" && $this->request->query['sSearch_'.$i] != '' )
			{
				$sWhere[$aColumns[$i].' LIKE'] = "%".($this->request->query['sSearch_'.$i])."%";
			}
		}
		
		$options = array();
		
		if(!empty($sLimit)) $options['limit'] = $sLimit;
		if(!empty($sOffset)) $options['offset'] = $sOffset;

		$options['contain'] = array(
		);

		if(!empty($sWhere)) $options['conditions'] = $sWhere;
		if(!empty($sOrder)) $options['order'] = $sOrder;

		$options['fields'] = $aColumns;

		$res = $this->Newsletter->find('all', $options);	
		
		unset($options['limit']);
		unset($options['offset']);
		$iFilteredTotal = $this->Newsletter->find('count', $options);

		/*
		 * Output
		 */
		$totWhere = array();

		$output = array(
			"sEcho" => isset($this->request->query['sEcho']) ? intval($this->request->query['sEcho']) : 1,
			"iTotalRecords" => $this->Newsletter->find('count'),
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
	
		foreach($res as $row) {
			
			$r["DT_RowId"] = $row['Newsletter']["id"];
			$r["id"] = $row['Newsletter']["id"];
			$r["subject"] = strlen($row['Newsletter']["subject"]) > 40 ? substr($row['Newsletter']["subject"], 0, 40).'...' : $row['Newsletter']["subject"];
			$r["created"] = $row['created'];
			$r["scheduled"] = $row['scheduled'] == 1 ? 'SI' : 'NO';
			
			$r['num_sent'] = $this->DestinatarioNewsletter->find('count', array(
				'conditions' => array(
					'id_newsletter' => $newsletters[$i]['Newsletter']['id'],
					'sent <>' => null
				)
			));
			$r["num_unsent"] = $this->DestinatarioNewsletter->find('count', array(
				'conditions' => array(
					'id_newsletter' => $newsletters[$i]['Newsletter']['id'],
					'sent' => null
				)
			));
			
			$r['actions'] = '<a title="visualizza spesa" href="'.Router::url(array('controller' => 'newsletters', 'action' => 'edit', $row['Newsletter']["id"])).'" class="btn btn-xs btn-info">
				<i class="ace-icon fa fa-info-circle bigger-120"></i>
			</a>';

			$output['aaData'][] = $r;
		}

		
		$this->set('res', $output);
		$this->set('_serialize', 'res');
		
	}

	function delete($id) {
		if($this->Newsletter->delete($id)) {
			$this->Session->setFlash('Newsletter cancellata correttamente', 'ok');			
		}
		else {
			$this->Session->setFlash('Si è verificato un problema. Ripetere l\'operazione', 'error');
		}
		$this->redirect(array('action' => 'index'));
	}

	function edit($id=null) {	
		$current_timeout = ini_get('default_socket_timeout');
		set_time_limit(3000);
		ini_set('default_socket_timeout', '3000');

		if(!empty($this->request->data)) {
			//begin transaction
			$transaction_ok = true;

			$dbo1 = $this->Newsletter->getDataSource();
			$dbo2 = $this->DestinatarioNewsletter->getDataSource();
			$dbo1->begin();
			$dbo2->begin();

			$recipients = $this->request->data['Newsletter']['destinatari'];
			unset($this->request->data['Newsletter']['destinatari']);
	
			if(isset($id)) {
				//cancella tutti i destinatari a cui non è stata ancora inviata la newsletter perchè verranno sovrascritti
				$transaction_ok = $this->DestinatarioNewsletter->deleteAll(
					array(
						'DestinatarioNewsletter.id_newsletter' => $id,
						'DestinatarioNewsletter.sent' => NULL
					)
				);

				$this->request->data['Newsletter']['id'] = $id;
			}

			//setta la newsletter come non scheduled per l'invio
			$this->request->data['Newsletter']['scheduled'] = 0;

			if(!$this->Newsletter->save($this->request->data['Newsletter'])) $transaction_ok = false;
 
			if(isset($id)) $id_newsletter = $id;
			else $id_newsletter = $this->Newsletter->getLastInsertID();

			$destinatari = explode(';', $recipients);

			if(sizeof($destinatari) == 0) {
				$dbo1->commit();
				$dbo2->commit();
				$this->Session->setFlash('Nessun destinatario selezionato', 'flash_error');
				$this->redirect( array('action' == 'edit') );
			}

			$records = array();
			
			foreach($destinatari as $r) {
				 			
				$r = trim($r);

				if(empty($r)) continue;

				if(is_numeric($r)) {
					//È UN ID CLIENTE
					$cliente = $this->Cliente->find('first', array(
						'conditions' => array(
							'Cliente.id' => $r,
						),
						'contain' => array(
							'Contratto' => array(
								'conditions' => array(
									'data_chiusura' => null
								)
							)
						)
					));
					if(!empty($cliente)) {

						//memorizza i dati del cliente per i substitution tags
						$recipientData = array(
							'NOME' => $cliente['Cliente']['displayName'],
							'LINK_ACCESSO' => 'http://mangio.zolle.it/users/accedi/'.$cliente['User']['access_token'],
							'LINK_FATTURE' => 'http://mangio.zolle.it/users/accedi/'.$cliente['User']['access_token'].'/fatture',
							'LINK_MERCATO_LIBERO' => 'http://mangio.zolle.it/users/accedi/'.$cliente['User']['access_token'].'/mercatolibero'
						);

						if(!empty($cliente['RecapitoCliente'])) {
							foreach($cliente['RecapitoCliente'] as $recapito) {
								//TODO: far scegliere da zolle la logica per scegliere il recapito a cui inviare la newsletter. Al momento
								//metto tutti gli indirizzi email che trovo (eventualmente solo 'PRINCIPALE' = 'SI'
								if($recapito['TIPO'] == 'email') {

									if(empty($recapito['RECAPITO'])) continue;
									if(!filter_var($recapito['RECAPITO'], FILTER_VALIDATE_EMAIL)) continue;

									$records[] = array(
										'id_newsletter' => $id_newsletter,
										'id_cliente' => $cliente['Cliente']['ID_CLIENTE'], 
										'email' => trim($recapito['RECAPITO']),
										'data' => serialize($recipientData)
									);
								}
							}
						}
					}
				}
				else {
					//è già un indirizzo email
					if(empty($r)) continue;

					$recipientData = array(
						'NOME' => 'cliente',
						'LINK_ACCESSO' => '',
						'LINK_FATTURE' => '',
						'LINK_MERCATO_LIBERO' => ''
					);

					$records[] = array(
						'id_newsletter' => $id_newsletter,
						'id_cliente' => NULL, 
						'email' => trim($r),
						'data' => serialize($recipientData)
					);
				}
			}

			foreach($records as $record) {
				$this->DestinatarioNewsletter->create($record);
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
		
		//set data
		if(isset($id)) {
			$newsletter = $this->Newsletter->findById($id);
			//setta come destinatari quelli a cui deve ancora essere inviata l'email
			$this->request->data['Newsletter']['destinatari'] = '';
			foreach($newsletter['DestinatarioNewsletter'] as $destinatario) {
				if($destinatario['sent'] == NULL) $this->data['Newsletter']['destinatari'] .= $destinatario['email'].';';
			}	
			
			$this->request->data = $newsletter;
			
		}
		else {
			$this->request->data['Newsletter']['id'] = '';
			$this->request->data['Newsletter']['destinatari'] = '';	
			$this->request->data['Newsletter']['subject'] = '';
			$this->request->data['Newsletter']['content'] = '';
		}

		set_time_limit($current_timeout);
		ini_set('default_socket_timeout', $current_timeout);
	}

	function confirm_send($id)
	{
		//retrieve newsletter to be sent
		$newsletter = $this->Newsletter->findById($id);
		
		//separa i destinatari a cui è stata inviata e quelli a cui non è stata inviata
		$rcpt_unsent = array();
		$rcpt_sent = array();
		foreach($newsletter['DestinatarioNewsletter'] as $recipient) {
			if(empty($recipient['sent'])) $rcpt_unsent[] = $recipient;
			else $rcpt_sent[] = $recipient;
		}

		$this->set('newsletter', $newsletter);
		$this->set('sent', $rcpt_sent);
		$this->set('unsent', $rcpt_unsent);
	}

	//schedule the newsletter to be sent
	function schedule($id)
	{
		$this->Newsletter->id = $id;
		if($this->Newsletter->saveField('scheduled', 1)) {
			$this->Session->setFlash('Newsletter elaborata correttamente. I destinatari selezionati sono stati inseriti nella coda di trasmissione. Verificare l\'esito dell\'invio tra alcune ore (a seconda del numero di destinatari selezionati)', 'ok');
			$this->redirect( array('action' => 'index') );
		}
		else {
			$this->Session->setFlash('Si è verificato un problema. Ripetere l\'operazione', 'error');
			$this->redirect( array('action' => 'edit', $id) );
		}	
	}

	/*function rnd_emails()
	{
		$str = "";
		for($i=0;$i<200;$i++) {
			$str .= substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyz", 5)), 0, 6).$i.'@'.substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5).'.com;';
		}
		debug($str);die;
	}*/

	function send($bulk_size)
	{
		//ottieni la lista dei destinatari a cui deve essere inviata la newsletter (indipendentemente dalla newsletter che è già tirata su come modello associato)
		$recipients = $this->DestinatarioNewsletter->find('all', array(
			'conditions' => array(
				'DestinatarioNewsletter.sent' => NULL,
				'Newsletter.scheduled' => 1
			),
			'limit' => $bulk_size
		));

		//esegui l'invio al bulk selezionato
		foreach($recipients as $recipient) {
			$recipientData = unserialize($recipient['DestinatarioNewsletter']['data']);
			$content = $recipient['Newsletter']['content'];

			//esegui le sostituzioni ai tag sulla base del campo 'data' di ogni recipient
			$content = str_replace('$NOME$', $recipientData['NOME'], $content);
			$content = str_replace('$LINK_ACCESSO$', '<a href="'.$recipientData['LINK_ACCESSO'].'">'.$recipientData['LINK_ACCESSO'].'</a>', $content);
			$content = str_replace('$LINK_FATTURE$', '<a href="'.$recipientData['LINK_FATTURE'].'">'.$recipientData['LINK_FATTURE'].'</a>', $content);
			$content = str_replace('$LINK_MERCATO_LIBERO$', '<a href="'.$recipientData['LINK_MERCATO_LIBERO'].'">'.$recipientData['LINK_MERCATO_LIBERO'].'</a>', $content);

			//componi l'email ed inviala	
			$delivered = $this->_send_email(
				$recipient['DestinatarioNewsletter']['email'],
				'mangio@zolle.it',
				$recipient['Newsletter']['subject'],
				$content,
				3);
			if($delivered) {
				//memomorizza la data di invio al recipient
				$r['id'] = $recipient['DestinatarioNewsletter']['id'];
				$r['sent'] = time();
				if(!$this->DestinatarioNewsletter->save($r)) {
					$this->log("SEVERE: Impossibile salvare il campo sent ".$r['sent']." per ".$recipient['DestinatarioNewsletter']['email'], 'newsletter');
				}
			}		
		}
	}

	function _send_email($to, $from, $subject, $content, $num_retry)
	{
		$vars = array('messaggio' => $content);
		return $this->send_email_tpl('newsletter', $to, $from, $subject, $vars, $num_retry);

		/*$headers =
		'MIME-Version: 1.0
		From: "Zolle" <mangio@zolle.it>
		Content-type: text/html; charset=utf8';

		$message = $content;

		// Make sure linefeeds are in CRLF format - it is essential for signing
		$message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
		$headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);

		$signature = new mail_signature(
			MAIL_RSA_PRIV,
			MAIL_RSA_PASSPHRASE,
			MAIL_DOMAIN,
			MAIL_SELECTOR
		);
		$signed_headers = $signature -> get_signed_headers($to, $subject, $message, $headers);

		mail($to, $subject, $message, $signed_headers.$headers);

		return true;*/
	}

	function select_recipients()
	{
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
				if(isset($query[$selected_query])) { //ci sono query aggiuntive che vengono trattate dopo in modo diverso
					$query_res = $this->Cliente->query( $query[$selected_query] );
					foreach($query_res as $r) {
						if(filter_var($r[0]['RECAPITO'], FILTER_VALIDATE_EMAIL)) {
							$res[] = $r[0]['RECAPITO'];
						}				
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

		//gestisci la query relative ai "clienti con almeno una fattura emessa nel giorno x"
		if(!empty($this->data)) {
			if(isset($this->data['Newsletter']['fattura'])) {
				if(empty($this->data['Newsletter']['data_fattura'])) {
					$this->data['Newsletter']['data_fattura'] = date('Ymd'); //se non valorizzato usa la data corrente
				}
				$query_res = $this->ScatoleDettaglio->query("SELECT scatole_dettaglio.ID_CLIENTE
					,group_concat(DISTINCT cast(scatole_dettaglio.ID_FATTURA AS CHAR(10))) AS elencofatture
					FROM scatole_dettaglio
					WHERE (scatole_dettaglio.DATA = '".$this->data['Newsletter']['data_fattura']."') AND id_fattura IS NOT NULL
					GROUP BY scatole_dettaglio.ID_CLIENTE");

				foreach($query_res as $r) {
					$a = new StdClass();
					$a->email = $r['scatole_dettaglio']['ID_CLIENTE'];
					$recipients[] = $a;
				}							
	
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

