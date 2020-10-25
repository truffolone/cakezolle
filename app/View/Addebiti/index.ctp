<?php 
	$this->assign('title', 'Spese ricorrenti');
	$this->assign('subtitle', 'Elenco spese ricorrenti');
?> 

<?php

	$tipi = array(
		ADDEBITI_ALL => 'Tutte',
		ADDEBITI_CARTE_OK => 'Carte OK',
		ADDEBITI_CARTE_KO => 'Carte KO',
		ADDEBITI_CARTE_PAGABILI => 'Carte Pagabili',
		ADDEBITI_CARTE_NON_PAGABILI => 'Carte Non pagabili',
		ADDEBITI_CARTE_BLOCCATE => 'Carte Bloccate',
		ADDEBITI_RID_PAGABILI => 'RID Pagabili',
		ADDEBITI_RID_NON_PAGABILI => 'RID Non pagabili',
		ADDEBITI_BONIFICO => 'Bonifico',
		ADDEBITI_CONTANTE => 'Contanti',
		ADDEBITI_LEGALE => 'Procedura legale',
		ADDEBITI_NON_ATTIVI => 'NON attive (Storico)',
	);

?>

<div class="btn-group">
	<?php foreach($tipi as $k => $v):?>
		<?php
			$btnCls = $tipo_attivo == $k ? 'btn-primary' : 'btn-default'; 
		?>
		<?php echo $this->Html->link($v, array('action' => 'index', $k), array('class' => 'btn btn-sm '.$btnCls));?>
	<?php endforeach;?>
</div>

<hr/>

<?php echo $this->element('dt_addebiti', array(
	'titolo' => 'Spese - '.$tipi[ $tipo_attivo ],
	'id' => 'addebiti',
	'tipo' => $tipo_attivo,
));?>

