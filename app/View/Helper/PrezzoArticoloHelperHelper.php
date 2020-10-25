<?php

class PrezzoArticoloHelperHelper extends AppHelper 
{
	var $helpers = array('Html','Form');
	
	function visualizzaPrezzo($price)
	{
		$price = str_replace(".",",",$price);
       	if(strpos($price,",")===false) $price .= ',00';
       	else {
			$decimal_part_len = strlen($price)-1-strpos($price,","); //esiste di sicuro per via del precedente controllo
        	if($decimal_part_len > 2) $price = substr($price,0,strlen($price)-($decimal_part_len-2));
			else {
				$num_zeros = 2-$decimal_part_len;
				for($j=0;$j<$num_zeros;$j++) $price .= '0';
			}         
       	}

		return $price.' €';
	}

	//dalla logica usata da zolle
	function visualizzaPrezzoUnitario($articolo)
	{
		if(empty($articolo['Articolo']['CONFEZIONE_UDM'])) return '';
		
		$prezzoVendita = 0;//$this->ArticoloHelper->getArticoloPrezzoVendita($articolo);
		$quantita = str_replace(",",".",$articolo['Articolo']['CONFEZIONE_QUANTITA']); //espresso con separatore decimale ","

		if( strtolower($articolo['Articolo']['CONFEZIONE_UDM']) == 'pz' ) {
			if( empty($articolo['Articolo']['CONFEZIONE_QUANTITA']) || empty($articolo['Articolo']['CONFEZIONE_QUANTITA_UDM']) ) {
				//l'articolo non ha un prezzo/udm
				return '';
			}
			else {
				//occorre calcolare ed esprimere prezzo/udm
				if( strtolower($articolo['Articolo']['CONFEZIONE_QUANTITA_UDM']) == 'g' ) {
					//esprimo in kg (fattore 1000)
					return '('.$this->visualizzaPrezzo( 1000*$prezzoVendita/$quantita ).'/kg)';
				}	
				else if( strtolower($articolo['Articolo']['CONFEZIONE_QUANTITA_UDM']) == 'kg' ) {
					//esprimo in kg
					return '('.$this->visualizzaPrezzo( $prezzoVendita/$quantita ).'/kg)';
				}
				else if( strtolower($articolo['Articolo']['CONFEZIONE_QUANTITA_UDM']) == 'cl' ) {
					//esprimo in l (fattore 100)
					return '('.$this->visualizzaPrezzo( $prezzoVendita/$quantita ).'/l)';
				}
				else if( strtolower($articolo['Articolo']['CONFEZIONE_QUANTITA_UDM']) == 'l' ) {
					//esprimo in l
					return '('.$this->visualizzaPrezzo( $prezzoVendita/$quantita ).'/l)';
				}
				else {
					//ignora (pz - da sistemare)
					return '';
				}
			}
		}
		else if( strtolower($articolo['Articolo']['CONFEZIONE_UDM']) == 'kg' ) {
			
			if( strtolower($articolo['Articolo']['CONFEZIONE_QUANTITA_UDM']) == 'g' ) {
				return '('.$this->visualizzaPrezzo( 1000*$prezzoVendita/$quantita ).'/kg)';
			}
			else if( strtolower($articolo['Articolo']['CONFEZIONE_QUANTITA_UDM']) == 'kg' ) {
				return '('.$this->visualizzaPrezzo($prezzoVendita).'/kg)';
			}
			return '';
		}
		return '';
	}

	function visualizzaPrezzo2($price)
	{
		$p = $this->visualizzaPrezzo($price);
		return $p;
	}

	/*
		su fattoria alcuni campi (= prezzi) sono scritti con la virgola come separatore
	*/
	function getPrezzoAsDouble($price)
	{
		return str_replace(",", ".", $price);
	}
}
