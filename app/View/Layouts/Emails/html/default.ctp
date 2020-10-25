<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts.Email.html
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title><?php echo $this->fetch('title'); ?></title>
</head>
<body>

<!--100% body table-->
<div id="main" style="padding:10px; width:620px;margin:auto;border: 1px solid #333;">
	<div id="header" style="padding:10px; width:600px;height:90px;">
		<h1 style="color: #920071; margin: 0px; font-weight: normal;font-size: 30px; font-family: VAG rounded, Arial,sans-serif;float:left;clear:left;width:200px;">
            <?=$this->Html->image('logo_zolle.png', [
				'fullBase' => true, 
                'moz-do-not-send' => true,
                'style' => "margin:2px; padding: 4px; background: #fff; width:150px; height:95px; margin:auto;"
            ]);?>
        </h1>
 	</div>
    <div id="container" style="padding:10px; width:600px; min-height:650px;">

   		<?php echo $this->fetch('content'); ?>

    </div>   
 
    <div id="footer" style="padding:10px; width:600px; float:left; clear: both; height: 40px; margin:auto;">
    	<p style="padding-left:10px;color:#333;float:left;width:47%;">
    	</p>
    	<p style="padding-left:10px;color:#333;float:left;width:47%;">
            <a moz-do-not-send="true" href="http://www.facebook.com/Zolle.it?ref=hl" target="_blank" style="float:right;margin-left:20px;color:#333;">
                <?=$this->Html->image('soc-img-facebook.png', ['fullBase' => true, 'moz-do-not-send' => true]);?>
			</a>
			<!--<a moz-do-not-send="true" href="skype:zolle.zolle?call" style="float:right;margin-left:20px;color:#333;">
              <img moz-do-not-send="true" src="http://www.zolle.it/web/wp-content/themes/zolle/images/soc-img-skype.png">
			</a>-->
            <a moz-do-not-send="true" href="http://www.zolle.it" style="float:right;margin-left:20px;color:#333;">
                <?=$this->Html->image('soc-img-infozolle.png', ['fullBase' => true, 'moz-do-not-send' => true]);?>
			</a>
		</p>
	</div>
</div>
<!--fine main-->

</body>
</html>
