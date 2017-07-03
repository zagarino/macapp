<?php
App::uses('AppController', 'Controller');
/**
 * CreditTypes Controller
 *
 * @property CreditType $CreditType
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class CreditTypesController extends AppController {

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
		$this->CreditType->recursive = 0;
		$this->set('creditTypes', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->CreditType->exists($id)) {
			throw new NotFoundException(__('Invalid credit type'));
		}
		$options = array('conditions' => array('CreditType.' . $this->CreditType->primaryKey => $id));
		$this->set('creditType', $this->CreditType->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->CreditType->create();
			if ($this->CreditType->save($this->request->data)) {
				$this->Session->setFlash(__('The credit type has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The credit type could not be saved. Please, try again.'));
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
		if (!$this->CreditType->exists($id)) {
			throw new NotFoundException(__('Invalid credit type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->CreditType->save($this->request->data)) {
				$this->Session->setFlash(__('The credit type has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The credit type could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('CreditType.' . $this->CreditType->primaryKey => $id));
			$this->request->data = $this->CreditType->find('first', $options);
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
		$this->CreditType->id = $id;
		if (!$this->CreditType->exists()) {
			throw new NotFoundException(__('Invalid credit type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->CreditType->delete()) {
			$this->Session->setFlash(__('The credit type has been deleted.'));
		} else {
			$this->Session->setFlash(__('The credit type could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
