<?php

class AutorizzazioneRid extends AppModel {

	public $actsAs = array('Containable');

	public $useTable = 'autorizzazioni_rid';

	public $belongsTo = array(
		'Cliente' => array(
			'className' => 'Cliente',
			'foreignKey' => 'cliente_id',
			'dependent' => false,
			//'type' => 'inner'
			'conditions' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
	);
	
	public $validate = array(
		'banca_debitore' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),
			'maxLength' => array(
				'rule' => array('maxLength', '64'),
				'message' => 'max 64 caratteri'
			)
		),
		'agenzia_debitore' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),
			'maxLength' => array(
				'rule' => array('maxLength', '64'),
				'message' => 'max 64 caratteri'
			)
		),
		'check_digit' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),	
			'maxLength' => array(
				'rule' => array('lengthBetween', '2', '2'),
				'message' => 'sono richiesti 2 caratteri'
			),
			'isNumeric' => array(
				'rule' => '/^[0-9]+$/',
				'message' => 'solo cifre'
			),
		),
		'cin' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),	
			'maxLength' => array(
				'rule' => array('lengthBetween', '1', '1'),
				'message' => 'è richiesto 1 carattere'
			),
			'onlyAlpha' => array(
				'rule' => '/^[A-Z]+/',
				'message' => 'solo lettere maiuscole'
			),
		),
		'abi' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),	
			'maxLength' => array(
				'rule' => array('lengthBetween', '5', '5'),
				'message' => 'sono richiesti 5 caratteri'
			),
			'isNumeric' => array(
				'rule' => '/^[0-9]+$/',
				'message' => 'solo cifre'
			),
		),
		'cab' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),	
			'maxLength' => array(
				'rule' => array('lengthBetween', '5', '5'),
				'message' => 'sono richiesti 5 caratteri'
			),
			'isNumeric' => array(
				'rule' => '/^[0-9]+$/',
				'message' => 'solo cifre'
			),
		),
		'conto_corrente' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),	
			'maxLength' => array(
				'rule' => array('lengthBetween', '12', '12'),
				'message' => 'sono richiesti 12 caratteri'
			),
			'onlyAlpha' => array(
				'rule' => '/^[A-Z0-9]+$/',
				'message' => 'solo cifre e caratteri maiuscoli'
			),
			'iban' => array(
				'rule' => 'valid_iban',
				'message' => "L'IBAN inserito non è valido"
			),
		),
		'nome_sottoscrittore' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),	
		),
		'codice_fiscale_sottoscrittore' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),	
		),
		'anagrafica_intestatario' => array(
			'notBlank' => array(
				'rule' => 'notBlank',
				'message' => 'campo obbligatorio'
			),	
		),
	);
	
	function valid_iban($data) {
		
		$iban = 'IT' . 
				$this->data[$this->name]['check_digit'] .
				$this->data[$this->name]['cin'] .
				$this->data[$this->name]['abi'] .
				$this->data[$this->name]['cab'] .
				$this->data[$this->name]['conto_corrente'];
				
		return $this->_checkIBAN($iban);
	}
	
	/** 
	 * per usare bcmod, php deve essere compilato con  il supporto bcmath  
	 * (--enable-bcmath configure option). 
	 * in alternativa può essere usata questa funzione
	 * ref: http://ru.php.net/manual/en/function.bcmod.php 
	 

	 * my_bcmod - get modulus (substitute for bcmod) 
	 * string my_bcmod ( string left_operand, int modulus ) 
	 * left_operand can be really big, but be carefull with modulus :( 
	 * by Andrius Baranauskas and Laurynas Butkus :) Vilnius, Lithuania 
	 **/ 
	function _my_bcmod( $x, $y ) 
	{ 
		// how many numbers to take at once? carefull not to exceed (int) 
		$take = 5;     
		$mod = ''; 

		do 
		{ 
			$a = (int)$mod.substr( $x, 0, $take ); 
			$x = substr( $x, $take ); 
			$mod = $a % $y;    
		} 
		while ( strlen($x) ); 

		return (int)$mod; 
	} 



	/*                                                     */
	function _checkIBAN($iban)
	{
		$iban = strtolower(str_replace(' ','',$iban));
		$Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
		$Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);

		if(strlen($iban) == $Countries[substr($iban,0,2)]){

			$MovedChar = substr($iban, 4).substr($iban,0,4);
			$MovedCharArray = str_split($MovedChar);
			$NewString = "";

			foreach($MovedCharArray AS $key => $value){
				if(!is_numeric($MovedCharArray[$key])){
					$MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
				}
				$NewString .= $MovedCharArray[$key];
			}

			//if(bcmod($NewString, '97') == 1)
			if($this->_my_bcmod($NewString, '97') == 1)
			{
				return TRUE;
			}
			else{
				return FALSE;
			}
		}
		else{
			return FALSE;
		}   
	}
}

 
