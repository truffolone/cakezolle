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
class MlHelper extends AppHelper {
	
	function getGiornoAperturaMlAsStr($date, $env, $giornoConsegna) {
		$env = strtolower($env);
		$dateAliasStr = null;
		$n = date('N', strtotime($date));
		if( date('Y-m-d', strtotime($date)) == date('Y-m-d') ) {
			$dateAliasStr = "oggi";
		}
		else if( date('Y-m-d', strtotime($date)) == date('Y-m-d', time()+24*60*60) ) {
			$dateAliasStr = "domani";
		}
		return (!empty($dateAliasStr) ? $dateAliasStr : $this->giornoNumToStr($n)).' ore '.Configure::read($env.'.'.$giornoConsegna.'.ml.orario_apertura');
	}
	
	function giornoNumToStr($n) {
		$days[1] = 'Lunedi';
		$days[2] = 'Martedi';
		$days[3] = 'Mercoledi';
		$days[4] = 'Giovedi';
		$days[5] = 'Venerdi';
		$days[6] = 'Sabato';
		$days[7] = 'Domenica';

		return $days[$n];
	}
	
	function giornoNumToShortStr($n) {
		$days[1] = 'lun';
		$days[2] = 'mar';
		$days[3] = 'mer';
		$days[4] = 'gio';
		$days[5] = 'ven';
		$days[6] = 'sab';
		$days[7] = 'dom';

		return $days[$n];
	}
	
	function getDataConsegna($dataConsegna)
	{
		if( $dataConsegna == date('Y-m-d') ) {
			return 'OGGI';
		}
		else if( $dataConsegna == date('Y-m-d', time()+24*60*60) ) {
			return 'DOMANI';
		}
		
		$tokens = explode('-', $dataConsegna);

		$mesi['01'] = 'Gennaio';
		$mesi['02'] = 'Febbraio';
		$mesi['03'] = 'Marzo';
		$mesi['04'] = 'Aprile';
		$mesi['05'] = 'Maggio';
		$mesi['06'] = 'Giugno';
		$mesi['07'] = 'Luglio';
		$mesi['08'] = 'Agosto';
		$mesi['09'] = 'Settembre';
		$mesi['10'] = 'Ottobre';
		$mesi['11'] = 'Novembre';
		$mesi['12'] = 'Dicembre';

		return $this->giornoNumToStr(date('N', strtotime($dataConsegna))).' '.$tokens[2].' '.$mesi[ $tokens[1] ].' '.$tokens[0];
	}
	
}
