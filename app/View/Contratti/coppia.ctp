<?php 
	$this->assign('title', 'Contratti');
	$this->assign('subtitle', 'elenco contratti attivi coppia');
?>  

<h4 class="header blue">Elenco contratti attivi per coppia clienti</h4>

<table class="table table-striped table-bordered table-hover">
	<tbody>
		<tr>
			<td>Cliente</td>
			<td><?php echo $this->Html->link($cliente['Cliente']['id'].' - '.$cliente['Cliente']['displayName'], array('controller' => 'clienti', 'action' => 'view', $cliente['Cliente']['id']));?></td>
		</tr>
		<tr>
			<td>Cliente fatturazione</td>
			<td><?php echo $this->Html->link($cliente['Cliente']['id'].' - '.$cliente_fatturazione['Cliente']['displayName'], array('controller' => 'clienti', 'action' => 'view', $cliente_fatturazione['Cliente']['id']));?></td>
		</tr>
	</tbody>
</table>


<?php if(empty($contratti)):?>
	<span class="red">Nessun contratto attivo</span>
<?php else:?>
	<table class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<td>ID contratto</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach($contratti as $c):?>
				<tr><td><?php echo $this->Html->link('Contratto '.$c['Contratto']['id'], array('action' => 'view', $c['Contratto']['id']));?></td></tr>
			<?php endforeach;?>
		</tbody>
	</table>
<?php endif;?>

