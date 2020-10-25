				<h5 class="blue"><?php echo $title;?></h5>
				<table class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th>ID contratto</th>
							<th>ID cliente</th>
							<th>Nome cliente</th>
							<th>ID cliente fatt</th>
							<th>Nome cliente fatt</th>
							<th>Azioni</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($contratti as $c):?>
						<tr>
							<td><?php echo $c['id'];?></td>
							<td><?php echo $c['cliente_id'];?></td>
							<td><?php echo $c['Cliente']['displayName'];?></td>
							<td><?php echo $c['cliente_fatturazione_id'];?></td>
							<td><?php echo $c['ClienteFatturazione']['displayName'];?></td>
							<td><?php echo $this->Html->link('<i class="ace-icon fa fa-info-circle bigger-120"></i>', array('controller' => 'contratti', 'action' => 'view', $c['id']), array('escape' => false, 'class' => 'btn btn-xs btn-info', 'title' => 'visualizza contratto'));?></td>
						</tr>	
					<?php endforeach;?>
					</tbody>
				</table> 
