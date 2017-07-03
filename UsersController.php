<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::uses('Validation', 'Utility');
/**
 * Users Controller
 *
 * @property User $User
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class UsersController extends AppController
{

	/**
	 * Components
	 *
	 * @var array
	 */
	public $components = array('Session', 'Captcha');

	public $steps = array(
		'current' => null,
		'progress' => null,
		'max' => 4,
	);

	public function beforeFilter()
	{
		parent::beforeFilter();
		// Allow users to register and logout.
		$this->Auth->allow('affegiraffe', 'terms', 'sign', 'facebook', 'step1', 'step2', 'step3', 'step4', 'step5', 'start', 'verificateEmail', 'resendEmail', 'forgotPassword', 'resetPassword');
		$this->Security->unlockedActions = array('affegiraffe');
        //$this->Security->unlockedActions = array('logintest');
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$steps = $this->Session->read('registration.steps.current');
		$this->set('steps', $steps);
	}

	public function start()
	{
		$this->Session->delete('registration');

		$this->Session->write('registration.params.maxProgress', 1);
		$this->Session->write('registration.params.stepCurrent', 1);

		$this->redirect(array('action' => 'step1'));
	}

	public function step1()
	{
		$this->layout = 'auth';

		if ($this->Auth->user())
		{
			$this->redirect(array('controller' => 'pages', 'action' => 'dashboard', 'admin' => false));
		}
		elseif (is_int($this->Session->read('registration.params.maxProgress')))
		{
			$this->set('maxProgress', $this->Session->read('registration.params.maxProgress'));
		}
		else
		{
			$this->redirect(array('action' => 'start'));
		}

		$this->Session->write('registration.params.stepCurrent', 1);
		$this->set('stepCurrent', $this->Session->read('registration.params.stepCurrent'));

		if ($this->request->is('post'))
		{
			$this->User->set($this->request->data);

			if ($this->User->validates())
			{
				$this->Session->write('registration.params.maxProgress', 2);

				$prevSessionData = $this->Session->read('registration.data');
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$this->Session->write('registration.data', $currentSessionData);

				$this->redirect(array('action' => 'step2'));
			}
		}
		else
		{
			$this->request->data = $this->Session->read('registration.data');
		}
	}

	public function step2()
	{
		$this->_step(2);

		$captcha = $this->Captcha->create();
		$this->set('captcha', $captcha);
	}

	public function step3()
	{
		$this->Captcha->delete();

		$genders = $this->User->Gender->find('list');
		$countries = $this->User->Country->find('list');
		$this->set(compact('genders', 'countries'));

		$this->_step(3);
	}
	public function step4()
	{
		$this->_step(4);
		$groupUser = $this->User->Group->findByValue('user');
		$this->Session->write('registration.data.User.group_id', $groupUser['Group']['id']);

		$this->Session->delete('registration.data.User.captcha');
		$this->Session->write('registration.data.User.email_key', $this->_getKey());
		$this->Session->write('registration.data.User.email_verified', 0);
		$this->log($this->Session->read('registration.data.User'));
		//$this->log(md5(microtime()));
	}

	public function step5()
	{
		$this->_step(5);

		if ($this->User->save($this->Session->read('registration.data')))
		{
			$message = __("%s %s,you are one step away from verifying your Mark Allen Coaching Account. Your activation key code is:%sClick on the link below to verify.",$this->Session->read('registration.data.User.first_name'), $this->Session->read('registration.data.User.last_name'), "\n<strong>".$this->Session->read('registration.data.User.email_key')."</strong>\n");

			$url = Router::url(array('controller' => 'users', 'action' => 'verificateEmail', 'key' => $this->Session->read('registration.data.User.email_key')), true);

			$email = new CakeEmail('default');
			$email->template('button');
			$email->viewVars(array('header' => __('Your Activation Key Code'), 'buttonLabel' => __('Verify'), 'url' => $url));
			$email->from(array('support@markallencoaching.com' => 'Mark Allen Coaching'))->to($this->Session->read('registration.data.User.email'));
			$email->subject('Your activation key code for Mark Allen Coaching');
			$email->send($message);

			$this->Session->delete('registration');

			$this->Session->setFlash(__('Registration complete. Please check your email to verify.'),'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-success'
			));
			return $this->redirect(array('action' => 'login'));
		}
		else
		{
			$this->Session->delete('registration');
			$this->Session->setFlash(__('Oops! An program error occured.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			return $this->redirect(array('action' => 'login'));
		}
	}

	protected function _step($step, $redirect = true)
	{
		$this->layout = 'auth';

		if ($this->Auth->user())
		{
			$this->redirect(array('controller' => 'pages', 'action' => 'dashboard', 'admin' => false));
		}
		elseif (is_int($this->Session->read('registration.params.stepCurrent')))
		{
			if ($this->Session->read('registration.params.maxProgress') < $step)
			{
				$this->redirect(array('action' => 'step'.$this->Session->read('registration.params.stepCurrent')));
			}
			else
			{
				$this->Session->write('registration.params.stepCurrent', $step);
			}
		}
		else
		{
			$this->redirect(array('action' => 'start'));
		}

		if ($this->request->is('post'))
		{
			$this->User->set($this->request->data);

			if ($this->User->validates())
			{
				$this->Session->write('registration.params.maxProgress', $step+1);

				$prevSessionData = $this->Session->read('registration.data');
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$this->Session->write('registration.data', $currentSessionData);

				if($redirect)
				{
					$this->redirect(array('action' => 'step'.($step+1)));
				}
			}
		}
		else
		{
			$this->request->data = $this->Session->read('registration.data');
		}

		$this->set('stepCurrent', $this->Session->read('registration.params.stepCurrent'));
		$this->set('maxProgress', $this->Session->read('registration.params.maxProgress'));
	}

	public function login()
	{
        $this->layout = 'auth';
        if ( $this->request->is('post') ) {

    		//special case for to make username and email logins possible
    		if (Validation::email($this->request->data['User']['user_name'])) {
    			$this->Auth->authenticate['Form']['fields']['username'] = 'email';
    			$this->Auth->constructAuthenticate();
    			$this->request->data['User']['email'] = $this->request->data['User']['user_name'];
    			#unset($this->request->data['User']['user_name']);
    		}

    		if ($this->Auth->login())
    		{
                if($this->Session->read('Auth.User.stripe_customer_id')) {
                    \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));
                    $customer = \Stripe\Customer::retrieve($this->Session->read('Auth.User.stripe_customer_id'));

                    if (isset($customer->subscriptions->data[0])) {
                        $this->User->setSubscription($customer->subscriptions->data[0]);
                    }
                }
    			return $this->redirect($this->Auth->redirect());
    		}

    		$unverifiedUser = $this->User->find('first', array(
    			'conditions' => array(
    				'User.user_name' => $this->request->data['User']['user_name'],
    				'not' => array(
    					'User.email_verified' => 1,
    				),
    			),
    		));

    		if(empty($unverifiedUser))
    		{
    			$this->Session->setFlash(__('Invalid username, email or password, try again'),'alert', array(
    				'plugin' => 'BoostCake',
    				'class' => 'alert-danger'
    			));
    		}
    		else
    		{
    			$verifyUrl = Router::url(array('action' => 'verificateEmail'));
    			$resendUrl = Router::url(array('action' => 'resendEmail'));

    			$this->Session->setFlash(__('Your email is still not being verified. %sVerificate%s %sResend%s', '<a href="'.$verifyUrl.'" class="btn btn-sm btn-danger"><i class="fa fa-check"></i> ','</a>', '<a href="'.$resendUrl.'" class="btn btn-sm btn-danger"><i class="fa fa-envelope"></i> ', '</a>'),'alert', array(
    				'plugin' => 'BoostCake',
    				'class' => 'alert-danger'
    			));
    		}
        }
	}
	public function logout()
	{
		$this->Session->setFlash(__('You have been logged out.'),'alert', array(
			'plugin' => 'BoostCake',
			'class' => 'alert-info'
		));
        $this->User->unsetSubscription();

		return $this->redirect($this->Auth->logout());

	}

	/**
	 * view method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function view($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
		$this->set('user', $this->User->find('first', $options));
	}


	/**
	 * edit method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function edit($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'view', $id));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
			$this->request->data = $this->User->find('first', $options);
		}
		$groups = $this->User->Group->find('list');
		$genders = $this->User->Gender->find('list');
		$countries= $this->User->Country->find('list');
		$this->set(compact('groups', 'genders', 'countries'));
	}

	/**
	 * delete method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function delete($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->User->delete()) {
			$this->Session->setFlash(__('The user has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The user could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}

	/**
	 * admin_index method
	 *
	 * @return void
	 */
	public function admin_index()
	{
    $this->Paginator->settings = array('User' => array(
        'order' => array('desc' => 'asc'),
    ));

		if($this->request->is('post'))
		{
			if(isset($this->request->data['Filter']['keywords']))
			{
				if($this->request->data['Filter']['keywords'] === "")
				{
					return $this->admin_search_reset();
				}

				$this->Session->write('Search.keywords', $this->request->data['Filter']['keywords']);
				return $this->redirect(array('action' => 'index'));
			}
		}
		$this->User->recursive = 0;

		if($this->Session->check('Search.keywords'))
		{
			$this->set('keywords', $this->Session->read('Search.keywords'));

			$conditions = array();
			$searchTerms = explode(' ', $this->Session->read('Search.keywords'));
			foreach($searchTerms as $searchTerm ){
				$conditions[] = array('OR' => array(
						'User.id Like' =>'%'. $searchTerm .'%',
						'User.first_name Like' =>'%'. $searchTerm .'%',
						'User.last_name Like' =>'%'. $searchTerm .'%',
						'User.user_name Like' =>'%'. $searchTerm .'%',
						'User.email Like' =>'%'. $searchTerm .'%'
					)
				);
			}
			$this->log($conditions);

			$this->set('users', $this->paginate('User', $conditions));
			/*
			$this->set('users', $this->Paginator->paginate('User', array(
					'or' => array(
						'User.id' => $this->Session->read('Search.keyword'),
						'User.first_name' => $this->Session->read('Search.keyword'),
						'User.last_name' => $this->Session->read('Search.keyword'),
						'User.user_name LIKE' => "%" . $this->Session->read('Search.keyword') . "%",
						'User.email LIKE' => "%" . $this->Session->read('Search.keyword') . "%",
					),
			)));
			*/
		}
		else
		{
			$this->set('users', $this->Paginator->paginate());
		}
	}

	public function admin_search_reset()
	{
		$this->Session->delete('Search');
		return $this->redirect(array('action' => 'index'));
	}

	public function admin_access($id)
	{
		if($this->request->is('post'))
		{
			$user = $this->User->find('first', array(
				'conditions' => array(
					'User.id' => $id,
				),
			));

			if(!empty($user))
			{
				$this->Session->write('Access', $this->Session->read('Auth'));
				unset($user['User']['password']);

				$this->Session->delete('Auth');
				$this->Session->write('Auth.User', $user['User']);
				$this->Session->write('Auth.User.Group', $user['Group']);
				$this->Session->write('Auth.User.Gender', $user['Gender']);

                if($this->Session->read('Auth.User.stripe_customer_id')) {
                    \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));
                    $customer = \Stripe\Customer::retrieve($this->Session->read('Auth.User.stripe_customer_id'));

                    if (isset($customer->subscriptions->data[0])) {
                        $this->User->setSubscription($customer->subscriptions->data[0]);
                    }
                }
				return $this->redirect(array('controller' => 'pages', 'action' => 'dashboard', 'admin' => false));
			}
		}
	}

	public function access_reset()
	{
		if($this->Session->check('Access'))
		{
			$id = $this->Session->read('Auth.User.id');
			$this->Session->write('Auth', $this->Session->read('Access'));
			$this->Session->delete('Access');

			return $this->redirect(array('controller' => 'users', 'action' => 'view', 'admin' => true, $id));
		}
	}

	/**
	 * admin_view method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function admin_view($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
		$this->set('user', $this->User->find('first', $options));
	}

	/**
	 * admin_add method
	 *
	 * @return void
	 */
	public function admin_add()
	{
		if ($this->request->is('post')) {
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->Session->setFlash(__('The user has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		$groups = $this->User->Group->find('list');
		$genders = $this->User->Gender->find('list');
		$this->set(compact('groups', 'genders'));
	}

	/**
	 * admin_edit method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function admin_edit($id = null) {
        $this->Security->unlockedFields = array('charged');
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}

		if ($this->request->is(array('post', 'put')))
		{
			if ($this->User->save($this->request->data))
			{

			$this->Session->setFlash(__('The user has been saved.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-success'
			));
			return $this->redirect(array('action' => 'index'));

			}
			else
			{
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		else
		{
			$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
			$this->request->data = $this->User->find('first', $options);
		}
		$groups = $this->User->Group->find('list');
		$genders = $this->User->Gender->find('list');
		$this->set(compact('groups', 'genders'));
	}

	/**
	 * admin_delete method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function admin_delete($id = null)
	{
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->User->delete()) {
			$this->Session->setFlash(__('The user has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The user could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}

	public function verificateEmail()
	{
		$this->layout = 'auth';

		if(!$this->request->is('post'))
		{
			if(isset($this->request['key']) && !empty($this->request['key']))
			{
				$this->request->data['User']['email_key'] = $this->request['key'];
			}
		}
		else
		{
			$this->User->set($this->request->data);

			if($this->User->validates())
			{
				$user = $this->User->find('first', array(
					'conditions' => array(
						'and' => array(
							'User.user_name' => $this->request->data['User']['user_name_check'],
							'User.email' => $this->request->data['User']['email_check'],
							'User.email_key' => $this->request->data['User']['email_key'],
							'User.email_verified' => false,
						),
					),
				));

				if(!empty($user))
				{
					$this->User->clear();
					$this->User->id = $user['User']['id'];

					if($this->User->saveField('email_verified', true) && $this->User->saveField('email_key', null))
					{
						$this->Session->setFlash(__('Your email and your account has been verified. You can login now.'), 'alert', array(
							'plugin' => 'BoostCake',
							'class' => 'alert-success'
						));

						$this->redirect(array('action' => 'login'));
					}
					else
					{
						$this->Session->setFlash(__('An error occured. Please try again.'), 'alert', array(
							'plugin' => 'BoostCake',
							'class' => 'alert-danger'
						));
					}
				}
				else
				{
					$this->Session->setFlash(__('Wrong username, email address or key code.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger'
					));
				}
			}
		}

		//$user = $this->User->find('first', array(
			//'conditions' => array(
				//'User.email_key' => $this->request->named['key'],
			//),
		//));

		if(!empty($user))
		{
		}
		else
		{
		}

		$captcha = $this->Captcha->create();
		$this->set('captcha', $captcha);
	}

	public function resendEmail()
	{
		$this->layout = 'auth';

		if($this->request->is('post'))
		{
			$this->User->set($this->request->data);

			if ($this->User->validates())
			{
				$user = $this->User->find('first', array(
					'conditions' => array(
						'and' => array(
							'User.user_name' => $this->request->data['User']['user_name_check'],
							'User.email' => $this->request->data['User']['email_check'],
						),
					),
				));

				if(!empty($user))
				{
					if($user['User']['email_verified'])
					{
						$this->Session->setFlash(__('Your email has been already verified. Please login.'), 'alert', array(
							'plugin' => 'BoostCake',
							'class' => 'alert-info'
						));

						$this->redirect(array('action' => 'login'));
					}
					else
					{
						$this->Session->setFlash(__('Your activation key code has been sent. Please check your inbox.'), 'alert', array(
							'plugin' => 'BoostCake',
							'class' => 'alert-info'
						));

						$message = __("%s %s,you are one step away from verifying your Mark Allen Coaching Account. Your activation key code is:%sClick on the link below to verify.",$user['User']['first_name'], $user['User']['last_name'], "\n<strong>".$user['User']['email_key']."</strong>\n");

						$url = Router::url(array('controller' => 'users', 'action' => 'verificateEmail', 'key' => $user['User']['email_key']), true);

						$email = new CakeEmail('default');
						$email->template('button');
						$email->viewVars(array('header' => __('Your Activation Key Code'), 'buttonLabel' => __('Verify'), 'url' => $url));
						$email->from(array('support@markallencoaching.com' => 'Mark Allen Coaching'))->to($user['User']['email']);
						$email->subject('Resending your Activation key code');
						$email->send($message);

						$this->redirect(array('action' => 'login'));
					}
				}
				else
				{
					$this->Session->setFlash(__('Wrong username and/or email address.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-info'
					));
				}
			}
		}

		$captcha = $this->Captcha->create();
		$this->set('captcha', $captcha);
	}

	public function forgotPassword()
	{
		$this->layout = 'auth';

		if($this->request->is('post'))
		{
			$this->User->set($this->request->data);
			if($this->User->validates())
			{
				$user = $this->User->find('first', array(
					'conditions' => array(
						'and' => array(
							'User.user_name' => $this->request->data['User']['user_name_check'],
							'User.email' => $this->request->data['User']['email_check'],
						),
					),
				));

				if(!empty($user) && $user['User']['email_verified'])
				{
					$key = $this->_getKey();

					$this->User->clear();
					$this->User->id = $user['User']['id'];

					if($this->User->saveField('email_key', $key))
					{
						$this->Session->setFlash(__('Your reset key code has been sent. Please check your inbox.'), 'alert', array(
							'plugin' => 'BoostCake',
							'class' => 'alert-info'
						));

						$message = __("%s %s, please confirm that you'd like to reset the password for %s. Your reset key code is:%sClick on the link below to reset.",$user['User']['first_name'], $user['User']['last_name'], $user['User']['email'], "\n<strong>".$key."</strong>\n");

						$url = Router::url(array('controller' => 'users', 'action' => 'resetPassword', 'key' => $key), true);

						$email = new CakeEmail('default');
						$email->template('button');
						$email->viewVars(array('header' => __('Are you sure?'), 'buttonLabel' => __('Reset'), 'url' => $url));
						$email->from(array('support@markallencoaching.com' => 'Mark Allen Coaching'))->to($user['User']['email']);
						$email->subject('Password reset for Mark Allen Coaching');
						$email->send($message);

						$this->redirect(array('action' => 'login'));
					}
				}
				elseif(!empty($user) && !$user['User']['verified'])
				{
					$this->Session->setFlash(__('Your you to verify your email address first in order to reset your password.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger',
					));
				}
				else
				{
					$this->Session->setFlash(__('Wrong username and/or email address.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger',
					));
				}
			}
		}

		$captcha = $this->Captcha->create();
		$this->set('captcha', $captcha);
	}

	public function resetPassword()
	{
		$this->layout = 'auth';
		$this->set('title_for_layout', __('Reset your Password'));

		if(!$this->request->is('post'))
		{
			if(isset($this->request['key']))
			{
				$this->request->data['User']['email_key'] = $this->request['key'];
			}
		}
		else
		{
			$this->User->set($this->request->data);

			if($this->User->validates())
			{
				$user = $this->User->find('first', array(
					'conditions' => array(
						'and' => array(
							'User.user_name' => $this->request->data['User']['user_name_check'],
							'User.email' => $this->request->data['User']['email_check'],
							'User.email_key' => $this->request->data['User']['email_key'],
						),
					),
				));

				if(!empty($user) && $user['User']['email_verified'])
				{
					$this->User->clear();
					$this->User->id = $user['User']['id'];

					if($this->User->saveField('password', $this->request->data['User']['password_new']) && $this->User->saveField('email_key', null))
					{
						$this->Session->setFlash(__('succes, try to login with new password now.'), 'alert', array(
							'plugin' => 'BoostCake',
							'class' => 'alert-success',
						));

						$this->redirect(array('action' => 'login'));
					}
					else
					{
						$this->Session->setFlash(__('error'), 'alert', array(
							'plugin' => 'BoostCake',
							'class' => 'alert-danger',
						));
					}

				}
				elseif(!empty($user) && !$user['User']['email_verified'])
				{
					$verifyUrl = Router::url(array('action' => 'verificateEmail'));
					$resendUrl = Router::url(array('action' => 'resendEmail'));

					$this->Session->setFlash(__('Your email is still not being verified. %sVerificate%s %sResend%s', '<a href="'.$verifyUrl.'" class="btn btn-sm btn-danger"><i class="fa fa-check"></i> ','</a>', '<a href="'.$resendUrl.'" class="btn btn-sm btn-danger"><i class="fa fa-envelope"></i> ', '</a>'),'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger',
					));

					$this->redirect(array('action' => 'login'));
				}
				else
				{
					$this->Session->setFlash(__('Wrong username, email address or key code.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger'
					));
				}
			}
		}

		$captcha = $this->Captcha->create();
		$this->set('captcha', $captcha);
	}

	private function _getKey()
	{
		return md5(microtime());
	}

	public function affegiraffe()
	{
		$this->autoRender = false;

		//status
		//authResponse
		if (!$this->request->is('post')) {
			return $this->redirect(array('controller' => 'pages', 'action' => 'dashboard'));
		}
		$data = $this->request->data('authResponse');

        $fb = new Facebook\Facebook(array(
        'app_id' => Configure::read('Facebook.id'),
        'app_secret' => Configure::read('Facebook.secret'),
        'default_graph_version' => 'v2.2',
        ));
        $oAuth2Client = $fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($data['accessToken']);

		return json_encode($tokenMetadata);
	}

    public function facebook()
    {
		if(!session_id()) {
		session_start();
		}
        $fb = new Facebook\Facebook(array(
        'app_id' => Configure::read('Facebook.id'),
        'app_secret' => Configure::read('Facebook.secret'),
        'default_graph_version' => 'v2.2',
        ));

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        // Logged in
        $this->log($accessToken->getValue());

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $this->log($tokenMetadata);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId(Configure::read('Facebook.id')); // Replace {app-id} with your app id
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();

        if (!$accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
                exit;
            }
            $this->log($accessToken->getValue());
        }

        try {
        // Returns a `Facebook\FacebookResponse` object
        $fields = 'id,name,email,birthday,first_name,gender,last_name,verified';
        $response = $fb->get('/me?fields='.$fields, $accessToken->getValue());
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
        }

        $fbUser = $response->getGraphUser();

        if(!$fbUser['verified']) {
            throw new NotFoundException('Not verified Facebook user.');
        }

        $user = $this->User->findByEmail($fbUser['email']);

        if(empty($user)) {

            $date = (array)$fbUser['birthday'];
            $dob = new DateTime($date['date']);

            if ($fbUser['gender'] === 'female') {
                $genderId = 4;
            } else {

                $genderId = 3;
            }
            $user = array(
                'first_name' => $fbUser['first_name'],
                'last_name' => $fbUser['last_name'],
                'email' => $fbUser['email'],
                'email_verified' => 1,
                'password' => microtime(),
                'gender_id' => $genderId,
                'group_id' => 1,
                'date_of_birth' => $dob->format('Y-m-d')
            );
			$this->User->create();
			if ($this->User->save($user)) {
                $user['id'] = $this->User->id;
                $this->Auth->login($user);
				return $this->redirect($this->Auth->redirect());
			} else {
				$this->Session->setFlash(__('We couldnt safe your data. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
        } else {
            $this->Auth->login($user['User']);
			return $this->redirect($this->Auth->redirect());
        }
    }
    public function garmin_request()
    {
        ini_set('memory_limit','512M');
        $req_url = 'http://connectapitest.garmin.com/oauth-service-1.0/oauth/request_token';
        $authurl = 'http://connecttest.garmin.com/oauthConfirm';
        $acc_url = 'http://connectapitest.garmin.com/oauth-service-1.0/oauth/access_token';
        $conskey = '358c0537-301f-46f2-b85c-655580f8a8e4';
        $conssec = 'en1d0B7IUnUgUFUtpANhrXMP8ayeiaN1Te1';
        // In state=1 the next request should include an oauth_token.
        // If it doesn't go back to 0
        if(!isset($_GET['oauth_token']) && isset($_SESSION['state']) && $_SESSION['state'] == 1) {
            $_SESSION['state'] = 0;
        }

        try {
            $oauth = new OAuth($conskey,$conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
            $oauth->enableDebug();
            if(!isset($_GET['oauth_token']) && !isset($_SESSION['state'])) {
                $request_token_info = $oauth->getRequestToken($req_url);
                $_SESSION['secret'] = $request_token_info['oauth_token_secret'];
                $_SESSION['state'] = 1;
                header('Location:' . $authurl.'?oauth_token=' . $request_token_info['oauth_token']);
                $this->log('request successful');
                $this->log($request_token_info);
                exit;
            } else if(isset($_SESSION['state']) && $_SESSION['state'] == 1) {
                $oauth->setToken($_GET['oauth_token'],$_SESSION['secret']);
                $access_token_info = $oauth->getAccessToken($acc_url);
                $_SESSION['state'] = 2;
                $_SESSION['token'] = $access_token_info['oauth_token'];
                $_SESSION['secret'] = $access_token_info['oauth_token_secret'];
                $this->log('access successful');
                $this->log($access_token_info);
            }
        } catch(OAuthException $error) {
            $this->log('error successful');
            $this->log($error);
        }

    }
    public function garmin_receive()
    {
        $json = json_decode($_POST['uploadMetaData']);
        $tmp_name = $_FILES['file']['tmp_name'];
        $file_name = $_FILES['file']['name'];
        move_uploaded_file($tmp_name, YOUR_FILE_PATH);
        header('Location: YOUR_URL_FOR_THE_SAVED_FILE', true, 201);
    }

    public function sign()
    {
        $this->set('site_key', Configure::read('Google.recaptcha.keys.site'));

		if ($this->request->is('post')) {
            //only email and password fields are given
            if ( count( $this->request->data['User'] ) === 2 ) {
                $this->_signIn();
            } else {
                $recaptcha = new \ReCaptcha\ReCaptcha(Configure::read('Google.recaptcha.keys.secret'));
                $response = $recaptcha->verify($this->request->data['g-recaptcha-response'], $this->request->clientIp());
                if ( $response->isSuccess() ) {
                    $this->_signUp();
                } else {
                    $this->log( $response->getErrorCodes() );
                }
            }

        } elseif ( $this->Auth->login() ) {

			return $this->redirect($this->Auth->redirect());
		}
		$this->set('title_for_layout', __('Sign In'));
		$this->layout = 'auth';
        $quotes = [
            "Would you rather be coached by someone who read a book, or by the coach who wrote the book?",
            "Self-confidence eludes those who never face fear.",
            "Focus is ever-changing when following a moving target. Excellence is a moving target.",
            "Use pressure to focus energy.",
            "Beating an opponent at their game is tough; beating them at yours is not",
            "A result can only be bad if one does not learn from it.",
            "Self-confidence eludes those who never face fear.",
            "Easy changes rarely lead to victory...",
            "Impossible is a great victory taking shape.",
            "Excellence has little time for doubt.",
            "A race is truth in motion.",
            "Uncertainty is the test of trust. become comfortable with it.",
            "Thought precedes form. Silence preceded perfect form.",
            "Expect chaos and respond appropriately.",
            "The real world doesn't care about your ideal strategy. It is begging you to follow its plan."
        ];
        $gender = $this->User->Gender->find('first', array(
            'recursive' => 0,
            'order' => ['Gender.id' => 'asc']
        ));
        $gender = $gender['Gender']['id'];
        $genders = $this->User->Gender->find('list', array(
            'recursive' => 0,
            'order' => ['Gender.id' => 'asc']
        ));
				foreach ( $genders as $key => $gender ) {
						$genders[ $key ] = __( $gender );
				}

        $this->set('genders', $genders );
        $this->set('gender', $gender );
        $this->set('quote', $quotes[array_rand($quotes)] );
    }

    public function terms()
    {
        $this->layout = 'auth';
        $this->set( 'brand', 'Mark Allen Coaching' );
        $this->set( 'businessForm', 'LLC' );

    }

    protected function _signUp()
    {
        $group = $this->User->Group->find('first', array(
            'conditions' => array(
                'Group.value' => 'user',
            ),
            'recursive' => 0
        ));

        $user = [
            'User' => [
                'first_name' => $this->request->data['User']['first_name'],
                'last_name' => $this->request->data['User']['last_name'],
                'email' => $this->request->data['User']['email'],
                'password' => $this->request->data['User']['password'],
                'password_confirm' => $this->request->data['User']['password_confirm'],
                'date_of_birth' => $this->request->data['User']['date_of_birth'],
                'group_id' => $group['Group']['id'],
                'gender_id' => $this->request->data['User']['genders'],
            ]
        ];
		if ($this->User->save( $user )) {

			$url = Router::url(array('controller' => 'users', 'action' => 'login'), true);
			$message = __("%s %s,you are one step away from sign in into your Mark Allen Coaching Account. Your email is %s and your password is %s, Click on the link below to sign in.",
                $this->request->data['User']['first_name'],
                $this->request->data['User']['last_name'],
                "\n<strong>" . $this->request->data['User']['email'] . "</strong>\n",
                "\n<strong>" . $this->request->data['User']['password'] . "</strong>\n"
            );

/*
			$email = new CakeEmail('default')
                ->template('button')
                ->viewVars(array('header' => __('Welcome'), 'buttonLabel' => __('app.markallencoaching.com'), 'url' => $url))
                ->from(array('support@markallencoaching.com' => 'Mark Allen Coaching'))->to($this->request->data['User']['email'])
                ->subject('Your account informtion for Mark Allen Coaching')
                ->send($message);
                */

    		if ($this->Auth->login()) {
				return $this->redirect(array('controller' => 'Pages', 'action' => 'dashboard'));
    		}
        }

    }

	protected function _signIn()
	{
		//special case for to make username and email logins possible
		if (!Validation::email($this->request->data['User']['email'])) {
			$this->Auth->authenticate['Form']['fields']['username'] = 'user_name';
			$this->Auth->constructAuthenticate();
			$this->request->data['User']['user_name'] = $this->request->data['User']['email'];
			#unset($this->request->data['User']['user_name']);
		}

		if ($this->Auth->login())
		{
            if($this->Session->read('Auth.User.stripe_customer_id')) {
                \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));
                $customer = \Stripe\Customer::retrieve($this->Session->read('Auth.User.stripe_customer_id'));

                if (isset($customer->subscriptions->data[0])) {
                    $this->User->setSubscription($customer->subscriptions->data[0]);
                }
            }
			return $this->redirect($this->Auth->redirect());
		} else {
            $this->Session->setFlash(__('Invalid username, email or password, try again'));
        }
        /*
		$unverifiedUser = $this->User->find('first', array(
			'conditions' => array(
				'User.user_name' => $this->request->data['User']['user_name'],
				'not' => array(
					'User.email_verified' => 1,
				),
			),
		));

		if(empty($unverifiedUser))
		{
			$this->Session->setFlash(__('Invalid username, email or password, try again'),'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));
		}
		else
		{
			$verifyUrl = Router::url(array('action' => 'verificateEmail'));
			$resendUrl = Router::url(array('action' => 'resendEmail'));

			$this->Session->setFlash(__('Your email is still not being verified. %sVerificate%s %sResend%s', '<a href="'.$verifyUrl.'" class="btn btn-sm btn-danger"><i class="fa fa-check"></i> ','</a>', '<a href="'.$resendUrl.'" class="btn btn-sm btn-danger"><i class="fa fa-envelope"></i> ', '</a>'),'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));
		}
        */
	}

}
