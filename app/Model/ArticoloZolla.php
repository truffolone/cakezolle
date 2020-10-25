<?php

class ArticoloZolla extends AppModel {
    
    public $useDbConfig = 'zolla';
    
    public $useTable = 'articoli';

	public $primaryKey = 'ID_ARTICOLI';
	
	public $hasMany = array(
		'ArticoloPrezzoZolla' => array(
			'className' => 'ArticoloPrezzoZolla',
			'foreignKey' => 'ID_ARTICOLI',
			'dependent' => true, //se cancello l'articolo cancelli i suoi prezzi
		),
	);
	
	public $belongsTo = array(
		'ProdottoZolla' => array(
			'className' => 'ProdottoZolla',
			'foreignKey' => 'ID_PRODOTTO'
		),
		/*'CategoriaWeb' => array(
			'className' => 'CategoriaWeb',
			'foreignKey' => 'CATEGORIA_WEB'
		)*/
	);

}
 
