<?php
    
class SpesaClienteZolla extends AppModel 
{
	public $useDbConfig = 'zolla';
	
	public $useTable = 'spese_cliente';

	public $actsAs = array('Containable');

} 
