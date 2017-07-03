<?php
App::uses('AppController', 'Controller');
/**
 * Credits Controller
 *
 * @property Credit $Credit
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class SubscriptionsController extends AppController {

    public $subscriptionStatuses = array(
        'trialing' => '',
        'active' => '',
        'past_due' => '',
        'canceled' => '',
    );
/**
 * Components
 *
 * @var array
 */
	public $components = array('Session');

	public function beforeFilter()
	{
		parent::beforeFilter();

    $this->subscriptionStatuses = array(
        'trialing' => __('Trialing'),
        'active' => __('Active'),
        'past_due' => __('Past Due'),
        'canceled' => __('Canceled'),
    );

		//$this->Security->unlockedFields = array('cvc', 'number', 'token', 'month', 'year');
        $this->Security->unlockedActions = array('step3');
	}

/**
 * index method
 *
 * @return void
 */
	public function index() {
        $this->log(date('m/d/Y', $this->Session->read('Auth.User.Subscription.current_period_end')));
        $this->set('subscription', $this->Session->read('Auth.User.Subscription'));
        $this->set('plan', $this->Session->read('Auth.User.Plan'));
        $this->set('statuses', $this->subscriptionStatuses);
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$steps = $this->Session->read('subscribe.steps.current');
		$this->set('steps', $steps);
	}

	public function subscribe()
	{
		$this->Session->delete('subscribe');

		$this->Session->write('subscribe.params.maxProgress', 1);
		$this->Session->write('subscribe.params.stepCurrent', 1);

		$this->redirect(array('action' => 'step1'));
	}

	public function step1()
	{
        \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));

        if ($this->request->is('post')) {
            if (\Stripe\Plan::retrieve($this->request->data['Plan']['plans'])) {
                $this->_step(1);
            }
        }
        $plans = \Stripe\Plan::all(array("limit" => 10));
        $planList = array();
        foreach ($plans->data as $key => $plan) {
            $this->log($plan);
            if($plan->id === 'fast' || $plan->id === 'faster' || $plan->id === 'fastest') {
                unset($plans->data[$key]);
            }
        }


        foreach ($plans->data as $plan) {
            $planList[$plan->id] = $plan->name;
        }

        $this->set('plansData', $plans->data);

        $this->set('plans', $planList);


		$this->_step(1);

        /*
    	$this->Session->write('subscribe.params.maxProgress', 3);
    	return $this->redirect(array('action' => 'step3'));
        */
        /*

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
        */
	}

	public function step2()
	{
		if (is_int($this->Session->read('subscribe.params.maxProgress'))) {
			$this->set('maxProgress', $this->Session->read('subscribe.params.maxProgress'));
		} else {
			$this->redirect(array('action' => 'subscribe'));
		}

		$this->Session->write('subscribe.params.stepCurrent', 2);
		$this->set('stepCurrent', $this->Session->read('subscribe.params.stepCurrent'));

		if ($this->request->is('post')) {
			if($this->request->data['Credit']['card'] === 'new') {
				$this->Session->write('subscribe.params.maxProgress', 3);
			} else {
				$this->Session->write('subscribe.params.maxProgress', 4);
			}

			$prevSessionData = $this->Session->read('subscribe.data');
			$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
			$this->Session->write('subscribe.data', $currentSessionData);

			$this->redirect(array('action' => 'step' . $this->Session->read('subscribe.params.maxProgress')));
		} else {
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
			$this->request->data = $this->Session->read('subscribe.data');
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

	public function step3()
	{
		$this->_step(3, false);

		if ($this->Session->read('subscribe.data.Credit.card') !== 'new')
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

		if($this->request->is('post') && isset($this->request->data['Credit']['token']) && $this->Session->check('subscribe.data.Credit.card'))
		{
            \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));
			if (empty($user['User']['stripe_customer_id']) && $this->Session->read('subscribe.data.Credit.card') === 'new') // create credit card and new stripe customer
			{
				try
				{
                    $customer = \Stripe\Customer::create(array(
						'source' => $this->request->data['Credit']['token'],
						'email' => $this->Auth->user('email'),
						'description' => 'Mark Allen Coaching Athlete',
					));
				}
				catch(\Stripe\Error\Card $e)
				{
					$this->Session->delete('subscribe');
					$this->Session->setFlash(__('The Credit card could not be saved. Please, try again.'), 'alert', array(
    					'plugin' => 'BoostCake',
    					'class' => 'alert-danger'
		            ));
					return $this->redirect(array('action' => 'index'));
				}

				$user['User']['stripe_customer_id'] = $customer->id;

				if (!$this->User->save($user))
				{
					$this->Session->delete('subscribe');
					$this->Session->setFlash(__('The Credit card could not be saved. Please, try again.'), 'alert', array(
        				'plugin' => 'BoostCake',
        				'class' => 'alert-danger'
		            ));
					return $this->redirect(array('action' => 'index'));
				}
			}
			elseif ($this->Session->read('subscribe.data.Credit.card') === 'new') // update credit card for existing stripe customer
			{
				try
				{
                    $customer = \Stripe\Customer::retrieve($user['User']['stripe_customer_id']);

					$source = $customer->sources->create(array('source' => $this->request->data['Credit']['token']));

					$customer->default_source = $source->id;
					$customer->save();
				}
				catch(Stripe_CardError $e)
				{
					$this->Session->delete('subscribe');
					$this->Session->setFlash(__('The credit card could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
					return $this->redirect(array('action' => 'index'));
				}
			}

			$this->Session->write('subscribe.params.maxProgress', 4);
			return $this->redirect(array('action' => 'step4'));
			//$this->log($customer);
		}
		elseif($this->request->is('post'))
		{
			$this->Session->delete('subscribe');
			$this->Session->setFlash(__('The Credit card could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			return $this->redirect(array('action' => 'index'));
		}
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
				'stripe_customer_id',
				'promo',
			),
		));

        \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));

		$customer = \Stripe\Customer::retrieve($user['User']['stripe_customer_id']);

		$card = $customer->cards->retrieve($customer->default_card);

		$this->set('card', $card->brand);
		$this->set('user', $user);
		$this->set('last4', $card->last4);
		$this->set('value', $this->Session->read('subscribe.data.Credit.value'));
        $plan = \Stripe\Plan::retrieve($this->Session->read('subscribe.data.Plan.plans'));
        $this->set('plan', $plan);

		if ($this->request->is('post'))
		{
            $coupon = false;
            if (isset($this->request->data['User']['coupon']) && $plan->id === 'promo') {
                if(isset($this->request->data['User']['coupon']) && $this->request->data['User']['coupon']) {
        			try
        			{
                        $coupon = \Stripe\Coupon::retrieve($this->request->data['User']['coupon']);
        			}
        			catch(\Stripe\Error\InvalidRequest $e)
        			{
                        return $this->User->invalidate('coupon', __('Invaled Coupon Code!'));
        			}
                }
            }

			try
			{
                if ($coupon) {
                    $subscription = \Stripe\Subscription::create(array(
                        'customer' => $customer->id,
                        'plan' => $plan,
                        'coupon' => $coupon->id,
                    ));

    				$user['User']['promo'] = true;
    				$this->User->save($user);

                } else {
                    $subscription = \Stripe\Subscription::create(array(
                        'customer' => $customer->id,
                        'plan' => $plan
                    ));
                }

			}
			catch(\Stripe\Error\Card $e)
			{
				$this->Session->delete('subscribe');
				$this->Session->setFlash(__('The credit card has been declined. Please, try again or a different credit card.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
				return $this->redirect(array('action' => 'index'));
			}

            $this->User->setSubscription($subscription);

			$this->Session->setFlash(__('You subscribed with the %s.', $subscription->plan->name), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-success'
			));

            $this->Session->delete('subscribe');
            return $this->redirect(array('action' => 'index'));
		}
	}

	private function _step($step, $redirect = true)
	{
		if (is_int($this->Session->read('subscribe.params.stepCurrent')))
		{
			if ($this->Session->read('subscribe.params.maxProgress') < $step)
			{
				$this->redirect(array('action' => 'step'.$this->Session->read('subscribe.params.stepCurrent')));
			}
			else
			{
				$this->Session->write('subscribe.params.stepCurrent', $step);
			}
		}
		else
		{
			$this->redirect(array('action' => 'subscribe'));
		}

		if ($this->request->is('post'))
		{
			$this->Session->write('subscribe.params.maxProgress', $step+1);

			$prevSessionData = $this->Session->read('subscribe.data');
			$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
			$this->Session->write('subscribe.data', $currentSessionData);

			if ($redirect)
			{
				$this->redirect(array('action' => 'step'.($step+1)));
			}
		}
		else
		{
			$this->request->data = $this->Session->read('subscribe.data');
		}

		$this->set('stepCurrent', $this->Session->read('subscribe.params.stepCurrent'));
		$this->set('maxProgress', $this->Session->read('subscribe.params.maxProgress'));
	}

    public function unsubscribe()
    {
        \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));

        $subscription = \Stripe\Subscription::retrieve($this->Session->read('Auth.User.Subscription.id'));

        if($subscription->cancel()) {

            $this->loadModel('User');
            $this->User->unsetSubscription();

			$this->Session->setFlash(__('You subscription has been canceled.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-success'
			));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function terms()
    {

        $this->layout = 'auth';
        $this->set( 'brand', 'Mark Allen Coaching' );
        $this->set( 'businessForm', 'LLC' );
    }
}
