<?php
App::uses('AppController', 'Controller');
/**
 * Progressions Controller
 *
 * @property Progression $Progression
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class ProgressionsController extends AppController
{

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
		$this->Progression->recursive = 0;
		$this->set('progressions', $this->Paginator->paginate());

		$values = $this->Progression->find('all');
		$sports = $this->Progression->Sport->find('all', array(
			'conditions' => array(
				'not' => array(
					'Sport.value' => 'strength',
				),
			),
		));


		$this->set('values', $values);
		$this->set('sports', $sports);
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Progression->exists($id)) {
			throw new NotFoundException(__('Invalid progression'));
		}
		$options = array('conditions' => array('Progression.' . $this->Progression->primaryKey => $id));
		$this->set('progression', $this->Progression->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Progression->create();
			if ($this->Progression->save($this->request->data))
			{
				$this->Session->setFlash(__('The progression has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The progression could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		$progressionTypes = $this->Progression->ProgressionType->find('list');
		$sports = $this->Progression->Sport->find('list');
		$this->set(compact('progressionTypes', 'sports'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Progression->exists($id)) {
			throw new NotFoundException(__('Invalid progression'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Progression->save($this->request->data)) {
				$this->Session->setFlash(__('The progression has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The progression could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Progression.' . $this->Progression->primaryKey => $id));
			$this->request->data = $this->Progression->find('first', $options);
		}
		$progressionTypes = $this->Progression->ProgressionType->find('list');
		$sports = $this->Progression->Sport->find('list');
		$this->set(compact('progressionTypes', 'sports'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Progression->id = $id;
		if (!$this->Progression->exists()) {
			throw new NotFoundException(__('Invalid progression'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Progression->delete()) {
			$this->Session->setFlash(__('The progression has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The progression could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
