<?php 
	$this->assign('title', strtoupper('Browser non supportato'));
	$this->assign('breadcrumb', $this->App->breadcrumb(
		array(
			'Home' => '#',
		),
		__('Browser non supporato')
	));
?>  

<!--<div class="container-fluid">-->
					
	<div class="row">
		<div class="col-md-12">
			<h2 class="titolo-zolle">
				<?php echo ucfirst(strtolower(__("Browser non supportato")));?>
			</h2>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			
			<p>
				Gentile utente, la versione del browser che stai utilizzando (<b>Internet Explorer 8 o inferiore</b>) non è supportata dall'Area Riservata
			</p>
			
			<p>
				Ti invitiamo ad aggiornare il tuo browser ad una versione più recente (<b>9, 10, 11 o seguente</b>) oppure ad utilizzare un altro browser (<b>Firefox, Chrome, Safari o simili</b>)
			</p>
			
		</div>
	</div>

<!--</div>-->


 
