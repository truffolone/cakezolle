<?php

	Router::connect('/messaging/:controller', array('plugin' => 'BikesquareMessaging', 'action' => 'index'));
	Router::connect('/messaging/:controller/:action/*', array('plugin' => 'BikesquareMessaging'));
	
	Router::connect('/:language/messaging/:controller', array('plugin' => 'BikesquareMessaging', 'action' => 'index'));
	Router::connect('/:language/messaging/:controller/:action/*', array('plugin' => 'BikesquareMessaging'));

