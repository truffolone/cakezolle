	<div class="row">
		<?php
			$base_url = FULL_BASE_URL;
			if( strpos($base_url, "staging") !== FALSE ) {
				// sono sull'ambiente di test, setto di default zolle.impronta48.it per fare vedere le immagini prese dall'ambiente di produzione
				$base_url = 'https://zolle.impronta48.it';
			}
			$img_url = $base_url . $this->webroot.'img/areariservata/categorie/cropped/box/'.$categoria['CategoriaWeb']['id'].'.jpg';
			$no_image_url = $base_url . $this->webroot.'img/areariservata/categorie/no-image.jpg';
		?>
		<div class="box-categoria" style="background-image:url(<?php echo $img_url;?>), url(<?php echo $no_image_url;?>)">
			<div class="img col-xs-5"></div>
			<div class="dettagli col-xs-7">
				<h4>
					<?php echo $this->Html->link($categoria['CategoriaWeb']['NOME'], array('controller' => 'articoli', 'action' => 'index', 'categoria_web:'.$categoria['CategoriaWeb']['id']), array('class' => 'link'));?>
					<!--<span class="badge"><?php echo $categoria[0]['num_articoli'];?></span>-->
				</h4>
			</div>
		</div>
	</div>
