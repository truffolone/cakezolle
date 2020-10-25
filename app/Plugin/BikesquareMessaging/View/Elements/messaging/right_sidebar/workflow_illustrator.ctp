<div class="margin-left-10">
	<h5><?php echo __('WORKFLOW ILLUSTRATOR');?></h5> 
	
	<?php echo $this->Form->input('workflow_illustrator', array(
		'id' => 'workflow-illustrator-job-select',
		'class' => 'chosen-select',
		'empty' => true,
		'label' => false,
		'options' => $jobs,
		'data-baseurl' => Router::url(array('controller' => 'jobs', 'action' => 'workflow_illustrator_json'))
    ));?>
     <a id="workflow-illustrator-anchor" href="#" class="hidden"></a> <!-- usata per invocare la funzione ajax -->
	
</div>
