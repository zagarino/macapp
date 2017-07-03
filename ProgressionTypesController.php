<?php
App::uses('AppController', 'Controller');
/**
 * ProgressionTypes Controller
 *
 * @property ProgressionType $ProgressionType
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class ProgressionTypesController extends AppController {

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
		$this->ProgressionType->recursive = 0;
		$this->set('progressionTypes', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->ProgressionType->exists($id)) {
			throw new NotFoundException(__('Invalid progression type'));
		}
		$options = array('conditions' => array('ProgressionType.' . $this->ProgressionType->primaryKey => $id));
		$this->set('progressionType', $this->ProgressionType->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->ProgressionType->create();
			if ($this->ProgressionType->save($this->request->data)) {
				$this->Session->setFlash(__('The progression type has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The progression type could not be saved. Please, try again.'));
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
		if (!$this->ProgressionType->exists($id)) {
			throw new NotFoundException(__('Invalid progression type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProgressionType->save($this->request->data)) {
				$this->Session->setFlash(__('The progression type has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The progression type could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ProgressionType.' . $this->ProgressionType->primaryKey => $id));
			$this->request->data = $this->ProgressionType->find('first', $options);
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
		$this->ProgressionType->id = $id;
		if (!$this->ProgressionType->exists()) {
			throw new NotFoundException(__('Invalid progression type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProgressionType->delete()) {
			$this->Session->setFlash(__('The progression type has been deleted.'));
		} else {
			$this->Session->setFlash(__('The progression type could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
