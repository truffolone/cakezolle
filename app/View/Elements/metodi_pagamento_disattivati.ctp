<?php

	$carte = $cliente['CartaDiCredito'];
	$rid = $cliente['AutorizzazioneRid'];
	$bonifici = $cliente['Bonifico'];
	$contanti = $cliente['Contante'];
	$legale = $cliente['ProceduraLegale'];

?>

				<table class="table table-striped table-bordered table-hover">
					<thead>
						<tr>
							<th>Tipo</th>
							<th>ID</th>
							<th>Titolare</th>
							<th>Stato</th>
							<th>Data disattivazione</th>
							<th>Azioni</th>
						</tr>
					</thead>
					<tbody>
					
					<?php $num=0;?>
					<!-- carte -->
					<?php foreach($carte as $c):?>
						<?php if(empty($c['data_disattivazione'])) continue;?>
						<?php $num++;?>
						<?php
							$attivo = $cliente['Cliente']['tipo_metodo_pagamento_attivo_id'] == CARTA && $cliente['Cliente']['metodo_pagamento_attivo_id'] == $c['id'];
						?>
						<tr class="<?php echo $attivo ? 'bkg-blue' : '';?>">
							<td>Carta di Credito</td>
							<td><?php echo $c['id'];?></td>
							<td><?php echo $cliente['Cliente']['displayName'];?></td>
							<td><?php echo $this->MetodoPagamento->getStato(CARTA, $c);?></td>
							<td>
								<?php echo $c['data_disattivazione'];?>
							</td>
							<td>
								<?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'carte_di_credito', 'action' => 'confirm_delete', $c['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => 'elimina metodo di pagamento (richiede conferma)'));?>
							</td>
						</tr>	
					<?php endforeach;?>
					<!-- /carte -->
					
					<!-- rid -->
					<?php foreach($rid as $r):?>
						<?php if(empty($r['data_disattivazione'])) continue;?>
						<?php $num++;?>
						<?php
							$attivo = $cliente['Cliente']['tipo_metodo_pagamento_attivo_id'] == RID && $cliente['Cliente']['metodo_pagamento_attivo_id'] == $r['id'];
						?>
						<tr class="<?php echo $attivo ? 'bkg-blue' : '';?>">
							<td>RID</td>
							<td><?php echo $r['id'];?></td>
							<td><?php echo empty($r['nome_sottoscrittore']) ? 'n.a.' : $r['nome_sottoscrittore'];?></td>
							<td><?php echo $this->MetodoPagamento->getStato(RID, $r);?></td>
							<td>
								<?php echo $r['data_disattivazione'];?>
							</td>
							<td>
								<?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'autorizzazioni_rid', 'action' => 'confirm_delete', $r['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => 'elimina metodo di pagamento (richiede conferma)'));?>
							</td>
						</tr>	
					<?php endforeach;?>
					<!-- /rid -->
					
					<!-- bonifici -->
					<?php foreach($bonifici as $b):?>
						<?php if(empty($b['data_disattivazione'])) continue;?>
						<?php $num++;?>
						<?php
							$attivo = $cliente['Cliente']['tipo_metodo_pagamento_attivo_id'] == BONIFICO && $cliente['Cliente']['metodo_pagamento_attivo_id'] == $b['id'];
						?>
						<tr class="<?php echo $attivo ? 'bkg-blue' : '';?>">
							<td>Bonifico</td>
							<td><?php echo $b['id'];?></td>
							<td><?php echo $cliente['Cliente']['displayName'];?></td>
							<td><?php echo $this->MetodoPagamento->getStato(BONIFICO, $b);?></td>
							<td>
								<?php echo $b['data_disattivazione'];?>
							</td>
							<td>
								<?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'bonifici', 'action' => 'confirm_delete', $b['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => 'elimina metodo di pagamento (richiede conferma)'));?>
							</td>
						</tr>	
					<?php endforeach;?>
					<!-- /bonifici -->
					
					<!-- contanti -->
					<?php foreach($contanti as $c):?>
						<?php if(empty($c['data_disattivazione'])) continue;?>
						<?php $num++;?>
						<?php
							$attivo = $cliente['Cliente']['tipo_metodo_pagamento_attivo_id'] == CONTANTI && $cliente['Cliente']['metodo_pagamento_attivo_id'] == $c['id'];
						?>
						<tr class="<?php echo $attivo ? 'bkg-blue' : '';?>">
							<td>Contanti</td>
							<td><?php echo $c['id'];?></td>
							<td><?php echo $cliente['Cliente']['displayName'];?></td>
							<td><?php echo $this->MetodoPagamento->getStato(CONTANTI, $c);?></td>
							<td>
								<?php echo $c['data_disattivazione'];?>
							</td>
							<td>
								<?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'contanti', 'action' => 'confirm_delete', $c['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => 'elimina metodo di pagamento (richiede conferma)'));?>
							</td>
						</tr>	
					<?php endforeach;?>
					<!-- /contanti -->
					
					<!-- procedure legali -->
					<?php foreach($legale as $l):?>
						<?php if(empty($l['data_disattivazione'])) continue;?>
						<?php $num++;?>
						<?php
							$attivo = $cliente['Cliente']['tipo_metodo_pagamento_attivo_id'] == PROCEDURA_LEGALE && $cliente['Cliente']['metodo_pagamento_attivo_id'] == $l['id'];
						?>
						<tr class="<?php echo $attivo ? 'bkg-blue' : '';?>">
							<td>Procedura Legale</td>
							<td><?php echo $l['id'];?></td>
							<td><?php echo $cliente['Cliente']['displayName'];?></td>
							<td><?php echo $this->MetodoPagamento->getStato(PROCEDURA_LEGALE, $l);?></td>
							<td>
								<?php echo $l['data_disattivazione'];?>
							</td>
							<td>
								<?php echo $this->Html->link('<i class="ace-icon fa fa-trash bigger-120"></i>', array('controller' => 'procedure_legali', 'action' => 'confirm_delete', $l['id']), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => 'elimina metodo di pagamento (richiede conferma)'));?>
							</td>
						</tr>	
					<?php endforeach;?>
					<!-- /procedure legali -->
					
					</tbody>
				</table> 
				
				<?php if($num == 0):?>
					<div class="row"><div class="col-md-12 text-center">Nessun metodo presente</div></div>
				<?php endif;?>
