<?php
App::uses('AppController', 'Controller');
/**
 * SafeguardTypes Controller
 *
 * @property SafeguardType $SafeguardType
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class SafeguardTypesController extends AppController {

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
		$this->SafeguardType->recursive = 0;
		$this->set('safeguardTypes', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->SafeguardType->exists($id)) {
			throw new NotFoundException(__('Invalid safeguard type'));
		}
		$options = array('conditions' => array('SafeguardType.' . $this->SafeguardType->primaryKey => $id));
		$this->set('safeguardType', $this->SafeguardType->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->SafeguardType->create();
			if ($this->SafeguardType->save($this->request->data)) {
				$this->Session->setFlash(__('The safeguard type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The safeguard type could not be saved. Please, try again.'), 'alert', array(
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
		if (!$this->SafeguardType->exists($id)) {
			throw new NotFoundException(__('Invalid safeguard type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->SafeguardType->save($this->request->data)) {
				$this->Session->setFlash(__('The safeguard type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The safeguard type could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('SafeguardType.' . $this->SafeguardType->primaryKey => $id));
			$this->request->data = $this->SafeguardType->find('first', $options);
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
		$this->SafeguardType->id = $id;
		if (!$this->SafeguardType->exists()) {
			throw new NotFoundException(__('Invalid safeguard type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->SafeguardType->delete()) {
			$this->Session->setFlash(__('The safeguard type has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The safeguard type could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
