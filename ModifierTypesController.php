<?php
App::uses('AppController', 'Controller');
/**
 * ModifierTypes Controller
 *
 * @property ModifierType $ModifierType
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class ModifierTypesController extends AppController {

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
		$this->ModifierType->recursive = 0;
		$this->set('modifierTypes', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->ModifierType->exists($id)) {
			throw new NotFoundException(__('Invalid modifier type'));
		}
		$options = array('conditions' => array('ModifierType.' . $this->ModifierType->primaryKey => $id));
		$this->set('modifierType', $this->ModifierType->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->ModifierType->create();
			if ($this->ModifierType->save($this->request->data)) {
				$this->Session->setFlash(__('The modifier type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The modifier type could not be saved. Please, try again.'), 'alert', array(
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
		if (!$this->ModifierType->exists($id)) {
			throw new NotFoundException(__('Invalid modifier type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ModifierType->save($this->request->data)) {
				$this->Session->setFlash(__('The modifier type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The modifier type could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('ModifierType.' . $this->ModifierType->primaryKey => $id));
			$this->request->data = $this->ModifierType->find('first', $options);
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
		$this->ModifierType->id = $id;
		if (!$this->ModifierType->exists()) {
			throw new NotFoundException(__('Invalid modifier type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ModifierType->delete()) {
			$this->Session->setFlash(__('The modifier type has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The modifier type could not be deleted. Please, try again.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
