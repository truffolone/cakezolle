<?php

class ArticoloVendutoController extends AppController {

	var $name = "ArticoloVenduto";

	public $uses = array('ArticoloVenduto', 'ArticoloVendutoMlp');
	
	public $helpers = array();

	public $components = array();


	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('aggiorna');
		$this->layout = 'mangio';
	}

	/*
	 * invocato da fattoria per aggiornare il venduto finalizzato di un determinato articolo nel caso di cancellazioni
	 * telefoniche o modifiche telefoniche di un ordine
	 * 
	 */
	public function aggiorna($id_articolo, $data_consegna, $id_cliente, $numero_porzioni, $mlp=0) {
		
		$this->layout = 'mangio';
		
		$auth_code = Configure::read('aggiorna_venduto.auth_code');
		if(empty($auth_code)) throw new UnauthorizedException();
		
		$rcvd_auth_code = $this->request->query('auth_code');
		if(empty($rcvd_auth_code) || $rcvd_auth_code != $auth_code) {
			throw new UnauthorizedException();
		}
		
		$id = $id_articolo.'-'.$data_consegna.'-'.$id_cliente;
		if($numero_porzioni == 0) {
			$res = $this->ArticoloVenduto->delete($id);
		}
		else {
			$record = array(
				'id' => $id,
				// aggiungo sempre i 3 campi nel caso si tratti di un nuovo record
				'id_articolo' => $id_articolo,
				'data_consegna' => $data_consegna,
				'id_cliente' => $id_cliente,
				//
				'numero_porzioni' => $numero_porzioni,
				// aggiorno il campo modified
				'modified' => date('Y-m-d H:i:s'),
				'agent' => null // null = inserito via fattoria
			);
			if($mlp == 0) {
				$res = $this->ArticoloVenduto->save($record);
			}
			else {
				$res = $this->ArticoloVendutoMlp->save($record);
			}
		}
		$res = $res ? true : false; // forzo risultato booleano
		
		$this->set('res', $res);
		$this->set('_serialize', array('res'));
		
	}

} 
