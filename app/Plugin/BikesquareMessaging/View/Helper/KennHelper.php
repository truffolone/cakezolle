<?php
/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Helper', 'View');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class KennHelper extends AppHelper {
	
	public $helpers = array('Html', 'Number');
	
	/**
	 * 
	 */
	public function adjustBrightness($hex, $steps) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) {
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $return;
	}
	
	/**
	 * wrapper per gestire ogni possibile caso (es. se fosse necessario aggiungere anche il fuso orario)
	 * 
	 * @param aTime un timestamp o una data
	 */
	public function niceDate($aTime) {
		if(empty($aTime)) return ''; // importante!
		
		if( !is_numeric($aTime) ) { // è già una data
			$aTime = strtotime($aTime);
		}
		return date(KN_DATE_FORMAT, $aTime);
	}
	
	/**
	 * wrapper per gestire ogni possibile caso (es. se fosse necessario aggiungere anche il fuso orario)
	 * 
	 * @param aTime un timestamp o una data
	 */
	public function niceDatetime($aTime, $format=null) {
		if(empty($aTime)) return ''; // importante!
		
		if( !is_numeric($aTime) ) { // è già una data
			$aTime = strtotime($aTime);
		}
		if($format) return date($format, $aTime);
		else return date(KN_DATETIME_FORMAT, $aTime);
	}
	
	/**
	 * 
	 */
	public function getSiglaFornitore($persona) {
		$sigla = empty($persona['Fornitore']['sigla']) ? "" : $persona['Fornitore']['sigla'];
		if(empty($sigla)) {
			$sigla = "";
			$words = explode(" ", $persona['DisplayName']);

			foreach ($words as $w) {
				$sigla .= strlen($w) > 0 ? strtoupper(substr($w,0,1)) : "";
			}
		}
		return $sigla;
	}
	
	
	public function clienteGetSigla($persona) {
		$sigla = empty($persona['Cliente']['sigla']) ? "" : $persona['Cliente']['sigla'];
		if(empty($sigla)) {
			$sigla = "";
			$words = explode(" ", $persona['DisplayName']);

			foreach ($words as $w) {
			  $sigla .= strlen($w) > 0 ? strtoupper(substr($w,0,1)) : "";
			}
		}
		return $sigla;
	}
	
	public function clienteGetSiglaFromStrings($displayName, $sigla) {
		return $this->clienteGetSigla(array(
			'DisplayName' => $displayName,
			'Cliente' => array(
				'sigla' => $sigla
			)
		));
	}
	
	/**
	 * 
	 */
	public function crumbs($crumbs) {
		$html = '';
		foreach($crumbs as $crumb) {
			if($crumb[2]) {
				$html .= '<li class="active">
					'.$crumb[0].'
				</li>';
			}
			else {
				$html .= '<li>
					<a href="'.$crumb[1].'">'.$crumb[0].'</a>
				</li>';
			}
		}
		return $html;
	}
	
	/**
	 * 
	 */
	public function getUserDisplayName($user) {
		if( isset($user['username']) ) {
			return $user['username']; // uso il first name come display name
		}
		else if( isset($user['Persona']) ) {
			$displayName = $user['Persona']['DisplayName'];
			if(!empty(trim($displayName))) {
				return $displayName; 
			}
			return $user['Persona']['Nome'].' '.$user['Persona']['Cognome'];
		}
		else { // WTF ???
			return '';
		}
	}
	
	/**
	 * usato nel messaging
	 */
	public function getUserSymbol($user) {
		$groups = explode(',', $user[USER_ROLE_KEY]);
		if( in_array(ALGORITMO_GROUP_ID, $groups) ) {
			$cls = 'user-symbol-primary';
		}
		else {
			$cls = 'user-symbol-default';
		}
		
		// build symbol (prima lettera di first name e last name, se uno è vuoto prendo 2 lettere da quello non vuoto
		if( !empty($user['first_name']) && !empty($user['last_name']) ) {
			$symbol = substr($user['first_name'], 0, 1).substr($user['last_name'], 0, 1);
		}
		else {
			if( !empty($user['first_name']) ) $symbol = substr($user['first_name'], 0, 2);
			else $symbol = substr($user['last_name'], 0, 2);
		}
		if( strlen($symbol) < 2 ) $symbol .= '0'; // aggiungo uno zero
		
		return '<div class="user-symbol '.$cls.'">'.$symbol.'</div>';
	}
	
	/**
	 * 
	 */
	public function getIdenticon($id, $size, $cssClass) {
		
		$webrootURL = Router::url('/', true);
		if( strpos($webrootURL, 'index.php/') !== false ) $webrootURL = substr($webrootURL, 0, strlen($webrootURL)-10);
		
		return '<img class="'.$cssClass.'" src="'.$webrootURL.'webroot/identicon.php?size='.$size.'&hash='.sha1($id).'" />';
		
	}
	
	/**
	 * restituisce il blocco con la bandierina source
	 * 
	 * @param source variante source
	 */
	public function source($source) {
		
		$flagSource = empty($source['Country']['id']) ? '' : $source['Country']['a2'];
								
		$linguaSource = empty($source['Lingua']['id']) ? __('n.a.') : $source['Lingua']['name'];   
		
		return
			$this->flag($flagSource, 'kn-flag kn-flag-left').
			'&nbsp;&nbsp;'.
			'<span class="lingua-source">'.$linguaSource.'</span>'.
			'&nbsp;&nbsp;&nbsp;'.
			'<i class="fa fa-long-arrow-right"></i>';
	}
	
	/**
	 * restituisce il blocco con la bandierina target
	 * 
	 * @param target variante target
	 */
	public function target($target) {
		
		$flagTarget = empty($target['Country']['id']) ? '' : $target['Country']['a2'];
								
		$linguaTarget = empty($target['Lingua']['id']) ? __('n.a.') : $target['Lingua']['name'];   
		
		return
			'<span class="lingua-target">'.$linguaTarget.'</span>'.
			'&nbsp;&nbsp;'.
			$this->flag($flagTarget, 'kn-flag kn-flag-right');
	}
	
	/**
	 * restituisce il blocco con le bandierine source target
	 * 
	 * @param source variante source
	 * @param target variante target
	 */
	public function sourceTarget($source, $target) {
		
		$flagSource = empty($source['Country']['id']) ? '' : $source['Country']['a2'];
		$flagTarget = empty($target['Country']['id']) ? '' : $target['Country']['a2'];
								
		$linguaSource = empty($source['Lingua']['id']) ? __('n.a.') : $source['Lingua']['name']; 
 		$linguaTarget = empty($target['Lingua']['id']) ? __('n.a.') : $target['Lingua']['name'];   
		
		return
			$this->flag($flagSource, 'kn-flag kn-flag-left').
			'&nbsp;&nbsp;'.
			'<span class="lingua-source">'.$linguaSource.'</span>'.
			'&nbsp;&nbsp;&nbsp;'.'
			<i class="fa fa-long-arrow-right"></i>'.
			'&nbsp;&nbsp;&nbsp;'.
			'<span class="lingua-target">'.$linguaTarget.'</span>'.
			'&nbsp;&nbsp;'.
			$this->flag($flagTarget, 'kn-flag kn-flag-right');
	}
	
	/**
	 * restituisce il blocco con le bandierine source target - VERSIONE RIDOTTA IN LARGHEZZA (con meno spazi - per il messaging)
	 * 
	 * @param source variante source
	 * @param target variante target
	 */
	public function sourceTargetSmall($source, $target) {
		
		$flagSource = empty($source['Country']['id']) ? '' : $source['Country']['a2'];
		$flagTarget = empty($target['Country']['id']) ? '' : $target['Country']['a2'];
								
		$linguaSource = empty($source['Lingua']['id']) ? __('n.a.') : $source['Lingua']['name']; 
 		$linguaTarget = empty($target['Lingua']['id']) ? __('n.a.') : $target['Lingua']['name'];   
		
		return
			$this->flag($flagSource, 'kn-flag kn-flag-left').
			'&nbsp;'.
			'<span class="lingua-source">'.$linguaSource.'</span>'.
			'<i class="fa fa-long-arrow-right"></i>'.
			'<span class="lingua-target">'.$linguaTarget.'</span>'.
			'&nbsp;'.
			$this->flag($flagTarget, 'kn-flag kn-flag-right');
	}
	
	/**
	 * 
	 */
	public function flag($flag, $class) {
		$flag = strtolower($flag); // mandatory!
		if( empty($flag) ) $flag = 'unknown';
		$flagPath = str_replace('//', '/', APP . 'webroot' . DS . 'img' . DS . 'flags' . DS . '1x1' . DS . $flag . '.png');
		if( !file_exists($flagPath) ) $flag = 'unknown';
		return $this->Html->image('flags/1x1/'.strtolower($flag).'.png', array('class' => $class));
	}
	
	/**
	 * isTariffa:  le tariffe possono andare fino a 3 decimali
	 */
	public function currency($number, $valuta, $isTariffa=false) {
		$currencySymbol = empty($valuta['symbol']) ? $valuta['name'] : $valuta['symbol'];
		
		// per uno strano problema (encoding?) in produzione il simbolo dell'euro non si vede, 
		// in quel caso lo rimuovo (tanto i valori sono in euro di default)
		if($valuta['name'] == 'EUR') $currencySymbol = NULL;
		
		if($isTariffa) $options = array('places' => 3);
		else $options = array();
		
		return $this->Number->currency($number, $currencySymbol, $options);
		
	}
	
	/**
	 * 
	 */
	public function getFontAwesomeIcon($path) {
		
		$ext = strtolower( pathinfo($path, PATHINFO_EXTENSION) );
		
		switch($ext) {
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'svg':
				$icon = 'file-image-o';
				break;
				
			case 'xls':
			case 'ods':
			case 'xlsx':
			case 'csv':
				$icon = 'file-excel-o';
				break;
				
			case 'pdf':
				$icon = 'file-pdf-o';
				break;
				
			case 'rft':
			case 'odt':
			case 'doc':
			case 'docx':
			case 'txt':
				$icon = 'file-word-o';
				break;
				
			case 'zip':
			case 'rar':
			case '7z':
			case 'tar':
			case 'gz':
				$icon = 'file-archive-o';
				break;
				
			case 'mp3':
			case 'wav':
			case 'flac':
				$icon = 'file-audio-o';
				break;
				
			case 'mp4':
			case 'mpg':
			case 'mpeg':
			case 'avi':
			case 'flv':
				$icon = 'file-movie-o';
				break;
			
			case 'odp':
			case 'ppt':
			case 'pptx':
				$icon = 'file-powerpoint-o';
				break;
			
			default:
				$icon = 'file-o';
		}
		
		return $icon;
	}
	
}
