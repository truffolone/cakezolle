<?php

class Sottocategoria extends AppModel {

	public $actsAs = array('Containable');


	public $belongsTo = array(
		'CategoriaWeb' => array(
			'className' => 'CategoriaWeb',
			'foreignKey' => 'categoria_web_id'
		)
	);
	
}

 
