<?php
	$env = $this->Session->read('env');
?>

<?php echo $this->element('mangio/cerca_articoli');?>

<?php
	//$this->assign('title', $title);
	$this->assign('title', 'MERCATO LIBERO'); // 2018-07: dovunque titolo fisso "mercato libero
	$this->assign('breadcrumb', $this->App->breadcrumb(
		array(
			'Home' => '#',
			$env == 'MLV' ? __('Mercato Libero') : __('Prenotazione Articoli') => 
				Router::url( array('controller' => 'categorie_web', 'action' => 'index', '?' => array(strtolower($env) => 1)) )
		),
		$title
	));
	
	$shopping_type = Configure::read('shopping_type.' . strtolower($env));
	if($shopping_type == null) $shopping_type = 'normal'; // default
	if(!in_array($shopping_type, array('normal', 'quick'))) $shopping_type = 'normal'; // default

	if( $shopping_type == 'normal' ) {
		// uso un element perchè mi serve la stessa struttura anche per i 'visti di recente'
		echo $this->element('mangio/lista_articoli', array('articoli' => $articoli));
	}
	else { // quick list
		// raggruppa gli articoli per categoria web (a loro volta all'interno di ogni categoria sono in ordine alfabetico
		$articoliByCat = array();
		foreach($articoli as $a) {
			$cat_nome = $a['Prodotto']['Sottocategoria']['CategoriaWeb']['NOME']; // uso il nome per fare il sorting anche per categoria web
			if(!isset($articoliByCat[$cat_nome])) {
				$articoliByCat[$cat_nome] = array();
			}
			$articoliByCat[$cat_nome][] = $a;
		}
		ksort($articoliByCat); // ordina per categoria
		echo $this->element('mangio/quick_list', array('articoli' => $articoliByCat));
	}
?>
