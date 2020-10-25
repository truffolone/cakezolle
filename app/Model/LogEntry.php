<?php

App::uses('CakeSession', 'Model/Datasource');

class LogEntry extends AppModel {

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
		$user = CakeSession::read('Auth.User');
		$cliente_id = empty($user) ? 0 : ( empty($user['cliente_id']) ? 0 : $user['cliente_id'] );
		// il logger di cake non prevede oggetti ma stringhe, serializzo per passare al db logger
		$this->log(serialize([
			'cliente_id' => $cliente_id, 
			'message' => $message,
			'args' => $args,
			'type' => $type,
			'shopping_session_id' => CakeSession::read('shopping_session_id')
		]), $level);
	}
	
}

 
