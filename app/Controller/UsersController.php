<?php

App::uses('SimplePasswordHasher', 'Controller/Component/Auth'); // to be removed. Used for password hashing

class UsersController extends AppController {

	var $name = "Users";

	public $uses = array('User', 'Cliente', 'Group', 'ClienteZolla', 'Contratto');
	
	public function beforeFilter() {
		
		parent::beforeFilter();
		
		$this->Auth->allow('accedi', 'login', 'admin_login', 'logout', 'unsupported_browser');
		// TODO: logout deve essere impostato tra le allowed actions !!!
		
		//$this->Auth->allow('init_permissions'); // We can comment this line after we're finished  
	}
	
	public function unsupported_browser() {
		$this->layout = 'mangio';
	}
	
	public function hash() {
		/*$plainPassword = 'xyz';
		
		$passwordHasher = new SimplePasswordHasher();
        $hashedPassword = $passwordHasher->hash( $plainPassword );
        debug($hashedPassword);die;*/
	}
	
	public function admin_login() {
		
		$this->layout = 'default';
		
		if($this->request->is('post')) {
			if($this->Auth->login()) {
				// If here because user is logged in
				// Check to see if group_id is 1
				if( in_array($this->Auth->user('group_id'), array(1,2,3)) ){
					//$this->redirect($this->Auth->redirect());
					$this->redirect('/');
				}
				else{
					// se non è admin non può accedere via admin login
					$this->Session->write('Auth.User', null);
					$this->Session->setFlash('Non sei autorizzato ad accedere', 'flash_error');
				}
			}
			else {
				$this->Session->setFlash('Nome utente e/o password non validi', 'flash_error');
			}
		}
	
	}
	
	public function init_permissions() {
		
		// NOTA: prima di lanciare il metodo ricordarsi di invocare: 
		// ./Console/cake AclExtras.AclExtras aco_sync
		
		$this->User->query('DELETE FROM aros_acos WHERE id > 0'); // rimuovi qualunque permesso esistente prima di procedere
		
		$group = $this->User->Group;

		// CAKE_ADMIN (tutto)
		$group->id = CAKE_ADMIN;
		$this->Acl->allow($group, 'controllers');

		// AMMINISTRATORE (tutto)
		$group->id = AMMINISTRATORE;
		$this->Acl->allow($group, 'controllers');
		
		// OPERATORE (solo creazione nuovi contratti)
		$group->id = OPERATORE;
		$this->Acl->deny($group, 'controllers');
		$this->Acl->allow($group, 'controllers/Contratti/add');

		// CLIENTE_STANDARD [le tue zolle=RW, profilo=R, ml=RW]
		$group->id = CLIENTE_STANDARD;
		$this->Acl->deny($group, 'controllers');

		$this->Acl->allow($group, 'controllers/CategorieWeb/index');

		$this->Acl->allow($group, 'controllers/Articoli/aggiungi_ml');
		$this->Acl->allow($group, 'controllers/Articoli/index');
		$this->Acl->allow($group, 'controllers/Articoli/rimuovi_ml');
		$this->Acl->allow($group, 'controllers/Articoli/view');

		$this->Acl->allow($group, 'controllers/Clienti/cmpFatture');
		$this->Acl->allow($group, 'controllers/Clienti/download_fattura');
		$this->Acl->allow($group, 'controllers/Clienti/fatture');
		$this->Acl->allow($group, 'controllers/Clienti/oops');
		$this->Acl->allow($group, 'controllers/Clienti/profilo');

		$this->Acl->allow($group, 'controllers/Consegne');

		$this->Acl->allow($group, 'controllers/Zolle');
		
		// CLIENTE_SENZA_OPERATIVITA [le tue zolle=R, profilo=R, ml=R]
		$group->id = CLIENTE_SENZA_OPERATIVITA;
		$this->Acl->deny($group, 'controllers');

		$this->Acl->allow($group, 'controllers/CategorieWeb/index');

		$this->Acl->allow($group, 'controllers/Articoli/index');
		$this->Acl->allow($group, 'controllers/Articoli/view');

		$this->Acl->allow($group, 'controllers/Clienti/cmpFatture');
		$this->Acl->allow($group, 'controllers/Clienti/download_fattura');
		$this->Acl->allow($group, 'controllers/Clienti/fatture');
		$this->Acl->allow($group, 'controllers/Clienti/oops');
		$this->Acl->allow($group, 'controllers/Clienti/profilo');

		$this->Acl->allow($group, 'controllers/Consegne/index');
		$this->Acl->allow($group, 'controllers/Consegne/sidemenu_hidden');
		$this->Acl->allow($group, 'controllers/Consegne/sidemenu_visible');
		
		// CLIENTE_BASSA_OPERATIVITA [le tue zolle=RW, profilo=R, ml=R] - può operare solo su consegne e zolle
		$group->id = CLIENTE_BASSA_OPERATIVITA;
		$this->Acl->deny($group, 'controllers');

		$this->Acl->allow($group, 'controllers/CategorieWeb/index');

		$this->Acl->allow($group, 'controllers/Articoli/index');
		$this->Acl->allow($group, 'controllers/Articoli/view');

		$this->Acl->allow($group, 'controllers/Clienti/cmpFatture');
		$this->Acl->allow($group, 'controllers/Clienti/download_fattura');
		$this->Acl->allow($group, 'controllers/Clienti/fatture');
		$this->Acl->allow($group, 'controllers/Clienti/oops');
		$this->Acl->allow($group, 'controllers/Clienti/profilo');

		$this->Acl->allow($group, 'controllers/Consegne');
		
		$this->Acl->allow($group, 'controllers/Zolle');
		
		
		// CLIENTE_NO_ML [le tue zolle=RW, profilo=R] - non può fare nulla nel ML, nelle zolle può fare tutto
		$group->id = CLIENTE_NO_ML;
		$this->Acl->deny($group, 'controllers');

		$this->Acl->allow($group, 'controllers/Clienti/cmpFatture');
		$this->Acl->allow($group, 'controllers/Clienti/download_fattura');
		$this->Acl->allow($group, 'controllers/Clienti/fatture');
		$this->Acl->allow($group, 'controllers/Clienti/oops');
		$this->Acl->allow($group, 'controllers/Clienti/profilo');

		$this->Acl->allow($group, 'controllers/Consegne');

		$this->Acl->allow($group, 'controllers/Zolle');
		
		// allow basic users to log out
		//$this->Acl->allow($group, 'controllers/users/logout');

		// we add an exit to avoid an ugly "missing views" error message
		echo "all done";
		exit;
		
	}
	
	/**
	 * usata esclusivamente per invitare l'utente ad accedere con il proprio access token
	 */
	public function login() {
		
		$this->layout = 'mangio';
	}
	
	/**
	 * 
	 */
	public function logout() {
		return $this->redirect($this->Auth->logout());
	}
	
	/**
	 * 
	 */
	public function accedi($accesstoken) { // TODO: rimuovere il token forzato!

		// la successiva mi serve per gestire i casi di un utente che accede una volta e subito dopo 
		// accede con un altro token per un altro contratto
		// $this->Session->destroy(); // in realtà non mi serve e genera solo (potenziali) problemi ... 

		$this->layout = 'mangio';
		
		// azzera tutto in sessione (nel caso in cui il cliente si loggi con diversi accesso token dallo stesso pc)
		$this->Session->write('categorieWeb', null);
		$this->Session->write('categorieWebMlp', null);
		$this->Session->write('infoContratto', null); // mandatory per gestire i casi in cui un cliente accede per un contratto e subito dopo accede con un altro
		$this->Session->write('operazioniProvvisorie', array());
		$this->Session->write('ordineProvvisorio', array());
		
		// SETTA IL DEFAULT ENV
		$this->Session->write('env', 'MLV'); // è sempre MLV
		
		// individua il contratto a cui il token si riferisce
		$tokens = explode('$', $accesstoken);
		if( sizeof($tokens) != 3 ) {
			$contratto = null;
		}
		elseif($tokens[1] == 'x') {
			$contratto = $this->Contratto->find('first', array(
				'conditions' => array(
					'data_chiusura' => null, // mandatory !!!
					'cliente_access_token' => $accesstoken
				),
				'contain' => array(
					'Cliente' => array('User'),
					'ClienteFatturazione' => array('User')
				)
			));
		}
		elseif($tokens[1] == 'y') {
			$contratto = $this->Contratto->find('first', array(
				'conditions' => array(
					'data_chiusura' => null, // mandatory !!!
					'cliente_fatturazione_access_token' => $accesstoken
				),
				'contain' => array(
					'Cliente' => array('User'),
					'ClienteFatturazione' => array('User')
				)
			));
		}
		else {
			$contratto = null;	
		}
		
		if( empty($contratto) ) {
			throw new UnauthorizedException(__('Token di accesso non valido'));
		}

		// salva in sessione l'effettivo cliente che si sta loggando (letto da locale) con una extra
		// informazione sul suo ruolo nel contratto
		if($tokens[1] == 'x') {
			$clienteLoggato = $contratto['Cliente'];
			$clienteLoggato['isCliente'] = true;
			$clienteLoggato['isClienteFatturazione'] = ($contratto['Cliente']['id'] == $contratto['ClienteFatturazione']['id']);
		}
		else {
			$clienteLoggato = $contratto['ClienteFatturazione'];
			$clienteLoggato['isClienteFatturazione'] = true; 
			$clienteLoggato['isCliente'] = ($contratto['Cliente']['id'] == $contratto['ClienteFatturazione']['id']);
		}
		$this->Session->write('clienteLoggato', $clienteLoggato);
		// salva in sessione l'id del contratto
		$this->Session->write('contratto_id', $contratto['Contratto']['id']);
		
		// verifica che l'utente associato al cliente esista e sia attivo
		if( empty($clienteLoggato['User']) || $clienteLoggato['User'][0]['active'] == 0 ) {
			throw new UnauthorizedException(__('Utente non attivo'));
		}
		
		// 2017-03-23: se il ruolo dell'utente è sconosciuto lo sbatto fuori
		if( empty($clienteLoggato['User'][0]['group_id']) || $clienteLoggato['User'][0]['group_id'] == 0 ) {
			throw new UnauthorizedException(__('Non sei autorizzato a procedere'));
		}
		
		// logga l'utente programmaticamente
		$this->Session->write('Auth.User', $clienteLoggato['User'][0]);
		
		$this->logInfo("", 'Il cliente si è autenticato tramite access token');
		
		// scrivi in sessione la stringa relativa alla spesa che si sta gestendo
		if( $clienteLoggato['isClienteFatturazione'] && $contratto['Cliente']['id'] != $contratto['ClienteFatturazione']['id'] ) {
			$this->Session->write('infoContratto', __('Stai gestendo le spese indirizzate al cliente %s (%s)', array($contratto['Cliente']['id'], $contratto['Cliente']['displayName'])));
		}
		
		$this->Session->write('NOME', $clienteLoggato['NOME']);
		
		// -----------------------------------------------------------
		
		$this->_updateClienteCheRiceveSpesaDaZolla($contratto['Cliente']['id']);
		
		// setta variabile in sessione (verrà solo aggiornata se si procede con la riattivazione del contratto)
		$this->Cliente->contrattoDaRiattivareSuAdyenToSession();
		$this->Session->write('firstVisitToCategorieAfterLogin', null);
		
		// redireziona alla home
		$user = $this->Session->read('Auth.User');
		if( $user['group_id'] == CLIENTE_NO_ML) { // non può accedere ad ML
			$this->redirect( array('controller' => 'clienti', 'action' => 'profilo') );
		}
		else {
			$this->redirect( array('controller' => 'categorie_web', 'action' => 'index') );
		}
	}

} 
