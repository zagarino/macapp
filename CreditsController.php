<?php
App::uses('AppController', 'Controller');
/**
 * Credits Controller
 *
 * @property Credit $Credit
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class CreditsController extends AppController {

	public $components = array('Paginator', 'Session');

	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Security->unlockedFields = array('cvc', 'number', 'token', 'month', 'year');
	}

	public function index() {
		$this->Credit->recursive = 0;

		$this->Paginator->settings = array(
			'order' => array(
				'Credit.created' => 'desc',
			),
		);

		$this->set('credits', $this->Paginator->paginate('Credit', array(
			'user_id' => $this->Auth->user('id')
		)));

	}

	public function beforeRender()
	{
		parent::beforeRender();
		$steps = $this->Session->read('charge.steps.current');
		$this->set('steps', $steps);
	}

/*
	public function charge()
	{
		$this->Session->delete('charge');

		$this->Session->write('charge.params.maxProgress', 1);
		$this->Session->write('charge.params.stepCurrent', 1);

		$this->redirect(array('action' => 'step1'));
	}

	public function step1()
	{
		if (is_int($this->Session->read('charge.params.maxProgress')))
		{
			$this->set('maxProgress', $this->Session->read('charge.params.maxProgress'));
		}
		else
		{
			$this->redirect(array('action' => 'charge'));
		}

		$this->Session->write('charge.params.stepCurrent', 1);
		$this->set('stepCurrent', $this->Session->read('charge.params.stepCurrent'));

		if ($this->request->is('post'))
		{
			$this->Credit->set($this->request->data);
			if ($this->Credit->validates())
			{
				if($this->request->data['Credit']['card'] === 'new')
				{
					$this->Session->write('charge.params.maxProgress', 2);
				}
				else
				{
					$this->Session->write('charge.params.maxProgress', 3);
				}

				$prevSessionData = $this->Session->read('charge.data');
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$this->Session->write('charge.data', $currentSessionData);

				$this->redirect(array('action' => 'step' . $this->Session->read('charge.params.maxProgress')));
			}
		}
		else
		{
			$this->loadModel('User');

			$user = $this->User->find('first', array(
				'conditions' => array(
					'User.id' => $this->Auth->user('id'),
				),
				'fields' => array(
					'stripe_customer_id',
				),
				'recursive' => false,
			));
			$this->request->data = $this->Session->read('charge.data');
		}

		if (isset($user['User']['stripe_customer_id']))
		{
			$this->set('stripeCustomer', (bool)$user['User']['stripe_customer_id']);
		}
		else
		{
			$this->set('stripeCustomer', false);
		}
	}

	public function step2()
	{
		$this->_step(2, false);

		if ($this->Session->read('charge.data.Credit.card') !== 'new')
		{
			$this->redirect(array('action' => 'step1'));
		}

		$this->loadModel('User');

		$user = $this->User->find('first', array(
			'conditions' => array(
				'User.id' => $this->Auth->user('id')
			),
			'fields' => array(
				'stripe_customer_id'
			),
		));

		if($this->request->is('post') && isset($this->request->data['Credit']['token']) && $this->Session->check('charge.data.Credit.card'))
		{
			Stripe::setApiKey(Configure::read('Stripe.secret_key'));

			if (empty($user['User']['stripe_customer_id']) && $this->Session->read('charge.data.Credit.card') === 'new') // create credit card and new stripe customer
			{
				try
				{
					$customer = Stripe_Customer::create(array(
						'card' => $this->request->data['Credit']['token'],
						'email' => $this->Auth->user('email'),
						'description' => 'Mark Allen Coaching Athlete',
					));
				}
				catch(Stripe_CardError $e)
				{
					$this->Session->delete('charge');
					$this->Session->setFlash(__('The credit card could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
					return $this->redirect(array('action' => 'index'));
				}

				$user['User']['stripe_customer_id'] = $customer->id;

				if (!$this->User->save($user))
				{
					$this->Session->delete('charge');
					$this->Session->setFlash(__('The credit card could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
					return $this->redirect(array('action' => 'index'));
				}
			}
			elseif ($this->Session->read('charge.data.Credit.card') === 'new') // update credit card for existing stripe customer
			{
				try
				{
					$customer = Stripe_Customer::retrieve($user['User']['stripe_customer_id']);

					$card = $customer->cards->create(array('card' => $this->request->data['Credit']['token']));

					$customer->default_card = $card->id;
					$customer->save();
				}
				catch(Stripe_CardError $e)
				{
					$this->Session->delete('charge');
					$this->Session->setFlash(__('The credit card could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
					return $this->redirect(array('action' => 'index'));
				}
			}

			$this->Session->write('charge.params.maxProgress', 3);
			return $this->redirect(array('action' => 'step3'));
			//$this->log($customer);
		}
		elseif($this->request->is('post'))
		{
			$this->Session->delete('charge');
			$this->Session->setFlash(__('The credit card could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			return $this->redirect(array('action' => 'index'));
		}
	}

	public function step3()
	{
		$this->_step(3);

		$this->loadModel('Pricing');

		$pricings = $this->Pricing->find('all', array(
			'order' => array(
				'Pricing.value' => 'asc'
			),
			'fields' => array(
				'value',
				'amount',
			)
		));

		$this->set('pricings', $pricings);
		$this->log($pricings);
	}

	public function step4()
	{
		$this->_step(4, false);

		$this->loadModel('User');
		$user = $this->User->find('first', array(
			'conditions' => array(
				'User.id' => $this->Auth->user('id')
			),
			'fields' => array(
				'stripe_customer_id'
			),
		));

		$this->loadModel('Pricing');

		$amount= $this->Pricing->find('first', array(
			'conditions' => array(
				'Pricing.value <=' => $this->Session->read('charge.data.Credit.value'),
			),
			'fields' => array(
				'amount',
			),
			'order' => array(
				'Pricing.value' => 'desc'
			)
		));

		$this->set('amount', $amount['Pricing']['amount']);

		Stripe::setApiKey(Configure::read('Stripe.secret_key'));

		$customer = Stripe_Customer::retrieve($user['User']['stripe_customer_id']);
		$card = $customer->cards->retrieve($customer->default_card);

		$this->set('card', $card->brand);
		$this->set('last4', $card->last4);
		$this->set('value', $this->Session->read('charge.data.Credit.value'));

		if ($this->request->is('post'))
		{
			try
			{
				$charge = Stripe_Charge::create(array(
				  'amount' => $amount['Pricing']['amount'] * $this->Session->read('charge.data.Credit.value') * 100 , // amount in cents
				  'currency' => 'usd',
					'customer' => $customer->id,
					'description' => 'Charged ' . $this->Session->read('charge.data.Credit.value') . ' Credits for ' . $amount['Pricing']['amount'] . ' $',
				));
			}
			catch(Stripe_CardError $e)
			{
				$this->Session->delete('charge');
				$this->Session->setFlash(__('The credit card has been declined. Please, try again or a different credit card.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
				return $this->redirect(array('action' => 'index'));
			}

			$creditType = $this->Credit->CreditType->find('first', array(
				'conditions' => array(
					'CreditType.value' => 'charge'
				),
				'fields' => array(
					'id'
				),
				'recursive' => false
			));

			$this->Credit->create();
			$data = array(
				'Credit' => array(
					'value' => $this->Session->read('charge.data.Credit.value'),
					'amount' => $amount['Pricing']['amount'],
					'stripe_charge_id' => $charge->id,
					'user_id' => $this->Auth->user('id'),
					'credit_type_id' => $creditType['CreditType']['id'],
				),
			);

			if ($this->Credit->save($data))
			{
				$summary = $this->Credit->summarize($this->Auth->user('id'));

				$this->Credit->User->id = $this->Auth->user('id');
				if($this->Credit->User->saveField('credits', $summary))
				{
					$this->Session->setFlash(__('The credit has been charged.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-success'
					));
					$this->Session->delete('charge');
					return $this->redirect(array('action' => 'index'));
				}
			}
			else
			{
				$this->Session->setFlash(__('The credit could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
	}

	private function _step($step, $redirect = true)
	{
		if (is_int($this->Session->read('charge.params.stepCurrent')))
		{
			if ($this->Session->read('charge.params.maxProgress') < $step)
			{
				$this->redirect(array('action' => 'step'.$this->Session->read('charge.params.stepCurrent')));
			}
			else
			{
				$this->Session->write('charge.params.stepCurrent', $step);
			}
		}
		else
		{
			$this->redirect(array('action' => 'charge'));
		}

		if ($this->request->is('post'))
		{
			$this->Credit->set($this->request->data);

			if ($this->Credit->validates())
			{
				$this->Session->write('charge.params.maxProgress', $step+1);

				$prevSessionData = $this->Session->read('charge.data');
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$this->Session->write('charge.data', $currentSessionData);

				if ($redirect)
				{
					$this->redirect(array('action' => 'step'.($step+1)));
				}
			}
		}
		else
		{
			$this->request->data = $this->Session->read('charge.data');
		}

		$this->set('stepCurrent', $this->Session->read('charge.params.stepCurrent'));
		$this->set('maxProgress', $this->Session->read('charge.params.maxProgress'));
	}
    */

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->Credit->recursive = 0;

		$this->Paginator->settings = array(
			'order' => array(
				'Credit.created' => 'desc',
			),
		);

		$this->set('credits', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Credit->exists($id)) {
			throw new NotFoundException(__('Invalid credit'));
		}
		$options = array('conditions' => array('Credit.' . $this->Credit->primaryKey => $id));
		$this->set('credit', $this->Credit->find('first', $options));
	}

	public function admin_add()
	{


		if ($this->request->is('post'))
		{
			$this->Credit->set($this->request->data);
			if (!$this->Credit->validates(array('fieldList' => array('value'))))
			{
				return $this->redirect(array('action' => 'add'));
			}

			$this->loadModel('CreditType');
			$type = $this->CreditType->find('first', array(
				'conditions' => array(
					'CreditType.value' => 'free',
				),
				'fields' => array(
					'id'
				),
				'recursive' => false
			));

			$this->Credit->create();

			$data = array(
				'Credit' => array(
					'value' => $this->request->data['Credit']['value'],
					'user_id' => $this->request->data['Credit']['user_id'],
					'credit_type_id' => $type['CreditType']['id']
				),
			);

			if ($this->Credit->save($data))
			{
				$this->Session->setFlash(__('The credit has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			}
			else
			{
				$this->Session->setFlash(__('The credit could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		$users = $this->Credit->User->find('list');
		$this->set(compact('users'));
	}

	public function admin_refund($id = null)
	{
		if (!$this->Credit->exists($id))
		{
			throw new NotFoundException(__('Invalid credit'));
		}

		if ($this->request->is(array('post', 'put')))
		{
			$charge = $this->Credit->find('first', array(
				'conditions' => array(
					'Credit.id' => $id
				),
				'fields' => array(
					'value',
					'user_id',
				),
				'recursive' => -1,
			));

			$user = $this->Credit->User->find('first', array(
				'conditions' => array(
					'User.id' => $charge['Credit']['user_id'],
				),
				'fields' => array(
					'credits',
					'credits_used',
					'credits_balance',
				),
				'recursive' => -1,
			));


			if($user['User']['credits_balance'] - $charge['Credit']['value'] < 0)
			{
				$this->Session->setFlash(__('The charge could not be refunded. Balance would fall negative.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
				return $this->redirect(array('action' => 'index'));
			}

			$stripe = $this->Credit->find('first', array(
				'conditions' => array(
					'Credit.id' => $id,
				),
				'fields' => array(
					'stripe_charge_id',
				),
			));

			if (!empty($stripe['Credit']['stripe_charge_id']))
			{
				try
				{
					Stripe::setApiKey(Configure::read('Stripe.secret_key'));
					$charge = Stripe_Charge::retrieve($stripe['Credit']['stripe_charge_id']);
					$charge->refunds->create();
				}
				catch (Stripe_ApiConnectionError $e)
				{
					$this->Session->setFlash(__('The charge could not be refunded. Please, try again.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger'
					));
					return $this->redirect(array('action' => 'index'));
				}
			}

			$this->Credit->id = $id;

			if ($this->Credit->saveField('refunded', 1))
			{
				$this->Session->setFlash(__('The charge has been refunded.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));

				return $this->redirect(array('action' => 'index'));
			}
			else
			{
				$this->Session->setFlash(__('The charge could not be refunded. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));

				return $this->redirect(array('action' => 'index'));
			}
		}
	}

	public function admin_reset()
	{
		$this->request->allowMethod('post', 'delete');

		if (!Configure::read('debug'))
		{
			$this->Session->setFlash(__('Not allowed on production.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));

			return $this->redirect(array('controller' => 'pages', 'action' => 'panel'));
		}

		if ($this->Credit->deleteAll(true, false))
		{
			if($this->Credit->User->updateAll(array('credits' => 0)))
			{
				$this->Session->write('Auth.User.credits', 0);
				$this->Session->setFlash(__('The credits has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
			}
			else
			{
				$this->Session->setFlash(__('The credits could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		else
		{
			$this->Session->setFlash(__('The credits could not be deleted. Please, try again.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));
		}
		return $this->redirect(array('controller' => 'pages', 'action' => 'panel'));
	}
}
