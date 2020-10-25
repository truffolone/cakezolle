<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after core.php
 *
 * This file should load/create any application wide configuration settings, such as
 * Caching, Logging, loading additional configuration files.
 *
 * You should also use this file to include any files that provide global functions/constants
 * that your application uses.
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
 * @package       app.Config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

// Setup a 'default' cache configuration for use in the application.
Cache::config('default', array('engine' => 'File'));



/**
 * The settings below can be used to set additional paths to models, views and controllers.
 *
 * App::build(array(
 *     'Model'                     => array('/path/to/models/', '/next/path/to/models/'),
 *     'Model/Behavior'            => array('/path/to/behaviors/', '/next/path/to/behaviors/'),
 *     'Model/Datasource'          => array('/path/to/datasources/', '/next/path/to/datasources/'),
 *     'Model/Datasource/Database' => array('/path/to/databases/', '/next/path/to/database/'),
 *     'Model/Datasource/Session'  => array('/path/to/sessions/', '/next/path/to/sessions/'),
 *     'Controller'                => array('/path/to/controllers/', '/next/path/to/controllers/'),
 *     'Controller/Component'      => array('/path/to/components/', '/next/path/to/components/'),
 *     'Controller/Component/Auth' => array('/path/to/auths/', '/next/path/to/auths/'),
 *     'Controller/Component/Acl'  => array('/path/to/acls/', '/next/path/to/acls/'),
 *     'View'                      => array('/path/to/views/', '/next/path/to/views/'),
 *     'View/Helper'               => array('/path/to/helpers/', '/next/path/to/helpers/'),
 *     'Console'                   => array('/path/to/consoles/', '/next/path/to/consoles/'),
 *     'Console/Command'           => array('/path/to/commands/', '/next/path/to/commands/'),
 *     'Console/Command/Task'      => array('/path/to/tasks/', '/next/path/to/tasks/'),
 *     'Lib'                       => array('/path/to/libs/', '/next/path/to/libs/'),
 *     'Locale'                    => array('/path/to/locales/', '/next/path/to/locales/'),
 *     'Vendor'                    => array('/path/to/vendors/', '/next/path/to/vendors/'),
 *     'Plugin'                    => array('/path/to/plugins/', '/next/path/to/plugins/'),
 * ));
 *
 */

/**
 * Custom Inflector rules can be set to correctly pluralize or singularize table, model, controller names or whatever other
 * string is passed to the inflection functions
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */
Inflector::rules('plural', array('rules' => array(), 'irregular' => array(
																		'cliente' => 'clienti',
																		'contratto' => 'contratti',
																		'metodo_pagamento' => 'metodi_pagamento',
																		'recapito' => 'recapiti',
																		'indirizzo' => 'indirizzi',
																		'carta_di_credito' => 'carte_di_credito',
																		'autorizzazione_rid' => 'autorizzazioni_rid',
																		'bonifico' => 'bonifici',
																		'procedura_legale' => 'procedure_legali',
																		'contante' => 'contanti',
																		'articolo' => 'articoli',
																		'prodotto' => 'prodotti',
																		'fornitore' => 'fornitori',
																		'articolo_prezzo' => 'articoli_prezzi',
																		'categoria_web' => 'categorie_web',
																		'sottocategoria' => 'sottocategorie',
																		'addebito' => 'addebiti',
																		'pagamento_carta' => 'pagamenti_carta',
																		'pagamento_rid' => 'pagamenti_rid',
																		'destinatario_newsletter' => 'destinatari_newsletter'
																		),
														'uninflected' => array(
																		'tag_categoria_web',
																		'articolo_disponibilita',
																		'articolo_venduto'
														)
));

/**
 * Plugins need to be loaded manually, you can either load them one by one or all of them in a single call
 * Uncomment one of the lines below, as you need. Make sure you read the documentation on CakePlugin to use more
 * advanced ways of loading plugins
 *
 * CakePlugin::loadAll(); // Loads all plugins at once
 * CakePlugin::load('DebugKit'); //Loads a single plugin named DebugKit
 *
 */

/**
 * To prefer app translation over plugin translation, you can set
 *
 * Configure::write('I18n.preferApp', true);
 */

/**
 * You can attach event listeners to the request lifecycle as Dispatcher Filter. By default CakePHP bundles two filters:
 *
 * - AssetDispatcher filter will serve your asset files (css, images, js, etc) from your themes and plugins
 * - CacheDispatcher filter will read the Cache.check configure variable and try to serve cached content generated from controllers
 *
 * Feel free to remove or add filters as you see fit for your application. A few examples:
 *
 * Configure::write('Dispatcher.filters', array(
 *		'MyCacheFilter', //  will use MyCacheFilter class from the Routing/Filter package in your app.
 *		'MyCacheFilter' => array('prefix' => 'my_cache_'), //  will use MyCacheFilter class from the Routing/Filter package in your app with settings array.
 *		'MyPlugin.MyFilter', // will use MyFilter class from the Routing/Filter package in MyPlugin plugin.
 *		array('callable' => $aFunction, 'on' => 'before', 'priority' => 9), // A valid PHP callback type to be called on beforeDispatch
 *		array('callable' => $anotherMethod, 'on' => 'after'), // A valid PHP callback type to be called on afterDispatch
 *
 * ));
 */
Configure::write('Dispatcher.filters', array(
	'AssetDispatcher',
	'CacheDispatcher'
));

/**
 * Configures default file logging options
 */
App::uses('CakeLog', 'Log');
CakeLog::config('debug', array(
	'engine' => 'File',
	'types' => array('notice', 'info', 'debug'),
	'file' => 'debug',
));
CakeLog::config('error', array(
	'engine' => 'File',
	'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
	'file' => 'error',
));

CakeLog::config('zolle', array(
    'engine' => 'Database',
    'model' => 'LogEntry',
    'types' => array('INFO', 'WARNING', 'ERROR') // diversi da quelli standard in modo che il loggin avvenga solo su questo handler
    // ...
));



CakePlugin::load('AclExtras');

CakePlugin::load('BikesquareMessaging', array('routes' => true, 'bootstrap' => true));


// ruoli Zolle (da tenere aggiornati cn quelli in 'aros')
define('CAKE_ADMIN', 1);
define('AMMINISTRATORE', 2);
define('OPERATORE', 3);
define('CLIENTE_STANDARD', 4);
define('CLIENTE_SENZA_OPERATIVITA', 5);
define('CLIENTE_BASSA_OPERATIVITA', 6);
define('CLIENTE_NO_ML', 7);

define('TEST', false);

define('CARTA', 1);
define('RID', 2);
define('BONIFICO', 3);
define('CONTANTI', 4);
define('PROCEDURA_LEGALE', 5);

define('NON_RICORRENTE', 0);
define('PRIMO_PAGAMENTO', 1);
define('RICORRENTE', 2);
define('RICORRENTE_NON_CONFERMATO', 3); // usata in fase di upload spese

define('INVIA_MAIL', true); // per invio email lato sistema pagamenti

define('REST_PASS', 'xyz');

define('ADDEBITI_ALL', 0);
define('ADDEBITI_CARTE_OK', 1);
define('ADDEBITI_CARTE_KO', 12);
define('ADDEBITI_CARTE_PAGABILI', 2);
define('ADDEBITI_CARTE_NON_PAGABILI', 3);
define('ADDEBITI_CARTE_BLOCCATE', 4);
define('ADDEBITI_RID_PAGABILI', 5);
define('ADDEBITI_RID_NON_PAGABILI', 6);
define('ADDEBITI_BONIFICO', 7);
define('ADDEBITI_CONTANTE', 8);
define('ADDEBITI_LEGALE', 9);
define('ADDEBITI_CARICATI_DA_CONFERMARE', 10);
define('ADDEBITI_NON_ATTIVI', 11);

define('CARTE_TUTTE', 0);
define('CARTE_SCADUTE', 1);
define('CARTE_NON_ATTIVE', 2);

define('RID_TUTTI', 0);
define('RID_NON_ATTIVI', 1);

define('CONFERMA', 1);
define('ABBANDONA', 0);

define('CONSEGNE_TUTTE', 0);
define('CONSEGNE_PROSSIMO_ML', 1);
define('CONSEGNE_ML_E_DOPO_ML_READONLY', 2);

define('ADYEN_APPLICATION_NAME', 'Zolle Pagamenti ricorrenti');
define('ADYEN_USERNAME', "pippo");
define('ADYEN_API_KEY', "pippo");
define('ADYEN_PASSWORD', "pippo");
require_once(ROOT . DS . 'vendor' . DS  . 'adyen' . DS . 'php-api-library' . DS . 'src' . DS . 'Adyen' . DS . 'Environment.php');
define('ADYEN_ENVIRONMENT', \Adyen\Environment::LIVE); // test o live
define('ADYEN_MERCHANT', 'ZolleIT');

Configure::write('Asset.timestamp', 'force'); // aggiunge la versione a css e js (force per averli anche in debug mode altrimenti basta true)

define('USER_ROLE_KEY', 'group_id'); // per il messaging
define('ROLE_ADMIN', 1); // per il messaging
define('ROLE_BAM', 100); // per il messaging
define('ROLE_RENTER', 100); // per il messaging
define('ROLE_EON_PLUS', 100); // per il messaging

Configure::write('Exception', array(
    'handler' => 'ErrorHandler::handleException',
    'renderer' => 'ExceptionRenderer',
    'log' => true,
    'skipLog'=>array(
        'MissingControllerException', // stufo di robots.txt e tutta la porcheria di 404 che logga in automatico ...
        //'MissingActionException'
    )
));

// custom logging streams per le mail
CakeLog::config('emails_info', array(
	'engine' => 'File',
	'types' => array('emails_info'),
	'file' => 'emails_info',
));
CakeLog::config('emails_error', array(
	'engine' => 'File',
	'types' => array('emails_error'),
	'file' => 'emails_error',
));

define('CHAT_ADMIN', 'mangio@zolle.it');
