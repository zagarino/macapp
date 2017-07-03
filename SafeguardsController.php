<?php
App::uses('AppController', 'Controller');
/**
 * Safeguards Controller
 *
 * @property Safeguard $Safeguard
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class SafeguardsController extends AppController {

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
		$this->Safeguard->recursive = 0;
		$this->set('safeguards', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Safeguard->exists($id)) {
			throw new NotFoundException(__('Invalid safeguard'));
		}
		$options = array('conditions' => array('Safeguard.' . $this->Safeguard->primaryKey => $id));
		$this->set('safeguard', $this->Safeguard->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Safeguard->create();
			if ($this->Safeguard->save($this->request->data)) {
				$this->Session->setFlash(__('The safeguard has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The safeguard could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		$safeguardTypes = $this->Safeguard->SafeguardType->find('list');
		$races = $this->Safeguard->Race->find('list');
		$sports = $this->Safeguard->Sport->find('list');
		$this->set(compact('safeguardTypes', 'races', 'sports'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Safeguard->exists($id)) {
			throw new NotFoundException(__('Invalid safeguard'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Safeguard->save($this->request->data)) {
				$this->Session->setFlash(__('The safeguard has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The safeguard could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Safeguard.' . $this->Safeguard->primaryKey => $id));
			$this->request->data = $this->Safeguard->find('first', $options);
		}
		$safeguardTypes = $this->Safeguard->SafeguardType->find('list');
		$races = $this->Safeguard->Race->find('list');
		$sports = $this->Safeguard->Sport->find('list');
		$this->set(compact('safeguardTypes', 'races', 'sports'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Safeguard->id = $id;
		if (!$this->Safeguard->exists()) {
			throw new NotFoundException(__('Invalid safeguard'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Safeguard->delete()) {
			$this->Session->setFlash(__('The safeguard has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The safeguard could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
