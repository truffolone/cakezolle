<?php

class Prodotto extends AppModel {

	public $actsAs = array('Containable');

	// mandatory per fare certe query in modo semplice
	public $hasMany = array(
		'Articolo' => array(
			'className' => 'Articolo',
			'foreignKey' => 'prodotto_id',
			'dependent' => true
		)
	);

	public $belongsTo = array(
		'Fornitore' => array(
			'className' => 'Fornitore',
			'foreignKey' => 'fornitore_id'
		),
		'Sottocategoria' => array(
			'className' => 'Sottocategoria',
			'foreignKey' => 'sottocategoria_id'
		)
	);
	
	
}

 
