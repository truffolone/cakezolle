<?php

/*
	id INTEGER NOT NULL
	nome VARCHAR(255)
	email VARCHAR(255)
	id_contratto VARCHAR(30)
	pan VARCHAR(40)
	scadenza_pan VARCHAR(6)
	created TIMESTAMP
	sent TIMESTAMP
	signed TIMESTAMP
	rid_sent TIMESTAMP;
	rid_filled TIMESTAMP;
	rid_activated TIMESTAMP;
	curr_payment_method INTEGER;
	id_contratto_rid VARCHAR(30);
	contractAmountAlreadyCredited INTEGER - usato per scalare dal primo importo ricorrente il pagamento eseguito con il contratto (1 Euro)
	PRIMARY KEY(id));
*/

class CustomerTmp extends AppModel
{
	var $name='CustomerTmp';

	public $useDbConfig = 'tmp';
    
    public $useTable = 'customers';
    
    var $hasOne = array(
		'RidAuthTmp' => array(
			'className' => 'RidAuthTmp',
			'foreignKey' => 'id',
			'dependent' => true
		)
	); 
}
