<?php if($numOperazioniDaConfermare > 0):?>
<li id="bell-reminder" class="violet">
	<a href="#" class="apri-ordine-provvisorio">
		<!--<i class="fa fa-bell fa-2x icon-animated-bell"></i>&nbsp;&nbsp;<span id="num-operazioni-da-confermare" class="badge" style="background:#FFC232;"><?php echo $numOperazioniDaConfermare;?></span>-->
		<i class="violet fa fa-bell fa-2x icon-animated-bell"></i><!--&nbsp;&nbsp;<span id="num-operazioni-da-confermare" class="badge" style="background:#FFC232;"><?php echo $numOperazioniDaConfermare;?></span>-->
	</a>
</li> 
<?php endif;?>
