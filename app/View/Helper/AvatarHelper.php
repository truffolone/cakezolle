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
class AvatarHelper extends AppHelper {
	
	public $helpers = ['User'];
	
	public function getAvatar($user) {
		if( $user == null ) { // utente collegato al messaggio non esiste più ...
			$user = [
				'id' => 0,
				'username' => 'Missing User'
			];
		}
		
		if(is_numeric($user)) {
			$user= ['id' => $user];
		}
		
		if( $user['id'] < 0 ) { // utenti bikesquare speciali
			$imagePath = 'zolle-logo2.png';
		}
		else {
			$uid = $user['id'];
			$avatarPath = IMAGES . "profiles/$uid.png";
			if (!file_exists($avatarPath)) {
				$displayName = $this->User->displayName($uid);
				// genera avatar e memorizzalo
				$bkg = ['1abc9c', '2ecc71', '3498db', '9b59b6', '34495e', 'f1c40f', 'e67e22', 'e74c3c'][ rand(0,7) ];
				$name = str_replace(' ', '+', $displayName);
				file_put_contents($avatarPath, file_get_contents( "https://ui-avatars.com/api/?name=$name&background=$bkg&color=fff&size=128" ) );
			}
			$imagePath = "profiles/$uid.png";
		}
		
		return $imagePath;
	}
	
}
