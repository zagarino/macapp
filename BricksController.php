<?php
App::uses('AppController', 'Controller');
/**
 * Bricks Controller
 *
 * @property Brick $Brick
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class BricksController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session','Training');

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->Brick->recursive = 0;
		$this->set('bricks', $this->Paginator->paginate());
	}

/**
 * admin_visual method
 *
 * @return void
 */
	public function admin_visual() {
		$races = $this->Brick->Race->find('list',array(
			'fields' => array(
				'name'
			),
			'contain' => false,
			'order' => array('sort' => 'asc')
		));
		foreach ($races as $race => $value)
		{
			$bricks = $this->Brick->find('list', array(
				'order' => array('Brick.training_length' => 'asc'),
				'contain' => array(
					'Race'
				),
				'fields' => array(
					'week',
					'value',
					'training_length',
				),
				'conditions' => array('race_id' => $race)
			));
			$groupedBricks[$value] = $bricks;
		}

		$this->log($groupedBricks);
		$this->set('bricks', $groupedBricks);
		$this->set('max', $this->Training->max);
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Brick->exists($id)) {
			throw new NotFoundException(__('Invalid brick'));
		}
		$options = array('conditions' => array('Brick.' . $this->Brick->primaryKey => $id));
		$this->set('brick', $this->Brick->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Brick->create();
			if ($this->Brick->save($this->request->data)) {
				$this->Session->setFlash(__('The brick has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The brick could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}

		$races = $this->Brick->Race->find('list');
		$weeks = $this->Training->getWeeks('week', true);
		$trainingLengths = $this->Training->getWeeks('trainingweek');
		$this->set(compact('races', 'trainingLengths', 'weeks'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Brick->exists($id)) {
			throw new NotFoundException(__('Invalid brick'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Brick->save($this->request->data)) {
				$this->Session->setFlash(__('The brick has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The brick could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Brick.' . $this->Brick->primaryKey => $id));
			$this->request->data = $this->Brick->find('first', $options);
		}

		$races = $this->Brick->Race->find('list');
		$weeks = $this->Training->getWeeks('week', true);
		$trainingLengths = $this->Training->getWeeks('trainingweek');
		$this->set(compact('races', 'trainingLengths', 'weeks'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Brick->id = $id;
		if (!$this->Brick->exists()) {
			throw new NotFoundException(__('Invalid brick'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Brick->delete()) {
			$this->Session->setFlash(__('The brick has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The brick could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
