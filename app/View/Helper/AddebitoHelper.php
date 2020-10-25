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
class AddebitoHelper extends AppHelper {
	
	public function getStato($addebito) {
		$result = '';
		
		$esito = $addebito['last_payment_ok'];
		
		if($addebito['metodo_pagamento_id'] == 1) { // carta
			if($esito == -1) {
				return '<span class="yellow"><b>Nessun pagamento eseguito</b></span>';
			}
			elseif($esito == 0) {
				return '<span class="red"><b>KO</b></span>';
			}
			elseif($esito == 1) {
				return '<span class="green"><b>OK</b></span>';
			}
			else {
				return 'n.d.';
			}
		}
		else return 'n.d.';
	}
	
}
