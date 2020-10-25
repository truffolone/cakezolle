<?php

App::uses('CakeSession', 'Model/Datasource');

class TagCategoriaWeb extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'tag_categorie_web';

	public $belongsTo = array(
		'CategoriaWeb' => array(
			'className' => 'CategoriaWeb',
			'foreignKey' => 'categoria_web_id'
		)
	);
	
	
	function getAll($env, $giornoConsegna) {
		$Articolo = ClassRegistry::init('Articolo');
		//$tags = CakeSession::read("Tags.$giornoConsegna");
		//if(empty($tags)) {
			$ids = $Articolo->getArticoliEffDisponibili($env);
			$tagsQueryRes = [];
			if(!empty($ids)) {
				$tagsQueryRes = $this->query(
					"SELECT DISTINCT TagCategoriaWeb.id, TagCategoriaWeb.TAG AS tag, TagCategoriaWeb.categoria_web_id AS categoria_web_id, COUNT(Articolo.id) AS num_articoli FROM tag_categorie_web AS TagCategoriaWeb
					INNER JOIN prodotti AS Prodotto ON Prodotto.TAGS LIKE CONCAT('%;',tag,';%') 
					INNER JOIN articoli AS Articolo ON Articolo.prodotto_id = Prodotto.id
					WHERE Articolo.ABILITATO = 1 AND (Articolo.DISP_ML = 1 OR Articolo.DISP_AF = 1) 
					AND Articolo.DISP_".strtoupper($giornoConsegna)."=1
					AND Articolo.id IN (".implode(',',$ids).")
					GROUP BY TagCategoriaWeb.id
						ORDER BY tag"
				);
			}
			// raggruppa per categoria web (all'interno di ogni gruppo i tag sono già ordinati alfabeticamente)
			$tags = array();
			foreach($tagsQueryRes as $r) {
				if( empty($r['TagCategoriaWeb']['categoria_web_id']) ) { // tag generale: setta 0
					$r['TagCategoriaWeb']['categoria_web_id'] = 0;
				}
					
				if( !isset($tags[ $r['TagCategoriaWeb']['categoria_web_id'] ]) ) {
					$tags[ $r['TagCategoriaWeb']['categoria_web_id'] ] = array();
				}
				$tags[ $r['TagCategoriaWeb']['categoria_web_id'] ][] = $r;
			}
			//CakeSession::write("Tags.$giornoConsegna", $tags);
		//}
		return $tags;
	}
	
	
}

 
