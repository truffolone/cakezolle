<?php echo $this->Html->script('mangio/ml.1-1', array('inline' => FALSE));?>

<?php $searchKeyword = $this->Session->read('searchKeyword');?>

<?php if(empty($articoli)):?>

	<div class="text-center">
		<?php echo $searchKeyword == null ? 
			__('Attualmente non ci sono articoli disponibili (potrebbero tornare disponibili in futuro)') :
			__("Nessun articolo trovato con questa ricerca")
		;?>
	</div>

<?php else:?>

<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
  <?php $index = 0;?>
  <?php foreach($articoli as $categoriaWeb => $articoliInCategoria):?>
  <?php $index++;?>
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="heading<?php echo $index;?>">
      <h4 class="panel-title">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $index;?>" aria-expanded="true" aria-controls="collapseOne">
          <?php echo $categoriaWeb;?>
        </a>
      </h4>
    </div>
    <div id="collapse<?php echo $index;?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading<?php echo $index;?>">
      <div class="panel-body" style="padding:0">
        <?php foreach($articoliInCategoria as $articolo):?>
			<?php echo $this->element('mangio/box_articolo_compact', array(
				'articolo' => $articolo
			));?>
        <?php endforeach;?>
      </div>
    </div>
  </div>
  <?php endforeach;?>
</div> 

<?php endif;?>
