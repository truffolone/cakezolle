<?php if(empty($dettagli)):?>

	<i><?php echo __('informazione non ancora disponibile');?></i>

<?php else:?>

	<?php if(empty($dettagli['SPESA'])):?>
		<i>Informazione non ancora disponibile</i>
	<?php endif;?>

	<table class="table table-striped">
	
	<?php foreach($dettagli['SPESA'] as $d):?>	
		
		<tr>
			<td>
				<?php echo ucfirst($d['PRODOTTO']);?>
			</td>
			<td class="text-right">
				<?php echo str_replace('.', ',', $d['QUANTITA']).' '.$d['UDM'];?>
			</td>
		</tr>
		
	<?php endforeach;?>
	
	<?php if(sizeof($dettagli['AGGIUNTE FISSE']) > 0):?>
	<tr><td colspan="2"><div class="alert alert-warning"><i>abbiamo aggiunto i seguenti articoli per ogni settimana:</i></div></td></tr>
	
	<?php foreach($dettagli['AGGIUNTE FISSE'] as $d):?>	
		
		<tr>
			<td>
				<?php echo ucfirst($d['PRODOTTO']);?>
			</td>
			<td class="text-right">
				<?php echo str_replace('.', ',', $d['QUANTITA']).' '.$d['UDM'];?>
			</td>
		</tr>
		
	<?php endforeach;?>
	<?php endif;?>
	
	<?php if(sizeof($dettagli['ARTICOLI ESCLUSI']) > 0):?>
	<tr><td colspan="2"><div class="alert alert-warning"><i>abbiamo eliminato i seguenti articoli previsti questa settimana perchè ci hai comunicato di non gradirli:</i></div></td></tr>
	
	<?php foreach($dettagli['ARTICOLI ESCLUSI'] as $d):?>	
		
		<tr>
			<td>
				<?php echo ucfirst($d['PRODOTTO']);?>
			</td>
			<td class="text-right">
				<?php echo str_replace('.', ',', $d['QUANTITA']).' '.$d['UDM'];?>
			</td>
		</tr>
		
	<?php endforeach;?>
	<?php endif;?>
	
	<?php if(sizeof($dettagli['SOSTITUZIONE']) > 0):?>
	<tr><td colspan="2"><div class="alert alert-warning"><i>abbiamo sostituito alcuni articoli che ci hai indicato di non gradire con i seguenti:</i></div></td></tr>
	
	<?php foreach($dettagli['SOSTITUZIONE'] as $d):?>	
		
		<tr>
			<td>
				<?php echo ucfirst($d['PRODOTTO']);?>
			</td>
			<td class="text-right">
				<?php echo str_replace('.', ',', $d['QUANTITA']).' '.$d['UDM'];?>
			</td>
		</tr>
		
	<?php endforeach;?>
	<?php endif;?>

	</table>

<?php endif;?>



