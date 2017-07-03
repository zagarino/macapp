<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.  *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	public $components = array
	(
		'Security' => array(
			//'csrfExpires' => '1 second'
		),
		'Paginator' => array ('limit' => 10),
		'Cookie',
		'Session',
		//'Session' => array ('className' => 'BootstrapSession'),
		'Auth' => array(
			'loginAction' => array(
				'controller' => 'users',
				'action' => 'sign',
				'admin' => false
			),
			//'flash' => array(
				//'element' => 'alert',
				//'key' => 'auth',
				//'params' => array(
					//'plugin' => 'BoostCake',
					//'class' => 'alert-info'
				//)
			//),
			'loginRedirect' => array(
				'controller' => 'pages',
				'action' => 'dashboard'
			),
			'logoutRedirect' => array(
				'controller' => 'users',
				'action' => 'sign',
			),
			'authenticate' => array(
				'Form' => array(
					'passwordHasher' => 'Blowfish',
					'fields' => array('username' => 'email', 'password' => 'password'),
					//'scope' => array('User.email_verified' => 1),
				)
			),
			'authorize' => 'Controller'

		),
		'DebugKit.Toolbar'
	);
	public function isAuthorized($user)
	{

		//Any registered user can access public functions
		if (empty($this->request->params['admin']))
		{
			return true;
		}
		// Only admins can access admin functions
		if (isset($this->request->params['admin']))
		{
			return (bool)($user['Group']['value'] === 'admin');
		}

		// Default deny
		return false;
	}

	public $helpers = array(
			'Session',
			'Html' => array('className' => 'BoostCake.BoostCakeHtml'),
			'Form' => array('className' => 'BoostCake.BoostCakeForm'),
			'Paginator' => array('className' => 'BoostCake.BoostCakePaginator'),
			'Iqio',
	);

	public function forceSSL($type)
	{
		$this->redirect('https://' . env('SERVER_NAME') . $this->here);
	}

	public function blackhole($type)
	{
		$this->redirect('https://' . env('SERVER_NAME') . $this->here);
	}



	public function beforeFilter()
	{
		$this->Security->blackHoleCallback = 'blackhole';

		if($this->Session->check('Access'))
		{
			$this->set('access', true);
		}

		if (!isset($_SERVER['HTTPS']))
		{
			$this->Security->blackHoleCallback = 'forceSSL';
			$this->Security->requireSecure();
		}

		$auth = array();
		$dob = new DateTime($this->Auth->user('date_of_birth'));
		$now = new DateTime();
		$age = $now->diff($dob);

        $auth = $this->Auth->user();
        $auth['age'] = $age;
        $auth['admin'] = (bool)($this->Auth->user('Group.value') === 'admin');

		$this->set('auth', $auth);

		if($this->Cookie->read('fullscreen'))
		{
			$this->set('fullscreen',1);
		}
		else
		{
			$this->set('fullscreen',0);
		}
		$this->Auth->allow('login', 'logout');
		$this->Cookie->httpOnly = true;
		$cookie = $this->Cookie->read('rememberMe');

		if (!$this->Auth->loggedIn() && $this->Cookie->read('rememberMe'))
		{
			$cookie = $this->Cookie->read('rememberMe');

			$this->loadModel('User'); // If the User model is not loaded already
			$user = $this->User->find('first', array
			(
				'conditions' => array
				(
					'User.user_name' => $cookie
				)
			));

			if ($user && !$this->Auth->login($user['User']))
			{
				$this->redirect(array('controller' => 'users', 'action' => 'logout')); // destroy session & cookie
			}
		}
	}

    public function isSubscribed()
    {
        $this->loadModel('User');
        return $this->User->isSubscribed();
    }

    public function canTryout()
    {
        $this->loadModel('User');
        return $this->User->canTryout();
    }

    public function hasFreeCharge()
    {
        $this->loadModel('User');
        return $this->User->hasFreeCharge();
    }

    public function checkUserAccess()
    {
        if ($this->hasFreeCharge()) {
            return true;
        }
        if (!$this->isSubscribed()) {
            if (!$this->canTryout() && !$this->User->isTryingout()) {
    			$this->Session->setFlash(__('Uhoh, it seems  that your subscription is not active, please subscribe now!'), 'alert', array(
    				'plugin' => 'BoostCake',
    				'class' => 'alert-danger'
    			));

                return false;
            }
        }

        return true;
    }
}
