<?php 
	$this->assign('title', strtoupper('Oops ...'));
	$this->assign('breadcrumb', $this->App->breadcrumb(
		array(
			'Home' => '#',
		),
		__('oops ...')
	));
?>  

<!--<div class="container-fluid">-->
					
	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-zolle">
				<?php echo ucfirst(strtolower(__("oops ...")));?>
			</h2>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			
			<p><?php echo __("Si è verificato un errore imprevisto.");?></p>
			
			<p><?php echo __("Si prega di riprovare più tardi.");?></p>
			
			<p><?php echo __("Qualora il problema persistesse puoi contattare l'Assistenza Clienti Zolle all'indirizzo");?></p>
			
			<p><a href="mailto:<?php echo __("mangio@zolle.it");?>"><i class="fa fa-envelope"></i> <?php echo __("mangio@zolle.it");?></a></p>
				
			<p><?php echo __("o contattarci al numero di telefono");?></p>
			
			<p><a href="tel:<?php echo __("+390692917616");?>"><i class="fa fa-phone-square"></i> <?php echo __("06/92917616");?></a></p>
			
		</div>
	</div>

<!--</div>-->


