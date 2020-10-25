<?php

App::uses('BaseLog', 'Log/Engine');

class DatabaseLog extends BaseLog {
    public function __construct($options = array()) {
        parent::__construct($options);
    }

    public function write($type, $message) {
		$LogEntryModel = ClassRegistry::init('LogEntry');
		$message = unserialize($message); // il logger prevede che il messaggio sia una stringa, uso questo barbatrucco
        // per via del potenziale numero di record che salvo in sequenza devo usare create() altrimenti in loop salva solo l'ultimo!
        $LogEntryModel->create([
			'level' => $type,
			'cliente_id' => $message['cliente_id'],
			'message' => $message['message'],
			'json_args' => json_encode($message['args'], JSON_PRETTY_PRINT),
			'type' => $message['type'],
			'shopping_session_id' => $message['shopping_session_id']
        ]);
        $LogEntryModel->save($LogEntryModel->data);
    }
} 
