<div class="margin-left-10">
	<h5><?php echo __('TIMELINE');?></h5> 
	
	<?php echo $this->Form->input('timeline', array(
		'id' => 'timeline-job-select',
		'class' => 'chosen-select',
		'empty' => true,
		'label' => false,
		'options' => $jobs,
		'data-baseurl' => Router::url(array('controller' => 'jobs', 'action' => 'gantt_json'))
    ));?>
    <a id="timeline-anchor" href="#" class="hidden"></a> <!-- usata per invocare la funzione ajax -->
	
</div>
