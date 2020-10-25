<?php if( $user['group_id'] != CLIENTE_NO_ML ):?>

				<?php
					$env = $this->Session->read('env');
					$cls = $env == 'MLV' ? 'bkg-green' : 'bkg-mlp';
					
					$MlEnv = ClassRegistry::init(ucfirst(strtolower($env)));
					$giornoConsegna = $MlEnv->getCurrGiornoConsegna();
			
					if(empty($giornoConsegna)) {
						$categorieWeb = [];
						$tags = [];	
					}
					else {
						$categorieWeb = ClassRegistry::init('CategoriaWeb')->getAll($env, $giornoConsegna);
						$tags = ClassRegistry::init('TagCategoriaWeb')->getAll($env, $giornoConsegna);
					}
					
					$categoriaWebCorrente = $this->Session->read('categoriaWebCorrente');
				?>

				<div class="<?php echo $cls;?>" id="categorie-ml">
					<div class="panel-body white">
						
						<?php if(!empty($user)):?>
						
						<h4><i class="fa fa-list"></i>  <?php echo $env == 'MLV' ? __("Mercato Libero") : __("Prenotazione Articoli");?></h4>
						<?php if(!empty($categorieWeb)):?>
						<h5 style="line-height:1em; color:  #DFFF5F;"><?php echo __("Categorie");?></h5>
						<ul class="list-group">
							<?php foreach($categorieWeb as $c):?>
							<li class="list-group-item">
								<!-- 2017-03-24: concordato con zolle, rimuovo dalla visualizzazione i badge altrimenti
									se l'opzioni nascondi_esauriti è abilitata dovrei riaggiornare la lista ad ogni caricamento --> 
								<!--<span class="badge"><?php echo $c[0]['num_articoli'];?></span>-->
								<?php echo $this->Html->link($c['CategoriaWeb']['NOME'], array('plugin' => null, 'controller' => 'articoli', 'action' => 'index', 'categoria_web:'.$c['CategoriaWeb']['id']), array('class' => 'white'));?>
								<?php if($c['CategoriaWeb']['id'] == $categoriaWebCorrente):?>
									<?php if(isset($tags[$categoriaWebCorrente])):?>
									<ul class="list-group">
										<?php foreach($tags[$categoriaWebCorrente] as $tagCategoria):?>
										<li class="list-group-item">
											<!-- 2017-03-24: concordato con zolle, rimuovo dalla visualizzazione i badge altrimenti
											se l'opzioni nascondi_esauriti è abilitata dovrei riaggiornare la lista ad ogni caricamento --> 
											<!--<span class="badge"><?php echo $tagCategoria[0]['num_articoli'];?></span>-->
											<?php echo $this->Html->link($tagCategoria['TagCategoriaWeb']['tag'], array('plugin' => null, 'controller' => 'articoli', 'action' => 'index', 'tag:'.$tagCategoria['TagCategoriaWeb']['tag']), array('class' => 'white'));?>
										</li>
										<?php endforeach;?>
									</ul>
									<?php endif;?>
								<?php endif;?>
							</li>
							<?php endforeach;?>
						</ul>
						<?php endif;?>
	
						<?php if(isset($tags[0])):?>
						<h5 style="line-height:1em; color:  #DFFF5F;"><?php echo __("Tag");?></h5>
						<ul class="list-group">
							<?php foreach($tags[0] as $t):?>
							<?php if($t[0]['num_articoli'] > 0):?>
							<li class="list-group-item">
								<!-- 2017-03-24: concordato con zolle, rimuovo dalla visualizzazione i badge altrimenti
									se l'opzioni nascondi_esauriti è abilitata dovrei riaggiornare la lista ad ogni caricamento --> 
								<!--<span class="badge"><?php echo $t[0]['num_articoli'];?></span>-->
								<?php echo $this->Html->link($t['TagCategoriaWeb']['tag'], array('plugin' => null, 'controller' => 'articoli', 'action' => 'index', 'tag:'.$t['TagCategoriaWeb']['tag']), array('class' => 'white'));?>
							</li>
							<?php endif;?>
							<?php endforeach;?>
						</ul>
						<?php endif;?>
						
						<?php endif;?>
					</div>
				</div>	
<?php endif;?> 
