<?php
	$this->assign('title', strtoupper(__('Accedi'))); 
?>

<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-zolle">
				<?php echo ucfirst(strtolower(__("Accedi all'area riservata")));?>
			</h2>
		</div>
	</div>


<div class="row">

	<div class="col-xs-12">
	
		<p><?php echo __('Gentile cliente,');?></p>
		
		<p><?php echo __('per accedere alla tua area riservata utilizza il link di accesso che hai ricevuto via mail.');?></p>
		
		<br/>
		
		<p><?php echo __("Se lo hai perso o non riesci ad accedere puoi contattare l'Assistenza Clienti Zolle all'indirizzo");?></p>
			
		<p><a href="mailto:<?php echo __("mangio@zolle.it");?>"><i class="fa fa-envelope"></i> <?php echo __("mangio@zolle.it");?></a></p>
				
		<p><?php echo __("o contattarci al numero di telefono");?></p>
			
		<p><a href="tel:<?php echo __("+390692917616");?>"><i class="fa fa-phone-square"></i> <?php echo __("06/92917616");?></a></p>
		
	</div>

</div>
