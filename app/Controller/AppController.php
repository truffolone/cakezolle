<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

App::uses('CakeEmail', 'Network/Email');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
 
Configure::load('tempistiche/tempistiche_mlv');
Configure::load('tempistiche/tempistiche_mlp');
Configure::load('tempistiche/area_riservata'); // configurazione disponibilità
Configure::load('zolle');
Configure::load('tempistiche/sollecito_attivazione_metodi_pagamento');

/**
 * ESTRAZIONE STRINGHE i18n:  ./Console/cake i18n extract --extract-core no
 */
 
class AppController extends Controller {
	
	public $components = array(
		'Auth' => array(
			'authorize' => array(
                'Actions' => array('actionPath' => 'controllers')
            )
		), 
		'Acl', 
		'RequestHandler', 
		'Session', 
		'ClientiUtil', 
		'IndirizziClientiUtil',
		'ConsegneUtil');
	
	public $uses = array('CategoriaWeb', 'TagCategoriaWeb', 'Cliente', 'ClienteZolla', 'SpesaZolla', 'Articolo', 'TipoSpesa');
	
	public $helpers = array('Ml', 'Number', 'MetodoPagamento', 'Addebito', 'Avatar', 'User');
	
	/**
	 * 
	 */
	public function beforeRender() {
		
		parent::beforeRender();
		
		if( $this->request->params['action'] != 'unsupported_browser' ) {
			
			if( preg_match('/(?i)msie [5-8]/',$_SERVER['HTTP_USER_AGENT']) ) {
			//if( preg_match('^.*MSIE [2-8](?:\.[0-9]+)?(?!.*Trident\/[5-7]\.0).*$',$_SERVER['HTTP_USER_AGENT']) ) {
				// if IE<=8
				$this->redirect( array('controller' => 'users', 'action' => 'unsupported_browser') );
			}
		}
		
	}
	
	/**
	 * 
	 */
	public function beforeFilter() {
		
		parent::beforeFilter();
		
		// setta il background di base (viene usato questo se non sovrascritto dalla specifica pagina)
		$this->set('subheaderBkg', 'mangio/bg-01.jpg');
		
		$user = $this->Session->read('Auth.User');
		if(empty($user) || $user['group_id'] < 4) return;
		
		
		// gestisci l'ambiente 
		$env = $this->Session->read('env');
		if(empty($env)) {
			$this->Session->write('env', 'MLV'); // default
		}
		if($this->request->query('env')) {
			$this->Session->write('env', strtoupper($this->request->query('env')));
		}
		
		// aggiorna (SEMPRE) le date di acquisto in sessione
		$this->loadModel('Mlv');
		$this->Mlv->aggiornaDataAcquistoInSessione();
		$this->loadModel('Mlp');
		$this->Mlp->aggiornaDataAcquistoInSessione();
		
		// azzera sempre la categoria web in sessione (mi serve per visualizzare i tag di una certa categoria)
		$this->Session->write('categoriaWebCorrente', null);
		
		$this->loadModel('Cliente');
		$giornoConsegna = $this->Cliente->getGiornoConsegna();
		
		// gestisci la visualizzazione del menu 
		$showSideMenu = $this->Session->read('showSideMenu');
		if( $showSideMenu === null ) $this->Session->write('showSideMenu', true);
		
		// setta sempre di default la visualizzazione delle categorie laterali (scompaiono solo nel caso in cui
		// ci si trova in MLV ma si sta guardando in fallback un articolo in prenotazione)
		$this->Session->write('showCategorie', true);
		
		$this->loadModel('User');
		// GESTIONE AUTENTICAZIONE AUTOMATICA PER MESSAGING (temporanea - DA MIGLIORARE)
        /*if( $this->request->query('authkey') 
			//&& $this->request->query('authpass') ) 
		{
			// l'utente ha cliccato sul link di una notifica del messaging, autenticalo
			$username = $this->request->query('authkey');
			if($username == CHAT_ADMIN || $username == 'info@zolle.it') $username = 'amministratore';
			$user = $this->User->find('first', [
				'conditions' => [
					'username' => $this->request->query('authkey'),
					//'password' => $this->request->query('authpass')
				]
			]);
			if($user) {
				// logga l'utente in automatico
				$this->Session->write('Auth.User', $user['User']);
			}
		}
		
		// forzo login admin: per poter gestire/visualizzare correttamente i messaggi come admin ("info@bikesquare.eu")
		// forzo il login degli utenti admin
		$user = $this->Session->read('Auth.User');
		if($user[USER_ROLE_KEY] == ROLE_ADMIN) {
			$this->Session->write('Auth.User', [
				'id' => -1,
				USER_ROLE_KEY => ROLE_ADMIN,
				'username' => 'Le Zolle s.r.l.'
			]);
		}
		
		if($user && !empty($user['cliente_id'])) {
			$num_unread = $this->Cliente->query('SELECT COUNT(id) FROM messaging_messages WHERE is_read = 0 AND conversation_id = '.$user['cliente_id'].' AND is_received = 1 AND from_id <> -2 AND to_id='.$user['id']);
			$num_unread = $num_unread[0][0]['COUNT(id)'];
			$this->Session->write('num_unread', $num_unread);
		}*/
		$this->Session->write('num_unread', 0);
		
	}
	
	/**
	 * 
	 */
	public function logInfo($type, $message, $args=null) {
		$this->zlog('INFO', $type, $message, $args);
	}

	/**
	 * 
	 */
	public function logWarning($type, $message, $args=null) {
		$this->zlog('WARNING', $type, $message, $args);
	}
	
	/**
	 * 
	 */
	public function logError($type, $message, $args=null) {
		$this->zlog('ERROR', $type, $message, $args);
	}
	
	/**
	 * zolle log
	 */
	private function zlog($level, $type, $message, $args=null) {
		$user = $this->Session->read('Auth.User');
		$cliente_id = empty($user) ? 0 : ( empty($user['cliente_id']) ? 0 : $user['cliente_id'] );
		// il logger di cake non prevede oggetti ma stringhe, serializzo per passare al db logger
		$this->log(serialize([
			'cliente_id' => $cliente_id, 
			'message' => $message,
			'args' => $args,
			'type' => $type,
			'shopping_session_id' => $this->Session->read('shopping_session_id')
		]), $level);
	}
	
	/**
	 * se isOrdine visualizzo le sole settimane (editabili) di ordine (acquisto e prenotazione)
	 * altrimenti le prossime 4 settimane ma tutte read only
	 */
	function _getConsegne($target) { 
		
		$displayMode = $target == 'ordine' ? 'detail' : 'summary';
		
		$this->loadModel('User');
		$this->loadModel('Consegna');
		$dateConsegne = $this->Consegna->getConsegneCompletePerNumSettimane(4);

		$Mlv = ClassRegistry::init('Mlv');
		$Mlp = ClassRegistry::init('Mlp');
		
		$dataAcquistoMlv = $Mlv->getDataAcquistoOrNull();
		$dataAcquistoMlp = $Mlp->getDataAcquistoOrNull();
		
		if($target == 'ordine') {
			// visualizza le sole consegne nelle date mlv e mlp, se ml chiuso visualizza
			// le prossime 2 consegne
			// TODO: quando in futuro verranno gestite tutte le possibili date di acquisto qui
			// dovranno comparire tutte le consegne ottenute da Mlv->getDateAcquisto() e Mlp->getDateAcquisto() (ovvero tutte
			// le date a cui posso acquistare)
			$dateConsegneEff = [];
			foreach($dateConsegne as $data => $consegna) {
				if($data == $dataAcquistoMlv || $data == $dataAcquistoMlp) {
					$dateConsegneEff[$data] = $consegna; 
				}
			}
			if(empty($dateConsegneEff)) {
				$i = 0;
				foreach($dateConsegne as $data => $consegna) {
					$dateConsegneEff[$data] = $consegna; 
					$i++;
					if($i == 2) break;
				}
			}
			$dateConsegne = $dateConsegneEff;
		}
		
		$view = new View($this, false);
		return $view->element('mangio/consegne/index', [
			'consegne' => $dateConsegne,
			'dataAcquistoMlv' => $dataAcquistoMlv,
			'dataAcquistoMlp' => $dataAcquistoMlp,
			'userCanShop' => $this->User->canShop(),
			'displayMode' => $displayMode
		]);
	}
	
	
	/**
	 * 
	 */
	function _updateClienteCheRiceveSpesaDaZolla($id_cliente) {
		
		// inizia una nuova "sessione" di shopping: una nuova sessione inizia quando faccio login o dopo aver finalizzato
		// (cioe' quando invoco questo metodo)
		$this->Session->write('shopping_session_id', $id_cliente.'@'.date('Y-m-d H:i:s'));
		
		$this->Session->write('operazioniProvvisorie', []); // reset
		
		// leggi il cliente aggiornato
		$cliente = $this->ClienteZolla->find('first', array(
			'conditions' => array('ID_CLIENTE' => $id_cliente),
			'contain' => array(
				'RecapitoZolla'
			)
		));
		// accorcia le chiavi
		$cliente['Cliente'] = $cliente['ClienteZolla'];
		unset($cliente['ClienteZolla']);
		$cliente['Recapito'] = $cliente['RecapitoZolla'];
		unset($cliente['RecapitoZolla']);
		// salva in sessione
		$this->Session->write('cliente', $cliente);
		
		// ottieni le spese future aggiornate
		$this->loadModel('SpesaZolla');
		$this->SpesaZolla->getProssime($reset=true);
		// ottieni gli af futuri aggioranti
		$this->loadModel('AcquistoArticoloFissoZolla');
		$this->AcquistoArticoloFissoZolla->getProssimi($reset=true);
		// sincronizza mercato libero
		$this->loadModel('Ml');
		$this->Ml->syncWithZolla();
		
	}
	
	/**
	 * 
	 */
	function _aggiornaDettagli() {
		
		// data prossima consegna
		$Consegna = ClassRegistry::init('Consegna');
		$dataProssimaConsegna = $Consegna->getDataProssima();
		// dettagli ml
		$view = new View($this, false);
		$dettagliML = $view->element('mangio/sidemenu_ml'); // nota: ricalcolo anzichè leggere da sessione come in beforeFilter
		$dettagliMLP = $view->element('mangio/sidemenu_mlp'); // nota: ricalcolo anzichè leggere da sessione come in beforeFilter
		$Ml = ClassRegistry::init('Ml');
		$operazioniProvvisorie = $Ml->getOperazioniDaConfermare();
		// aggiorna le operazioni provvisorie in sessione (mi serve per il ottenere il valore aggiornato quando invoco dopo getConsegne)
		$this->Session->write('operazioniProvvisorie', $operazioniProvvisorie);
		$dettagliOperazioniProvvisorie = $view->element('mangio/modale_ordine_provvisorio', array(
			'operazioniProvvisorie' => $operazioniProvvisorie, 
			'tipiSpese' => $this->Session->read('tipiSpese') ));
		$reminder = $view->element('mangio/reminder', array('numOperazioniDaConfermare' => sizeof($operazioniProvvisorie)));
		$finalizzaModifiche = $view->element('mangio/finalizza_modifiche');
		
		// consegne ( NOTA: devo aggiornarle DOPO aver aggiornato le operazioni provvisorie!)
		$consegne = $this->_getConsegne('ordine');
		
		$ultimaOperazione = $this->Session->read('ultimaOperazione');
		// richiesta di zolle, dicitura generica
		if( sizeof($operazioniProvvisorie) > 0 ) { 
			$ultimaOperazione = __('Ci sono modifiche da confermare nel tuo ordine');
		}
		else { 
			$ultimaOperazione = __('Non ci sono modifiche da confermare nel tuo ordine');
		}
		// /richiesta di zolle, dicitura generica
		
		return array(
			'consegne' => json_encode($consegne),
			'dataProssimaConsegna' => $dataProssimaConsegna,
			'dettagliML' => $dettagliML,
			'dettagliOperazioniProvvisorie' => $dettagliOperazioniProvvisorie,
			'reminder' => $reminder,
			'ultimaOperazione' => $ultimaOperazione,
			'numOperazioniProvvisorie' => sizeof($operazioniProvvisorie),
			'finalizzaModifiche' => (sizeof($operazioniProvvisorie) > 0) ? $finalizzaModifiche : '',
		);
		
	}
	
	/**
	 * invia messagio mail interno a zolle (da parte del sistema di pagamenti)
	 */
	function _sendInternalEmail($to, $subject, $msg) {
		
		$Email = new CakeEmail('mandrillapp');
		$Email->from(array('servizio.pagamenti@zolle.it' => 'Le Zolle'));
		$Email->to($to);
		$Email->subject(__("Le Zolle - pagamento con carta di credito"));
		$Email->emailFormat('html');
		$Email->template('notifica_sistema_pagamenti', 'default');
		$Email->viewVars(array(
			'msg' => $msg,
		));
		if(INVIA_MAIL) $Email->send();
		
	}
	
}
