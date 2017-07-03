<?php
App::uses('AppController', 'Controller');
/**
 * Races Controller
 *
 * @property Race $Race
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class RacesController extends AppController {

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
		$this->Race->recursive = 0;
		$this->set('races', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Race->exists($id)) {
			throw new NotFoundException(__('Invalid race'));
		}
		$options = array('conditions' => array('Race.' . $this->Race->primaryKey => $id));
		$this->set('race', $this->Race->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Race->create();
			if ($this->Race->save($this->request->data)) {
				$this->Session->setFlash(__('The race has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The race could not be saved. Please, try again.'), 'alert', array(
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
		if (!$this->Race->exists($id)) {
			throw new NotFoundException(__('Invalid race'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Race->save($this->request->data)) {
				$this->Session->setFlash(__('The race has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The race could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Race.' . $this->Race->primaryKey => $id));
			$this->request->data = $this->Race->find('first', $options);
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
		$this->Race->id = $id;
		if (!$this->Race->exists()) {
			throw new NotFoundException(__('Invalid race'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Race->delete()) {
			$this->Session->setFlash(__('The race has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The race could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
