<?php
App::uses('AppController', 'Controller');
/**
 * WorkoutLevels Controller
 *
 * @property WorkoutLevel $WorkoutLevel
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class WorkoutLevelsController extends AppController {

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
		$this->WorkoutLevel->recursive = 0;
		$this->set('workoutLevels', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->WorkoutLevel->exists($id)) {
			throw new NotFoundException(__('Invalid workout level'));
		}
		$options = array('conditions' => array('WorkoutLevel.' . $this->WorkoutLevel->primaryKey => $id));
		$this->set('workoutLevel', $this->WorkoutLevel->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->WorkoutLevel->create();
			if ($this->WorkoutLevel->save($this->request->data)) {
				$this->Session->setFlash(__('The workout level has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout level could not be saved. Please, try again.'));
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
		if (!$this->WorkoutLevel->exists($id)) {
			throw new NotFoundException(__('Invalid workout level'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->WorkoutLevel->save($this->request->data)) {
				$this->Session->setFlash(__('The workout level has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout level could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('WorkoutLevel.' . $this->WorkoutLevel->primaryKey => $id));
			$this->request->data = $this->WorkoutLevel->find('first', $options);
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
		$this->WorkoutLevel->id = $id;
		if (!$this->WorkoutLevel->exists()) {
			throw new NotFoundException(__('Invalid workout level'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->WorkoutLevel->delete()) {
			$this->Session->setFlash(__('The workout level has been deleted.'));
		} else {
			$this->Session->setFlash(__('The workout level could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
