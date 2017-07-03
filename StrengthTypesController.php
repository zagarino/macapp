<?php
App::uses('AppController', 'Controller');
/**
 * StrengthTypes Controller
 *
 * @property StrengthType $StrengthType
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class StrengthTypesController extends AppController {

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
		$this->StrengthType->recursive = 0;
		$this->set('strengthTypes', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->StrengthType->exists($id)) {
			throw new NotFoundException(__('Invalid strength type'));
		}
		$options = array('conditions' => array('StrengthType.' . $this->StrengthType->primaryKey => $id));
		$this->set('strengthType', $this->StrengthType->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->StrengthType->create();
			if ($this->StrengthType->save($this->request->data)) {
				$this->Session->setFlash(__('The strength type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The strength type could not be saved. Please, try again.'), 'alert', array(
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
		if (!$this->StrengthType->exists($id)) {
			throw new NotFoundException(__('Invalid strength type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->StrengthType->save($this->request->data)) {
				$this->Session->setFlash(__('The strength type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The strength type could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('StrengthType.' . $this->StrengthType->primaryKey => $id));
			$this->request->data = $this->StrengthType->find('first', $options);
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
		$this->StrengthType->id = $id;
		if (!$this->StrengthType->exists()) {
			throw new NotFoundException(__('Invalid strength type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->StrengthType->delete()) {
			$this->Session->setFlash(__('The strength type has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The strength type could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
