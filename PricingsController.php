<?php
App::uses('AppController', 'Controller');
/**
 * Pricings Controller
 *
 * @property Pricing $Pricing
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class PricingsController extends AppController {

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
		$this->Pricing->recursive = 0;
		$this->set('pricings', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Pricing->exists($id)) {
			throw new NotFoundException(__('Invalid pricing'));
		}
		$options = array('conditions' => array('Pricing.' . $this->Pricing->primaryKey => $id));
		$this->set('pricing', $this->Pricing->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Pricing->create();
			if ($this->Pricing->save($this->request->data)) {
				$this->Session->setFlash(__('The pricing has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The pricing could not be saved. Please, try again.'));
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
		if (!$this->Pricing->exists($id)) {
			throw new NotFoundException(__('Invalid pricing'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Pricing->save($this->request->data)) {
				$this->Session->setFlash(__('The pricing has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The pricing could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Pricing.' . $this->Pricing->primaryKey => $id));
			$this->request->data = $this->Pricing->find('first', $options);
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
		$this->Pricing->id = $id;
		if (!$this->Pricing->exists()) {
			throw new NotFoundException(__('Invalid pricing'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Pricing->delete()) {
			$this->Session->setFlash(__('The pricing has been deleted.'));
		} else {
			$this->Session->setFlash(__('The pricing could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
