<?php if($contract['Attivita']['legendastati_id'] != 0):?>
<div style="padding:5px; background:#fff">

	<h4><i class="fa fa-info-circle"></i> Dettagli prenotazione</h4>

	<label class="text-primary"><i class="fa fa-clock-o"></i> Data inizio</label>
	<div>
		<small><b><?php echo $contract['Attivita']['rental_date'];?></b></small>
	</div>
	<br/>

	<label class="text-primary"><i class="fa fa-clock-o"></i> Data fine</label>
	<div>
		<small><b><?php echo $contract['Attivita']['return_date'];?></b></small>
	</div>
	<br/>

	<label class="text-primary"><i class="fa fa-map-marker"></i> Dove</label>
	<div>
		<small><b><?=$this->Contact->viewDelivery($contract['Attivita']['id']);?></b></small>
	</div>
	<br/>
	
	<?php if(!empty($contract['Attivita']['BiciPrenotata'])):?>
	<label class="text-primary"><i class="fa fa-bicycle"></i> Bici prenotate</label>
	<div>
		<?php
			// raggruppa le bici per tipo
			$bikes = [];
			foreach($contract['Attivita']['BiciPrenotata'] as $b) {
				if( !isset($bikes[ $b['tipobici_id'] ]) ) {
					$bikes[ $b['tipobici_id'] ] = [
						'tipobici' => empty($b['Tipobici']) ? ['name' => 'n.d.'] : $b['Tipobici'], 
						'bikes_num' => 0
					];
				}
				$bikes[ $b['tipobici_id'] ]['bikes_num']++;
			}
		?>
		<small>
			<table class="table table-striped">
				<tr>
					<th>Tipo bici</th>
					<th>Num</th>
				</tr>
				<?php foreach($bikes as $b):?>
				<tr>
					<th><?php echo $b['tipobici']['name'];?></th>
					<th><?php echo $b['bikes_num'];?></th>
				</tr>
				<?php endforeach;?>
			</table>
		</small>
	</div>
	<?php endif;?>
	
	<?php $addonsStr = $this->Contact->viewAddons($contract['Attivita']['id']);?>
	<?php if($addonsStr):?>
	<label class="text-primary"><i class="fa fa-cart-plus"></i> Addon prenotati</label>
	<div>
		<small>
			<?=$addonsStr;?>
		</small>
	</div>
	<?php endif;?>

</div>
<?php endif;?>
