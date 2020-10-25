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
class MetodoPagamentoHelper extends AppHelper {
	
	public function getStato($tipo, $item) {
		$stato = '';
		
		switch($tipo) {
			case CARTA:
				if(empty($item['sent'])) {
					$stato = '<span class="red">Contratto carta non inviato</span>';
				} 
				elseif(empty($item['signed'])) {
					$stato = '<span class="red">Carta non ancora attiva</span>';
				}
				elseif(empty($item['adyen_psp_reference'])) {
					$stato = '<span class="yellow"><b>Carta in attesa di passaggio ad Adyen</b></span>';
				}
				else {
					$stato = '<span class="green">Abilitato</span>';
				}
				break;
			case RID:
				if(empty($item['rid_sent'])) {
					$stato = '<span class="red">Contratto RID non inviato</span>';
				}
				elseif(empty($item['rid_filled'])) {
					$stato = '<span class="red">Autorizzazione RID in attesa di compilazione</span>';
				}
				elseif(empty($item['rid_activated'])) {
					$stato = '<span class="red">Autorizzazione RID compilata in attesa di conferma autorizzazione dalla banca</span>';
				}
				else {
					$stato = '<span class="green">Abilitato</span>';
				}
				break;
			default:
				$stato = '<span class="green">OK</span>';
		}
		
		return $stato;
	}
	
	/**
	 * 
	 */
	public function getClienteDisplayStr($cliente) {
		return $cliente['id'].' - '.$cliente['COGNOME'].' '.$cliente['NOME'];
	}
	
}
