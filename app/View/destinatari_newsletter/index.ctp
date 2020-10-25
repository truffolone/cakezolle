<h3>
	Elenco Destinatari Newsletter <?php echo $id_newsletter;?> 
	<?php 
		if(isset($type)) {
			if($type == 'sent') echo ' - inviate';
			else echo ' - non inviate';
		}

		echo ' ['.sizeof($destinatari).']'; 
	?>
</h3>
<div class="ui-widget ui-widget-content">
	<table>
		<tr>
			<th>ID</th>
			<th>ID Cliente</th>	
			<th>Nome</th>
			<th>ID Newsletter</th>	
			<th>Email</th>
			<th>Data Creazione</th>
			<th>Data Invio</th>
			<!--<th>Data Lettura</th>-->
		</tr>
	<?php foreach($destinatari as $d):?>
		<tr>
			<td><?php echo $d['DestinatarioNewsletter']['id'];?></td>
			<td><?php echo $d['DestinatarioNewsletter']['id_cliente'];?></td>
			<td>
				<?php 
					$m = unserialize($d['DestinatarioNewsletter']['data']);
					echo $m['NOME'];
				?>
			</td>
			<td><?php echo $this->Html->link($d['DestinatarioNewsletter']['id_newsletter'], array('controller' => 'newsletter', 'action' => 'edit', $d['DestinatarioNewsletter']['id_newsletter']));?></td>
			<td><?php echo $d['DestinatarioNewsletter']['email'];?></td>
			<td><?php echo $d['DestinatarioNewsletter']['created'];?></td>
			<td><?php echo empty($d['DestinatarioNewsletter']['sent']) ? 'non inviata' : date('Y-m-d H:i:s', $d['DestinatarioNewsletter']['sent']);?></td>
			<!--<td><?php echo empty($d['DestinatarioNewsletter']['read']) ? 'non letta' : date('Y-m-d H:i:s', $d['DestinatarioNewsletter']['read']);?></td>-->
		</tr>
	<?php endforeach;?>
	</table>
</div>  
