		<h5 class="blue"><?php echo $titolo;?> [<?php echo sizeof($addebiti);?>]</h5>
		<table class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th>ID</th>
					<th>Importo</th>
					<th>Mese</th>
					<th>Anno</th>
					<th>Stato</th>
					<th>Tipo</th>
					<th>Azioni</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($addebiti as $a):?>
			<?php
				if( !empty($a['carta_id']) ) {
					$tipo_addebito = 'CARTA';
					switch($a['last_payment_ok']) {
						case -1:
							$stato = '<span class="yellow"><b>Nessun pagamento eseguito</b></span>';
							break;
						case 0:
							$stato = '<span class="red"><b>KO</b></span>';
							break;
						case 1:
							$stato = '<span class="green"><b>OK</b></span>';
							break;
						default:
							$stato = 'n.d.';
					}
					
					if($a['blocked']) $stato .= '(BLOCCATA)';
				}
				else {
					$stato = 'n.d.';
					
					if( !empty($a['rid_id']) ) $tipo_addebito = 'RID';
					elseif( !empty($a['bonifico_id']) ) $tipo_addebito = 'BONIFICO';
					elseif( !empty($a['contante_id']) ) $tipo_addebito = 'CONTANTI';
					elseif( !empty($a['legale_id']) ) $tipo_addebito = 'PROCEDURA LEGALE';
					
					else $tipo_addebito = 'n.d.';
				}
			?>
			<tr>
				<td><?php echo $a['id'];?></td>
				<td><?php echo $this->Number->currency($a['importo'], 'EUR');?></td>
				<td><?php echo $a['mese'];?></td>
				<td><?php echo $a['anno'];?></td>
				<td><?php echo $stato;?></td>
				<td><?php echo $tipo_addebito;?></td>
				<td><?php echo $this->Html->link('<i class="ace-icon fa fa-info-circle bigger-120"></i>', array('controller' => 'addebiti', 'action' => 'view', $a['id']), array('escape' => false, 'class' => 'btn btn-xs btn-info', 'title' => 'dettagli'));?></td>
			</tr>
			<?php endforeach;?>
			</tbody>
		</table> 

