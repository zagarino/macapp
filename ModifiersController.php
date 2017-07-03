<?php
App::uses('AppController', 'Controller');
/**
 * Modifiers Controller
 *
 * @property Modifier $Modifier
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class ModifiersController extends AppController {

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
		$this->Modifier->recursive = 0;
		$this->set('modifiers', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Modifier->exists($id)) {
			throw new NotFoundException(__('Invalid modifier'));
		}
		$options = array('conditions' => array('Modifier.' . $this->Modifier->primaryKey => $id));
		$this->set('modifier', $this->Modifier->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Modifier->create();
			if ($this->Modifier->save($this->request->data)) {
				$this->Session->setFlash(__('The modifier has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The modifier could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		$modifierTypes = $this->Modifier->ModifierType->find('list');
		$sports = $this->Modifier->Sport->find('list');
		$zones = $this->Modifier->Zone->find('list');
		$races = $this->Modifier->Race->find('list');
		$this->set(compact('modifierTypes', 'sports', 'zones', 'races'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Modifier->exists($id)) {
			throw new NotFoundException(__('Invalid modifier'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Modifier->save($this->request->data)) {
				$this->Session->setFlash(__('The modifier has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The modifier could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Modifier.' . $this->Modifier->primaryKey => $id));
			$this->request->data = $this->Modifier->find('first', $options);
		}
		$modifierTypes = $this->Modifier->ModifierType->find('list');
		$sports = $this->Modifier->Sport->find('list');
		$zones = $this->Modifier->Zone->find('list');
		$races = $this->Modifier->Race->find('list');
		$this->set(compact('modifierTypes', 'sports', 'zones', 'races'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Modifier->id = $id;
		if (!$this->Modifier->exists()) {
			throw new NotFoundException(__('Invalid modifier'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Modifier->delete()) {
			$this->Session->setFlash(__('The modifier has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The modifier could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
