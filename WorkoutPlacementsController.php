<?php
App::uses('AppController', 'Controller');
/**
 * WorkoutPlacements Controller
 *
 * @property WorkoutPlacement $WorkoutPlacement
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class WorkoutPlacementsController extends AppController {

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
		$this->WorkoutPlacement->recursive = 0;
		$this->set('workoutPlacements', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->WorkoutPlacement->exists($id)) {
			throw new NotFoundException(__('Invalid workout placement'));
		}
		$options = array('conditions' => array('WorkoutPlacement.' . $this->WorkoutPlacement->primaryKey => $id));
		$this->set('workoutPlacement', $this->WorkoutPlacement->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->WorkoutPlacement->create();
			if ($this->WorkoutPlacement->save($this->request->data)) {
				$this->Session->setFlash(__('The workout placement has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout placement could not be saved. Please, try again.'));
			}
		}
		$zones = $this->WorkoutPlacement->Zone->find('list');
		$sports = $this->WorkoutPlacement->Sport->find('list');
		$workoutLevels = $this->WorkoutPlacement->WorkoutLevel->find('list');
		$this->set(compact('zones', 'sports', 'workoutLevels'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->WorkoutPlacement->exists($id)) {
			throw new NotFoundException(__('Invalid workout placement'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->WorkoutPlacement->save($this->request->data)) {
				$this->Session->setFlash(__('The workout placement has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout placement could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('WorkoutPlacement.' . $this->WorkoutPlacement->primaryKey => $id));
			$this->request->data = $this->WorkoutPlacement->find('first', $options);
		}
		$zones = $this->WorkoutPlacement->Zone->find('list');
		$sports = $this->WorkoutPlacement->Sport->find('list');
		$workoutLevels = $this->WorkoutPlacement->WorkoutLevel->find('list');
		$this->set(compact('zones', 'sports', 'workoutLevels'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->WorkoutPlacement->id = $id;
		if (!$this->WorkoutPlacement->exists()) {
			throw new NotFoundException(__('Invalid workout placement'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->WorkoutPlacement->delete()) {
			$this->Session->setFlash(__('The workout placement has been deleted.'));
		} else {
			$this->Session->setFlash(__('The workout placement could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
