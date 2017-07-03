<?php
App::uses('AppController', 'Controller');
/**
 * WorkoutChildren Controller
 *
 * @property WorkoutChild $WorkoutChild
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class WorkoutChildrenController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->WorkoutChild->recursive = 0;
		$this->set('workoutChildren', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->WorkoutChild->exists($id)) {
			throw new NotFoundException(__('Invalid workout child'));
		}
		$options = array('conditions' => array('WorkoutChild.' . $this->WorkoutChild->primaryKey => $id));
		$this->set('workoutChild', $this->WorkoutChild->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->WorkoutChild->create();
			if ($this->WorkoutChild->save($this->request->data)) {
				$this->Session->setFlash(__('The workout child has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout child could not be saved. Please, try again.'));
			}
		}
		$programChildren = $this->WorkoutChild->ProgramChild->find('list');
		$sports = $this->WorkoutChild->Sport->find('list');
		$zones = $this->WorkoutChild->Zone->find('list');
		$tassPlacementTypes = $this->WorkoutChild->TassPlacementType->find('list');
		$strengthTypes = $this->WorkoutChild->StrengthType->find('list');
		$this->set(compact('programChildren', 'sports', 'zones', 'tassPlacementTypes', 'strengthTypes'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->WorkoutChild->exists($id)) {
			throw new NotFoundException(__('Invalid workout child'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->WorkoutChild->save($this->request->data)) {
				$this->Session->setFlash(__('The workout child has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout child could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('WorkoutChild.' . $this->WorkoutChild->primaryKey => $id));
			$this->request->data = $this->WorkoutChild->find('first', $options);
		}
		$programChildren = $this->WorkoutChild->ProgramChild->find('list');
		$sports = $this->WorkoutChild->Sport->find('list');
		$zones = $this->WorkoutChild->Zone->find('list');
		$tassPlacementTypes = $this->WorkoutChild->TassPlacementType->find('list');
		$strengthTypes = $this->WorkoutChild->StrengthType->find('list');
		$this->set(compact('programChildren', 'sports', 'zones', 'tassPlacementTypes', 'strengthTypes'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->WorkoutChild->id = $id;
		if (!$this->WorkoutChild->exists()) {
			throw new NotFoundException(__('Invalid workout child'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->WorkoutChild->delete()) {
			$this->Session->setFlash(__('The workout child has been deleted.'));
		} else {
			$this->Session->setFlash(__('The workout child could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->WorkoutChild->recursive = 0;
		$this->set('workoutChildren', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->WorkoutChild->exists($id)) {
			throw new NotFoundException(__('Invalid workout child'));
		}
		$options = array('conditions' => array('WorkoutChild.' . $this->WorkoutChild->primaryKey => $id));
		$this->set('workoutChild', $this->WorkoutChild->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->WorkoutChild->create();
			if ($this->WorkoutChild->save($this->request->data)) {
				$this->Session->setFlash(__('The workout child has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout child could not be saved. Please, try again.'));
			}
		}
		$programChildren = $this->WorkoutChild->ProgramChild->find('list');
		$sports = $this->WorkoutChild->Sport->find('list');
		$zones = $this->WorkoutChild->Zone->find('list');
		$tassPlacementTypes = $this->WorkoutChild->TassPlacementType->find('list');
		$strengthTypes = $this->WorkoutChild->StrengthType->find('list');
		$this->set(compact('programChildren', 'sports', 'zones', 'tassPlacementTypes', 'strengthTypes'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->WorkoutChild->exists($id)) {
			throw new NotFoundException(__('Invalid workout child'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->WorkoutChild->save($this->request->data)) {
				$this->Session->setFlash(__('The workout child has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout child could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('WorkoutChild.' . $this->WorkoutChild->primaryKey => $id));
			$this->request->data = $this->WorkoutChild->find('first', $options);
		}
		$programChildren = $this->WorkoutChild->ProgramChild->find('list');
		$sports = $this->WorkoutChild->Sport->find('list');
		$zones = $this->WorkoutChild->Zone->find('list');
		$tassPlacementTypes = $this->WorkoutChild->TassPlacementType->find('list');
		$strengthTypes = $this->WorkoutChild->StrengthType->find('list');
		$this->set(compact('programChildren', 'sports', 'zones', 'tassPlacementTypes', 'strengthTypes'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->WorkoutChild->id = $id;
		if (!$this->WorkoutChild->exists()) {
			throw new NotFoundException(__('Invalid workout child'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->WorkoutChild->delete()) {
			$this->Session->setFlash(__('The workout child has been deleted.'));
		} else {
			$this->Session->setFlash(__('The workout child could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
