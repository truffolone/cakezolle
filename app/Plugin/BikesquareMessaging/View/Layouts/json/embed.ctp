<?php
    // E' UN FRAMMENTO HTML (E NON UN HTML COMPLETO) DI PROPOSITO PER POTER INIETTARE L'HTML RISULTANTE VIA JQUERY DENTRO AD UN ELEMENT
?>

	<?php echo $this->element('BikesquareMessaging.jsCss');?>

	<?php echo $this->Js->writeBuffer(); ?>
	<?php echo $scripts_for_layout; ?>
	<!-- Load JS here for Faster site load =============================-->
	
			<!-- crumb -->
			<?php $crumb = $this->fetch('embedTag');?>
			<?php if(!empty($crumb)):?>
				<div class="row">
					<div class="container"> <!-- aggiunto rispetto al tema base -->
						<div class="col-lg-12">
							<span class="badge badge-primary" style="font-size: 14px">
								<b><?php echo $crumb;?></b>
							</span>
						</div>
					</div>
				</div>	
				<br/>
			<?php endif;?>
			<!-- /crumb -->
	
	<?php echo $content_for_layout; ?>
	<div id="modalWn" class="modal">
			</div>
