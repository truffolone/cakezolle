<?php

/*
CREATE TABLE destinatari_newsletter (
	id INTEGER NOT NULL, 
	id_newsletter INTEGER NOT NULL,
	id_cliente INTEGER, 
	email VARCHAR(255),
	data VARCHAR(1024),
	sent TIMESTAMP,
	read TIMESTAMP,
	created TIMESTAMP, 	
	PRIMARY KEY(id));  

CREATE TABLE destinatari_newsletter (id INTEGER NOT NULL,id_newsletter INTEGER NOT NULL,id_cliente INTEGER,email VARCHAR(255),data VARCHAR(1024),sent TIMESTAMP,read TIMESTAMP,created TIMESTAMP,PRIMARY KEY(id));  

*/
  
class DestinatarioNewsletter extends AppModel 
{
	var $useTable = 'destinatari_newsletter';

    var $name = 'DestinatarioNewsletter';

	var $belongsTo = array(
		'Newsletter' => array(
			'className' => 'Newsletter',
			'foreignKey' => 'id_newsletter'
		)
	);
}

