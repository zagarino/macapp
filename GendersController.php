<?php
App::uses('AppController', 'Controller');
/**
 * Genders Controller
 *
 * @property Gender $Gender
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class GendersController extends AppController {

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
		$this->Gender->recursive = 0;
		$this->set('genders', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Gender->exists($id)) {
			throw new NotFoundException(__('Invalid gender'));
		}
		$options = array('conditions' => array('Gender.' . $this->Gender->primaryKey => $id));
		$this->set('gender', $this->Gender->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Gender->create();
			if ($this->Gender->save($this->request->data)) {
				$this->Session->setFlash(__('The gender has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The gender could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
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
		if (!$this->Gender->exists($id)) {
			throw new NotFoundException(__('Invalid gender'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Gender->save($this->request->data)) {
				$this->Session->setFlash(__('The gender has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The gender could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Gender.' . $this->Gender->primaryKey => $id));
			$this->request->data = $this->Gender->find('first', $options);
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
		$this->Gender->id = $id;
		if (!$this->Gender->exists()) {
			throw new NotFoundException(__('Invalid gender'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Gender->delete()) {
			$this->Session->setFlash(__('The gender has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The gender could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
