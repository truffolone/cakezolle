<?php

App::uses('CakeSession', 'Model/Datasource');

class CategoriaWeb extends AppModel {

	public $actsAs = array('Containable');
	
	public $hasMany = array(
		'TagCategoriaWeb' => array(
			'className' => 'TagCategoriaWeb',
			'foreignKey' => 'categoria_web_id',
			'dependent' => true
		),
		'Sottocategoria' => array( // in realtà è una sola ...
			'className' => 'Sottocategoria',
			'foreignKey' => 'categoria_web_id',
			'dependent' => true
		),
	);
	
	/**
	 * restituisce tutte le categorie web, con il vincolo di restituire solo quelle aventi degli articoli disponibili
	 */
	function getAll($env, $giornoConsegna) {
		$Articolo = ClassRegistry::init('Articolo');
		
		//$categorie = CakeSession::read("CategorieWeb.$env.$giornoConsegna");
		//if(empty($categorie)) {
			$categorie = [];
			$ids = $Articolo->getArticoliEffDisponibili($env);
			if(!empty($ids)) {
				$dispKey = "DISP_".strtoupper($giornoConsegna);
				$categorie = $this->query(
					"SELECT DISTINCT CategoriaWeb.id, CategoriaWeb.NOME, COUNT(Articolo.id) AS num_articoli FROM categorie_web AS CategoriaWeb
					INNER JOIN sottocategorie AS Sottocategoria ON Sottocategoria.categoria_web_id = CategoriaWeb.id 
					INNER JOIN prodotti AS Prodotto ON Prodotto.sottocategoria_id = Sottocategoria.id 
					INNER JOIN articoli AS Articolo ON Articolo.prodotto_id = Prodotto.id 
					WHERE Articolo.ABILITATO = 1 AND (Articolo.DISP_ML = 1 OR Articolo.DISP_AF = 1) AND 
					Articolo.".$dispKey."=1 
					AND (Articolo.DISP_AMBIENTE IS NULL OR Articolo.DISP_AMBIENTE = '' OR Articolo.DISP_AMBIENTE LIKE '%".$env."%')
					AND Articolo.id IN (".implode(',',$ids).") 
					GROUP BY CategoriaWeb.id 
					ORDER BY CategoriaWeb.ORDINAMENTO, CategoriaWeb.NOME"
				);
			}
			//CakeSession::write("CategorieWeb.$env.$giornoConsegna", $categorie);
		//}
		return $categorie;
	}
	
}

 
