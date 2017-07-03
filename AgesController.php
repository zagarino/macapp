<?php
App::uses('AppController', 'Controller');
/**
 * Ages Controller
 *
 * @property Age $Age
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class AgesController extends AppController {

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
		$this->Age->recursive = 0;
		$this->set('ages', $this->Paginator->paginate());

		$this->log($this->Age->findRanges());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Age->exists($id)) {
			throw new NotFoundException(__('Invalid age'));
		}
		$options = array('conditions' => array('Age.' . $this->Age->primaryKey => $id));
		$this->set('age', $this->Age->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Age->create();
			if ($this->Age->save($this->request->data)) {
				$this->Session->setFlash(__('The age has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The age could not be saved. Please, try again.'));
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
		if (!$this->Age->exists($id)) {
			throw new NotFoundException(__('Invalid age'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Age->save($this->request->data)) {
				$this->Session->setFlash(__('The age has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The age could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Age.' . $this->Age->primaryKey => $id));
			$this->request->data = $this->Age->find('first', $options);
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
		$this->Age->id = $id;
		if (!$this->Age->exists()) {
			throw new NotFoundException(__('Invalid age'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Age->delete()) {
			$this->Session->setFlash(__('The age has been deleted.'));
		} else {
			$this->Session->setFlash(__('The age could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
