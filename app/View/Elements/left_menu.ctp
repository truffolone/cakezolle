<?php
	$clsDashboard = ($this->params['controller'] == 'clienti' && $this->params['action'] == 'dashboard') ? 'active' : '';
	$clsClienti = ($this->params['controller'] == 'clienti' && $this->params['action'] != 'dashboard') ? 'active' : '';
	$clsContratti = ($this->params['controller'] == 'contratti') ? 'active' : '';
	$clsAddebiti = ($this->params['controller'] == 'addebiti') ? 'active' : '';
	$clsCarte = ($this->params['controller'] == 'pagamenti_carta') ? 'active' : '';
	$clsRid = ($this->params['controller'] == 'pagamenti_rid') ? 'active' : '';
	$clsNewsletter = in_array($this->params['controller'], array('newsletter', 'destinatari_newsletter')) ? 'active' : '';
?>

<ul class="nav nav-list">
	<!-- dashboard -->
	<li class="<?php echo $clsDashboard;?>">
		<a href="<?php echo $this->Html->url('/');?>">
			<i class="menu-icon fa fa-tachometer"></i>
			<span class="menu-text"> Dashboard </span>
		</a>
		<b class="arrow"></b>
	</li>
	
	<!-- clienti -->
	<li class="<?php echo $clsClienti;?>">
		<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'clienti', 'action' => 'index'));?>">
			<i class="menu-icon fa fa-users"></i>
			<span class="menu-text"> Clienti </span>
		</a>
		<b class="arrow"></b>
	</li>
	
	<!-- contratti -->
	<li class="<?php echo $clsContratti;?>">
		<a href="#" class="dropdown-toggle">
			<i class="menu-icon fa fa-file-text-o"></i>
			<span class="menu-text">
				Contratti
			</span>

			<b class="arrow fa fa-angle-down"></b>
		</a>

		<b class="arrow"></b>

		<ul class="submenu">
							
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'contratti', 'action' => 'add'));?>" title="elenco contratti">
					<i class="menu-icon fa fa-caret-right"></i>
					Nuovo contratto
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('controller' => 'contratti', 'action' => 'index', 'attivi'));?>" title="elenco contratti">
					<i class="menu-icon fa fa-caret-right"></i>
					Elenco contratti attivi
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('controller' => 'contratti', 'action' => 'index', 'chiusi'));?>" title="elenco contratti">
					<i class="menu-icon fa fa-caret-right"></i>
					Elenco contratti chiusi
				</a>

				<b class="arrow"></b>
			</li>
		</ul>
	</li>
	
	<!-- spese -->
	<li class="<?php echo $clsAddebiti;?>">
		<a href="#" class="dropdown-toggle">
			<i class="menu-icon fa fa-money"></i>
			<span class="menu-text">
				Spese
			</span>

			<b class="arrow fa fa-angle-down"></b>
		</a>

		<b class="arrow"></b>

		<ul class="submenu">
							
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'addebiti', 'action' => 'index'));?>" title="elenco spese">
					<i class="menu-icon fa fa-caret-right"></i>
					Elenco spese
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'addebiti', 'action' => 'carica_ricorrenti'));?>" title="carica ricorrenti">
					<i class="menu-icon fa fa-caret-right"></i>
					Carica spese ricorrenti
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'addebiti', 'action' => 'conferma_caricamento'));?>" title="conferma caricamento">
					<i class="menu-icon fa fa-caret-right"></i>
					Conferma caricamento ricorrenti
				</a>

				<b class="arrow"></b>
			</li>
		</ul>
	</li>
	
	<!-- carte -->
	<li class="<?php echo $clsCarte;?>">
		<a href="#" class="dropdown-toggle">
			<i class="menu-icon fa fa-credit-card"></i>
			<span class="menu-text">
				Carte di Credito
			</span>

			<b class="arrow fa fa-angle-down"></b>
		</a>

		<b class="arrow"></b>

		<ul class="submenu">
							
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'pagamenti_carta', 'action' => 'seleziona_ricorrenti'));?>" title="esegui pagamenti ricorrenti carta">
					<i class="menu-icon fa fa-caret-right"></i>
					Esegui pagamenti ricorrenti
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'carte_di_credito', 'action' => 'index'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Elenco carte
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'carte_di_credito', 'action' => 'index', CARTE_SCADUTE));?>" title="esegui pagamenti ricorrenti carta">
					<i class="menu-icon fa fa-caret-right"></i>
					Carte scadute
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'carte_di_credito', 'action' => 'index', CARTE_NON_ATTIVE));?>" title="esegui pagamenti ricorrenti carta">
					<i class="menu-icon fa fa-caret-right"></i>
					Carte non attivate
				</a>

				<b class="arrow"></b>
			</li>
			
		</ul>
	</li>
	
	<!-- rid -->
	<li class="<?php echo $clsRid;?>">
		<a href="#" class="dropdown-toggle">
			<i class="menu-icon fa fa-bank"></i>
			<span class="menu-text">
				RID
			</span>

			<b class="arrow fa fa-angle-down"></b>
		</a>

		<b class="arrow"></b>

		<ul class="submenu">
							
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'pagamenti_rid', 'action' => 'seleziona_ricorrenti'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Genera flusso RID
				</a>

				<b class="arrow"></b>
			</li>
			
			<!--<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'pagamenti_rid', 'action' => 'analizza_flusso'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Analizza flusso RID
				</a>

				<b class="arrow"></b>
			</li>-->
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'autorizzazioni_rid', 'action' => 'index'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Elenco RID
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'autorizzazioni_rid', 'action' => 'index', RID_NON_ATTIVI));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					RID non attivati
				</a>

				<b class="arrow"></b>
			</li>
			
		</ul>
	</li>
	
	<!-- newsletter -->
	<li class="">
		<a href="#" class="dropdown-toggle">
			<i class="menu-icon fa fa-file-excel-o"></i>
			<span class="menu-text">
				Report
			</span>

			<b class="arrow fa fa-angle-down"></b>
		</a>

		<b class="arrow"></b>

		<ul class="submenu">
				
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'addebiti', 'action' => 'report', 'carte-ko'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Carte KO
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'pagamenti_carta', 'action' => 'report_non_attive'));?>" title="report carte non attive">
					<i class="menu-icon fa fa-excel-o"></i>
					Carte non attive
				</a>

				<b class="arrow"></b>
			</li>

			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'clienti', 'action' => 'report_metodi_pagamento'));?>" title="report metodi di pagamento non attivi sollecitabili/sollecitati">
					<i class="menu-icon fa fa-excel-o"></i>
					Metodi di pagamento non attivi sollecitabili/sollecitati
				</a>

				<b class="arrow"></b>
			</li>	
			
		</ul>
	</li>
	<!--<li class="">
		<a style="height: 80px" href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'clienti', 'action' => 'sollecito_attivazione_metodi_pagamento'));?>" class="">
			<i class="menu-icon fa fa-envelope"></i>
			<span class="menu-text">
				Invia sollecito attivazione metodi pagamento
			</span>
		</a>
	</li>-->
	
	<!-- newsletter -->
	<!--<li class="">
		<a href="#" class="dropdown-toggle">
			<i class="menu-icon fa fa-envelope"></i>
			<span class="menu-text">
				Newsletter
			</span>

			<b class="arrow fa fa-angle-down"></b>
		</a>

		<b class="arrow"></b>

		<ul class="submenu">
				
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'newsletter', 'action' => 'index'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Elenco newsletter
				</a>

				<b class="arrow"></b>
			</li>	
						
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'newsletter', 'action' => 'edit'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Nuova newsletter
				</a>

				<b class="arrow"></b>
			</li>			
			
		</ul>
	</li>-->
	
	<!-- sistema -->
	<li class="">
		<a href="#" class="dropdown-toggle">
			<i class="menu-icon fa fa-cogs"></i>
			<span class="menu-text">
				Area Riservata
			</span>

			<b class="arrow fa fa-angle-down"></b>
		</a>

		<b class="arrow"></b>

		<ul class="submenu">
				
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'clienti', 'action' => 'aggiorna_definizione'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Aggiorna clienti da Zolla
				</a>

				<b class="arrow"></b>
			</li>	
						
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'articoli', 'action' => 'aggiorna_definizione'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Aggiorna articoli da Zolla (con prezzi)
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'articoli', 'action' => 'aggiorna_definizione', '?' => array('prezzi' => 'no')));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Aggiorna articoli da Zolla (senza prezzi)
				</a>

				<b class="arrow"></b>
			</li>
			
			<li class="">
				<a href="<?php echo $this->Html->url(array('plugin' => null, 'controller' => 'articoli', 'action' => 'genera_immagini'));?>" title="">
					<i class="menu-icon fa fa-caret-right"></i>
					Rigenera immagini
				</a>

				<b class="arrow"></b>
			</li>
			
			
		</ul>
	</li>
	
</ul> <!-- /nav-list-->
							
							
