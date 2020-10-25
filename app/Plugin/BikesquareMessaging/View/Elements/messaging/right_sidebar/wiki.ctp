<?php
	// aggiungi ai tipi di metatesti il tipo "lavoro"
	array_unshift($tipi_metatesti, array(
		'Tipometatesto' => array(
			'id' => 0,
			'name' => __("Lavoro"),
			'color' => $lavoro_bkg
			
	)));
?>

<div class="margin-left-10">
	<h5><?php echo __('MY WIKI');?></h5>
	
	<?php if($is_admin):?>
		<div class="btn-group-vertical btn-block" role="group" aria-label="...">
			<?php foreach($tipi_metatesti as $m):?>
				<?php
					$k = $m['Tipometatesto']['id'];
					$color = $m['Tipometatesto']['color'];
					$v = $m['Tipometatesto']['name'];
				?>
				<?php echo $this->Html->link($v, 
					'#', 
					array(
						'class' => 'btn btn-default btn-sm open-wiki-target-selection-card',
						'style' => 'color:#fff; background:'.$color,
						'data-tipometatesto-id' => $k
					));
				?>
			<?php endforeach;?> 
		</div>
	<?php else:?>
		<div class="btn-group-vertical btn-block" role="group" aria-label="...">
			<?php foreach($tipi_metatesti as $m):?>
				<?php
					$k = $m['Tipometatesto']['id'];
					$color = $m['Tipometatesto']['color'];
					$v = $m['Tipometatesto']['name'];
				?>
				<?php echo $this->Html->link($v, 
					array('controller' => 'documenti', 'action' => 'wiki', $attivita_id, $k.'.json'), 
					array(
						'class' => 'btn btn-default btn-sm open-wiki',
						'style' => 'color:#fff; background:'.$color
					));
				?>
			<?php endforeach;?> 
		</div>
	<?php endif;?>
	
</div>
