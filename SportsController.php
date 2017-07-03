<?php
App::uses('AppController', 'Controller');
/**
 * Sports Controller
 *
 * @property Sport $Sport
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class SportsController extends AppController {

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
		$this->Sport->recursive = 0;
		$this->set('sports', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Sport->exists($id)) {
			throw new NotFoundException(__('Invalid sport'));
		}
		$options = array('conditions' => array('Sport.' . $this->Sport->primaryKey => $id));
		$this->set('sport', $this->Sport->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Sport->create();
			if ($this->Sport->save($this->request->data)) {
				$this->Session->setFlash(__('The sport has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sport could not be saved. Please, try again.'));
			}
		}
		$measurements = $this->Sport->Measurement->find('list');
		$this->set(compact('measurements'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Sport->exists($id)) {
			throw new NotFoundException(__('Invalid sport'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Sport->save($this->request->data)) {
				$this->Session->setFlash(__('The sport has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sport could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Sport.' . $this->Sport->primaryKey => $id));
			$this->request->data = $this->Sport->find('first', $options);
		}
		$measurements = $this->Sport->Measurement->find('list');
		$this->set(compact('measurements'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Sport->id = $id;
		if (!$this->Sport->exists()) {
			throw new NotFoundException(__('Invalid sport'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Sport->delete()) {
			$this->Session->setFlash(__('The sport has been deleted.'));
		} else {
			$this->Session->setFlash(__('The sport could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
