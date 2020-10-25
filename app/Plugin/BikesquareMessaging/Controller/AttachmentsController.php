<?php
App::uses('AppController', 'Controller');

class AttachmentsController extends BikesquareMessagingAppController {


/**
 * Components
 *
 * @var array
 */
	public $components = array();
	
	public $uses = array('BikesquareMessaging.Attachment');
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->set('activeSection', 'messaging');
		
		$user = $this->Session->read('Auth.User');
		if(!empty($user)) { 
			switch($user[USER_ROLE_KEY]) {
				case ROLE_ADMIN:
				//case ROLE_BAM:
					$this->Auth->allow('upload', 'delete', 'download');
					break;
				default:
					$this->Auth->allow('download');
					break;
			}
		}

	}

	/**
	 * carica via jquery file upload gli allegati relativi ad un messaggio
	 * Dato che il messaggio non è ancora salvato, per linkarli successivamente al messaggio
	 * stesso uso un id temporaneo (link_id) generato come campo nel form del messaggio
	 */
	public function upload($suffix, $link_id) {
		
		$user = $this->Session->read('Auth.User');
		
		if(empty($user) || !in_array($user[USER_ROLE_KEY], [ROLE_ADMIN/*, ROLE_BAM*/])) {
			$this->set('res', array(
				'content' => array(
					'Upload not supported'
				)
			) );
			$this->set('_serialize', 'res');
			return;
		}
		
		if( $this->request->is('post') || $this->request->is('put') ) {
			
			// NOTA: jq-file-upload è configurato per usare files e non data come nome dell'input, quindi i file NON li trovo in $this->request->data ...
		
			// elabora i file caricati
			$notUploadedDocuments = array();
			for($i=0;$i<sizeof($_FILES['files']['tmp_name']);$i++) {
				$r = $this->_handle_uploaded_file($link_id, $i);
	 
				if( empty($r['err']) && $r['moveSuccess'] ) {
					// create the record related to the document on the db
					$this->Attachment->create(); // saving in a loop, mandatory!
					if( $this->Attachment->save(array(
						'name' => $r['name'],
						'path' => $r['path'],
						'link_id' => $link_id,
						'uploader_id' => $user['id']
					)) ) {
						$r['saveSuccess'] = true;
						$r['id'] = $this->Attachment->id;
					}
					else {
						$r['saveSuccess'] = false;
						$r['id'] = -1;
						$notUploadedDocuments[] = $r;
					}
				}
				else {
					$notUploadedDocuments[] = $r;
				}
				
				$result[] = $r;
			}
			
			// restituisco la lista aggiornata degli allegati via ajax
			$this->set('res', array(
				'content' => array(
					'attachments-container-'.$suffix => $this->_getUploadedAttachmentsList($suffix, $link_id, $notUploadedDocuments),
				)
			) );
			$this->set('_serialize', 'res');

		}
		
	}
	
	/**
	 * 
	 */
	function _handle_uploaded_file($link_id, $index) {
		
		$err = '';
		
		// validate upload
		switch($_FILES['files']['error'][$index]) {
			case 0: // UPLOAD_ERR_OK
				$err = '';
				break;
			case 1: // UPLOAD_ERR_INI_SIZE
				$err = 'Il file caricato eccede la massima dimensione consentita dal server. Contattare l\'amministratore del sistema per aumentare il limite disponibile';
				break;
			case 2: // UPLOAD_ERR_FORM_SIZE
				$err = 'Il file caricato eccede la massima dimensione consentita dal form. Contattare l\'amministratore del sistema per aumentare il limite disponibile';
				break;
			case 3: // UPLOAD_ERR_PARTIAL
				$err = 'Il file è stato caricato solo parzialmente. Si prega di riprovare. Se il problema persiste contattare l\'amministratore del sistema';
				break;
			case 4: // UPLOAD_ERR_NO_FILE
				$err = 'Nessun file caricato. Selezionare un file prima di procedere con il caricamento';
				break;
			case 6: // UPLOAD_ERR_NO_TMP_DIR
				$err = 'Errore interno (6). Se il problema persiste contattare l\'amministratore del sistema';
				break;
			case 7: // UPLOAD_ERR_CANT_WRITE 
				$err = 'Errore interno (7). Se il problema persiste contattare l\'amministratore del sistema';
				break;
			case 8: // UPLOAD_ERR_EXTENSION
				$err = 'Errore interno (8). Se il problema persiste contattare l\'amministratore del sistema';
				break;
			default:
				$err = 'Errore interno ('.$_FILES['files']['error'][$index].'). Se il problema persiste contattare l\'amministratore del sistema';
				break;
		}
		
		if(empty($err)) { // move the file
			$dstFolder = 'files' . DS . 'attachments'. DS . $link_id;
			if (!file_exists(APP.$dstFolder)) {
				mkdir(APP.$dstFolder, 0755, true);
			}
			$filename = $_FILES['files']['name'][$index];
			$path = $dstFolder . DS . $filename;
			// verifica che un file con lo stesso nome non sia già presente
			$curr_file_index = 1;
			while(file_exists(APP.$path)) {
				$path = $dstFolder . DS  . $curr_file_index . $filename; // prepend un numero (non in coda perchè non conosco la lunghezza dell'estensione)
				$curr_file_index++;
			}
			$moveSuccess = move_uploaded_file($_FILES['files']['tmp_name'][$index], APP.$path);
		}
		else $moveSuccess = false;
		
		return array(
			'name' => $_FILES['files']['name'][$index],
			'path' => $path,
			'err' => $err,
			'moveSuccess' => $moveSuccess, 
		);
	}
	
	/**
	 * regola generale:
	 * - se il file è stato appena caricato lo può scaricare solo l'uploader
	 * - altrimenti lo scaricano solo gli utenti a cui appartiene un messaggio a cui sia collegato l'allegato
	 */
	public function download($id) {
	
		$user = $this->Session->read('Auth.User');
	
		$a1 = $this->Attachment->find('first', array(
			'conditions' => array(
				'Attachment.id' => $id,
				'uploader_id' => $user['id']
			),
			'recursive' => -1,
		));
	
		$a2 = $this->Attachment->find('first', array(
			'conditions' => array(
				'Attachment.id' => $id,
			),
			'recursive' => -1,
			'joins' => array(
				array(
					'table' => 'messaging_messages_attachments',
					'alias' => 'ma',
					'type' => 'INNER',
					'conditions' => array(
						'Attachment.id = ma.attachment_id',
						'ma.attachment_id' => $id,
					)
				),
				array(
					'table' => 'messaging_messages',
					'alias' => 'm',
					'type' => 'INNER',
					'conditions' => array(
						'm.id = ma.message_id',
						'm.to_id' => $user['id'],
					)
				)
			)
		));
		if( empty($a1) && empty($a2) ) {
			throw new NotFoundException(__('Attachment not found or not available'));
		}
		
		$a = empty($a1) ? $a2 : $a1;
	
		$this->response->file(APP . $a['Attachment']['path']);
		
		//Optionally force file download
		$this->response->download($a['Attachment']['name']);
		
		// Return response object to prevent controller from trying to render
		// a view
		return $this->response;
	
	}

	/**
	 * cancella un attachment che è stato caricato in fase di creazione di un messaggio
	 * 
	 * Regola generale:
	 * - possono cancellare un attachment solo admin o chi lo ha caricato
	 */
	public function delete($suffix, $link_id, $id) {
		
		$a = $this->Attachment->findById($id);
		if(empty($a)) throw new NotFoundException(__('Attachment non trovato'));
		
		if( !$this->_loggedUserIsAdmin() ) {
			$user = $this->Session->read('Auth.User');
			if( $a['Attachment']['uploader_id'] != $user['id'] ) {
				throw new UnauthorizedException(__('Attachment not available'));
			}
		}
		
		if( $this->Attachment->delete($id) ) {
			$success = true;
			
			// cancella il file dal disco
			$file = new File(APP . $a['Attachment']['path']);
			$file->delete();

		}
		else {
			$success = false;
		}
		
		// restituisco la lista aggiornata degli allegati via ajax
		$content['attachments-container-'.$suffix] = $this->_getUploadedAttachmentsList($suffix, $link_id, array());
		
		$this->set('res', array(
			'res' => $success,
			'content' => $content,
		) );
		$this->set('_serialize', 'res');
		
	}
	
	/**
	 * 
	 */
	function _getUploadedAttachmentsList($suffix, $link_id, $notUploadedDocuments) {
		
		// TODO: AL MOMENTO NON MI INTERESSANO I DOCUMENTI CHE NON SONO STATI CARICATI CON SUCCESSO
		// (BANALMENTE: SE NON LI VEDO E' PERCHE' NON SONO STATI CARICATI ;-)
		
		$attachments = $this->Attachment->find('all', array(
			'conditions' => array(
				'link_id' => $link_id,
				//'message_id' => null // importante! (nel caso di upload successivi con lo stesso form ci possono essere allegati già linkati ma con lo stesso link id)
			)
		));
		$view = new View($this, false);
		$content = $view->element('messaging/message/uploaded_attachments', array(
			'suffix' => $suffix,
			'attachments' => $attachments,
			'link_id' => $link_id
		));
		
		return $content;
	}

}
