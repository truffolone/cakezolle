<?php

App::uses('ExceptionRenderer', 'Error');

class CustomExceptionRenderer extends ExceptionRenderer {
    
	protected function _outputMessage($template) {
		
		$user = $this->controller->Session->read('Auth.User');
		if( empty($user) || $user['group_id'] > 2 ) {
			$this->controller->layout = 'mangio';
		}
		
		parent::_outputMessage($template);
	}
    
    
}
 
