<?php
App::uses('AppController', 'Controller');
/**
 * Plans Controller
 *
 * @property Plan $Plan
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class PlansController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session');

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
        \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));
        $plans = \Stripe\Plan::all(array("limit" => 10));
        $this->set('plans', $plans->data);
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$id) {
			throw new NotFoundException(__('No plan given.'));
		}

        \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));
        $plan = \Stripe\Plan::retrieve($id);
        $this->log($plan);
        $this->set('plan', $plan);
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if($this->request->is('post')) {
            $this->Plan->set($this->request->data);
            if ($this->Plan->validates()) {
                $plan = $this->request->data['Plan'];
                $plan = array(
                    'name' => $plan['name'],
                    'id' => $plan['plan_id'],
                    'interval_count' => $plan['interval_count'],
                    'interval' => 'month',
                    'currency' => 'usd',
                    'amount' =>  $plan['amount']
                );

                \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));
                if(\Stripe\Plan::create($plan)) {
    				$this->Session->setFlash(__('The plan has been saved.'), 'alert', array(
    					'plugin' => 'BoostCake',
    					'class' => 'alert-success'
    				));
    				return $this->redirect(array('action' => 'index'));
                }
            }
        }
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$id) {
			throw new NotFoundException(__('No plan given.'));
		}

        \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));
        $plan = \Stripe\Plan::retrieve($id);

		if ($this->request->is(array('post', 'put'))) {
            $this->Plan->set($this->request->data);
            if ($this->Plan->validates()) {
                $plan->name = $this->request->data['Plan']['name'];

                if($plan->save()) {
    				$this->Session->setFlash(__('The plan has been saved.'), 'alert', array(
    					'plugin' => 'BoostCake',
    					'class' => 'alert-success'
    				));
    				return $this->redirect(array('action' => 'index'));
                }

			} else {
				$this->Session->setFlash(__('The plan could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data['Plan']['name'] = $plan->name;
		}
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		if (!$id) {
			throw new NotFoundException(__('No plan given.'));
		}

		$this->request->allowMethod('post', 'delete');

        \Stripe\Stripe::setApiKey(Configure::read('Stripe.secret_key'));
        $plan = \Stripe\Plan::retrieve($id);

		if ($plan->delete()) {
			$this->Session->setFlash(__('The plan has been deleted.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-info'
			));
		} else {
			$this->Session->setFlash(__('The plan could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
