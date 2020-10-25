<?php

class Newsletter extends AppModel 
{
	var $name = 'Newsletter';
	
	public $actsAs = array('Containable');

	var $hasMany = array(
		'DestinatarioNewsletter' => array(
			'className' => 'DestinatarioNewsletter',
			'foreignKey' => 'id_newsletter',
			'dependent' => true //se cancello la newsletter cancello anche tutti i recipient
		)
	);
}
