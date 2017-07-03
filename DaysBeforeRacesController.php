<?php
App::uses('AppController', 'Controller');
/**
 * DaysBeforeRaces Controller
 *
 * @property DaysBeforeRace $DaysBeforeRace
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class DaysBeforeRacesController extends AppController {

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
		$this->DaysBeforeRace->recursive = 0;
		$this->set('daysBeforeRaces', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->DaysBeforeRace->exists($id)) {
			throw new NotFoundException(__('Invalid days before race'));
		}
		$options = array('conditions' => array('DaysBeforeRace.' . $this->DaysBeforeRace->primaryKey => $id));
		$this->set('daysBeforeRace', $this->DaysBeforeRace->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->DaysBeforeRace->create();
			if ($this->DaysBeforeRace->save($this->request->data)) {
				$this->Session->setFlash(__('The days before race has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The days before race could not be saved. Please, try again.'));
			}
		}
		$races = $this->DaysBeforeRace->Race->find('list');
		$zones = $this->DaysBeforeRace->Zone->find('list');
		$sports = $this->DaysBeforeRace->Sport->find('list');
		$this->set(compact('races', 'zones', 'sports'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->DaysBeforeRace->exists($id)) {
			throw new NotFoundException(__('Invalid days before race'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->DaysBeforeRace->save($this->request->data)) {
				$this->Session->setFlash(__('The days before race has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The days before race could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('DaysBeforeRace.' . $this->DaysBeforeRace->primaryKey => $id));
			$this->request->data = $this->DaysBeforeRace->find('first', $options);
		}
		$races = $this->DaysBeforeRace->Race->find('list');
		$zones = $this->DaysBeforeRace->Zone->find('list');
		$sports = $this->DaysBeforeRace->Sport->find('list');
		$this->set(compact('races', 'zones', 'sports'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->DaysBeforeRace->id = $id;
		if (!$this->DaysBeforeRace->exists()) {
			throw new NotFoundException(__('Invalid days before race'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->DaysBeforeRace->delete()) {
			$this->Session->setFlash(__('The days before race has been deleted.'));
		} else {
			$this->Session->setFlash(__('The days before race could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
