<?php
App::uses('AppController', 'Controller');
/**
 * Strengths Controller
 *
 * @property Strength $Strength
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class StrengthsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session', 'Training');

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->Strength->recursive = 0;
		$this->set('strengths', $this->Paginator->paginate());
	}

	public function admin_peak()
	{
		$strengths = $this->Strength->find('all', array(
			'conditions' => array(
				'Strength.type' => 'peak',
			),
		));
		return $this->_types($strengths, 'peak');
	}

	public function admin_maintenance()
	{
		$strengths = $this->Strength->find('all', array(
			'conditions' => array(
				'Strength.type' => 'maintenance',
			),
		));
		return $this->_types($strengths, 'maintenance');
	}

	protected function _types($strengths, $type)
	{
		foreach ($strengths as $strength) {
			$sortedStrength[$strength['Strength']['training_length']][$strength['Strength']['week']] = $strength;
		}

		$this->set('strengths', $sortedStrength);
		$this->set('type', $type);

		$this->render('admin_type');
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Strength->exists($id)) {
			throw new NotFoundException(__('Invalid strength'));
		}
		$options = array('conditions' => array('Strength.' . $this->Strength->primaryKey => $id));
		$this->set('strength', $this->Strength->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Strength->create();
			if ($this->Strength->save($this->request->data)) {
				$this->Session->setFlash(__('The strength has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The strength could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			foreach (array('training_length', 'week', 'type') as $attribute)
			{
				if (isset($this->request->params['named'][$attribute])) {
					$this->request->data['Strength'][$attribute] = $this->request->params['named'][$attribute];
				}
			}
		}
		$zones = $this->Strength->Zone->find('list');
		$weeks = $this->Training->getWeeks('week', true);
		$trainingLengths = $this->Training->getWeeks('trainingweek');
		$this->set(compact('zones', 'weeks', 'trainingLengths'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Strength->exists($id)) {
			throw new NotFoundException(__('Invalid strength'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Strength->save($this->request->data)) {
				$this->Session->setFlash(__('The strength has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The strength could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Strength.' . $this->Strength->primaryKey => $id));
			$this->request->data = $this->Strength->find('first', $options);
		}
		$zones = $this->Strength->Zone->find('list');
		$weeks = $this->Training->getWeeks('week', true);
		$trainingLengths = $this->Training->getWeeks('trainingweek');
		$this->set(compact('zones', 'weeks', 'trainingLengths'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Strength->id = $id;
		if (!$this->Strength->exists()) {
			throw new NotFoundException(__('Invalid strength'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Strength->delete()) {
			$this->Session->setFlash(__('The strength has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The strength could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
