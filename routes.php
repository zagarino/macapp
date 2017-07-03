<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 */

	Router::connect('/sign', array('controller' => 'users', 'action' => 'sign'));
	Router::connect('/facebook', array('controller' => 'users', 'action' => 'facebook'));

    Router::connect('/membership', array('controller' => 'subscriptions', 'action'=> 'index'));
    Router::connect('/membership/:action/*', array('controller' => 'subscriptions'));
	Router::connect('/register/1', array('controller' => 'users', 'action' => 'step1'));
	Router::connect('/register/2', array('controller' => 'users', 'action' => 'step2'));
	Router::connect('/register/3', array('controller' => 'users', 'action' => 'step3'));
	Router::connect('/register/4', array('controller' => 'users', 'action' => 'step4'));
	Router::connect('/admin', array('controller' => 'pages', 'action' => 'panel', 'admin' => true));
	Router::connect('/', array('controller' => 'pages', 'action' => 'dashboard', 'dashboard'));
	Router::connect('/calendar/*', array('controller' => 'pages', 'action' => 'calendar'));
	Router::connect('/profile/*', array('controller' => 'users', 'action' => 'view'));
	Router::connect('/fullscreen', array('controller' => 'pages', 'action' => 'fullscreen'));
	Router::connect('/statistic', array('controller' => 'pages', 'action' => 'statistic'));

	Router::connect('/forgot-your-password', array('controller' => 'users', 'action' => 'forgotPassword'));
	Router::connect('/resend-email', array('controller' => 'users', 'action' => 'resendEmail'));

	Router::connect('/select-program', array('controller' => 'program_children', 'action' => 'program'));
	Router::connect('/select-program/*', array('controller' => 'program_children', 'action' => 'program'));
	Router::connect('/add-race', array('controller' => 'program_children', 'action' => 'add'));

	Router::connect('/reset-your-password', array('controller' => 'users', 'action' => 'resetPassword'));
	Router::connect('/reset-your-password/key/:key', array('controller' => 'users', 'action' => 'resetPassword'), array('key' => '[a-z0-9]+'));

	Router::connect('/verificate-your-email', array('controller' => 'users', 'action' => 'verificateEmail'));
	Router::connect('/verificate-your-email/key/:key', array('controller' => 'users', 'action' => 'verificateEmail'), array('key' => '[a-z0-9]+'));
	//Router::connect('/verificate-your-email-with-key-:key', array('controller' => 'users', 'action' => 'verificateEmail'), array('key' => ' "^[a-zA-Z0-9_]*$"'));
/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
	//Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'dashboard'));

/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
