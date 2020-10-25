<?php
App::uses('AppController', 'Controller');
/**
 * Messagingtags Controller
 *
 * @property Messagingtag $Messagingtag
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class MessagingtagsController extends BikesquareMessagingAppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session');
	
	public $uses = array('BikesquareMessaging.Messagingtag');

	/**
	 * 
	 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->layout = 'default';
		
		$user = $this->Session->read('Auth.User');
		if(!empty($user) && $user[USER_ROLE_KEY] == ROLE_ADMIN) {
			$this->Auth->allow('add', 'delete', 'edit', 'index', 'view');
		}
		
	}

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Messagingtag->recursive = 1;
		$this->Messagingtag->order = 'name';
		$this->set('messagingtags', $this->Paginator->paginate());
		
		$user_groups = $this->Messagingtag->UsedBy->find('list');
		$this->set('user_groups', $user_groups);
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Messagingtag->exists($id)) {
			throw new NotFoundException(__('Tag non valido'));
		}
		$options = array('conditions' => array('Messagingtag.' . $this->Messagingtag->primaryKey => $id));
		$this->set('messagingtag', $this->Messagingtag->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			
			$name = $this->request->data['Messagingtag']['name'];
			if( in_array($name, array('sent', 'closed')) ) {
				throw new BadRequestException(__('sent e closed sono due parole riservate'));
			}
			
			$this->Messagingtag->create();
			if ($this->Messagingtag->save($this->request->data)) {
				$this->Session->setFlash(__('Tag salvato'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('Errore durante salvataggio. Si prega di riprovare'), 'default', array('class' => 'alert alert-danger'));
			}
		}
		
		$user_groups = $this->Messagingtag->UsedBy->find('list');
		$this->set('user_groups', $user_groups);
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Messagingtag->exists($id)) {
			throw new NotFoundException(__('Tag non valido'));
		}
		if ($this->request->is(array('post', 'put'))) {
			
			$name = $this->request->data['Messagingtag']['name'];
			if( in_array($name, array('sent', 'closed')) ) {
				throw new BadRequestException(__('sent e closed sono due parole riservate'));
			}
			
			if ($this->Messagingtag->save($this->request->data)) {
				$this->Session->setFlash(__('Tag salvato correttamente'), 'default', array('class' => 'alert alert-success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('Errore durante salvataggio. Si prega di riprovare'), 'default', array('class' => 'alert alert-danger'));
			}
		} else {
			$options = array('conditions' => array('Messagingtag.' . $this->Messagingtag->primaryKey => $id));
			$this->request->data = $this->Messagingtag->find('first', $options);
		}
		
		$user_groups = $this->Messagingtag->UsedBy->find('list');
		$this->set('user_groups', $user_groups);
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id) {
		$this->Messagingtag->id = $id;
		if (!$this->Messagingtag->exists()) {
			throw new NotFoundException(__('Tag non valido'));
		}
		$this->request->onlyAllow('post', 'delete');
		
		// verifica se ci sono conversazioni che usano questo tag		
		$n1 = $this->Messagingtag->query("SELECT COUNT(*) AS n FROM conversations_tags AS ct
				WHERE ct.tag_id = {$id}");
		if($n1[0][0]['n'] > 0) throw new BadRequestException(__("L'elemento è utilizzato da una o più conversazioni"));
		
		if ($this->Messagingtag->delete()) {
			$this->Session->setFlash(__('Tag cancellato'), 'default', array('class' => 'alert alert-success'));
		} else {
			$this->Session->setFlash(__('Errore durante cancellazione. Si prega di riprovare'), 'default', array('class' => 'alert alert-danger'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
