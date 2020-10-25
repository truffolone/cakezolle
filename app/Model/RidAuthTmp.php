<?php

/*
	id INTEGER NOT NULL,  --> id del cliente
	banca_debitore VARCHAR(256), 
	agenzia_debitore VARCHAR(256), 
	codice_paese VARCHAR(2), 
	check_digit VARCHAR(2), 
	cin VARCHAR(1), 
	abi VARCHAR(5), 
	cab VARCHAR(5), 
	conto_corrente VARCHAR(12), 
	nome_sottoscrittore VARCHAR(128), 
	indirizzo_sottoscrittore VARCHAR(128),
	cap_sottoscrittore VARCHAR(5), 
	localita_sottoscrittore VARCHAR(128), 
	codice_fiscale_sottoscrittore VARCHAR(16), 
	anagrafica_instestatario VARCHAR(256), 
	codice_fiscale_instestatario VARCHAR(16), 
	partita_iva_intestatario VARCHAR(11), 
	opzione_addebito INTEGER, 
	PRIMARY KEY(id)
*/

class RidAuthTmp extends AppModel
{
	var $name = 'RidAuthTmp';

	public $useDbConfig = 'tmp';
    
    public $useTable = 'rid_auths';

}
