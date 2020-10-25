<?php 
	$this->assign('title', 'Contratti');
	$this->assign('subtitle', 'visualizza');
?> 

<h4 class="header blue">Contratto <?php echo $contratto['Contratto']['id'];?></h4>

<?php 
	$attivo = empty($contratto['Contratto']['data_chiusura']);
?>

<table class="table table-striped table-bordered table-hover">
	<tbody>
		<tr>
			<td><span class="grey">ID</span></td>
			<td><?php echo $contratto['Contratto']['id'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Data apertura</span></td>
			<td><?php echo $contratto['Contratto']['created'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Data chiusura</span></td>
			<td><?php echo $contratto['Contratto']['data_chiusura'];?></td>
		</tr>
		<tr>
			<td><span class="grey">Cliente</span></td>
			<td>
				<?php echo $this->Html->link($contratto['Cliente']['id'].' - '.$contratto['Cliente']['displayName'], array('controller' => 'clienti', 'action' => 'view', $contratto['Cliente']['id']), array());?>
				
				<?php if($attivo):?>	
					<br/>
					<?php echo $this->Html->link("Accedi all'area riservata del cliente", array('controller' => 'users', 'action' => 'accedi', $contratto['Contratto']['cliente_access_token']), array('class' => 'btn btn-info btn-minier', 'title' => "accedi all'area riservata del cliente per questo contratto"));?>
					<span class="red"><small>NOTA: procedendo si verrà disconnessi come amministratore</small></span>
				<?php endif;?>
			</td>
		</tr>
		<tr>
			<td><span class="grey">Cliente fatturazione</span></td>
			<td>
				<?php echo $this->Html->link($contratto['ClienteFatturazione']['id'].' - '.$contratto['ClienteFatturazione']['displayName'], array('controller' => 'clienti', 'action' => 'view', $contratto['ClienteFatturazione']['id']), array());?>
			
				<?php if($attivo):?>
					<br/>
					<?php echo $this->Html->link("Accedi all'area riservata del cliente", array('controller' => 'users', 'action' => 'accedi', $contratto['Contratto']['cliente_fatturazione_access_token']), array('class' => 'btn btn-info btn-minier', 'title' => "accedi all'area riservata del cliente per questo contratto"));?>
					<span class="red"><small>NOTA: procedendo si verrà disconnessi come amministratore</small></span>
				<?php endif;?>
			</td>
		</tr>
		<tr>
			<td><span class="grey">Coppia</span></td>
			<td><?php echo $this->Html->link($contratto['Cliente']['id'].' ('.$contratto['Cliente']['displayName'].' ) - '.$contratto['ClienteFatturazione']['id'].' ('.$contratto['ClienteFatturazione']['displayName'].')', array('controller' => 'contratti', 'action' => 'coppia', $contratto['Contratto']['cliente_id'], $contratto['Contratto']['cliente_fatturazione_id']), array());?></td>
		</tr>
		<tr>
			<td><span class="grey">Stato</span></td>
			<td>
				<?php if($attivo):?>
					<span class="green">ATTIVO</span>
					&nbsp;&nbsp;&nbsp;
					<?php echo $this->Html->link('Chiudi contratto', array('controller' => 'contratti', 'action' => 'chiudi', $contratto['Contratto']['id']), array('class' => 'btn btn-info btn-minier', 'title' => 'chiudi contratto (richiede conferma)'));?>
				<?php else:?>
					<span class="red">CHIUSO</span>
				<?php endif;?>
			</td>
		</tr>
		<?php if(!$attivo):?>
		<tr>
			<td>Note</td>
			<td><?php echo $contratto['Contratto']['note'];?></td>
		</tr>
		<?php endif;?>
	</tbody>
</table>
