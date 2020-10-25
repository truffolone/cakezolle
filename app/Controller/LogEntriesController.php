<?php

class LogEntriesController extends AppController 
{
	public $uses = array(
		'LogEntry', 
    );
    
    public $components = array('Paginator');
    
    public $helpers = array('Paginator');
	
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		$user = $this->Session->read('Auth.User');
		if($user && in_array($user['group_id'], [CAKE_ADMIN, AMMINISTRATORE])) {
            $this->Auth->allow('index');
		}

	}
	
	public function index($id_cliente) {
        
        $this->Paginator->settings = array(
			'conditions' => array(
				'LogEntry.cliente_id' => $id_cliente, 
				'LogEntry.created >=' => '2020-06-26 00:00:00' // mandatory per non tirare su i record della vecchia ar
			),
			'limit' => 30,
			'order' => 'LogEntry.id DESC'
		);
		$data = $this->Paginator->paginate('LogEntry');
		$this->set(compact('data'));
		
		$this->loadModel('Cliente');
		$this->set('cliente', $this->Cliente->findById($id_cliente));
        
	}
}  
