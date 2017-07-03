<?php
App::uses('AppController', 'Controller');
/**
 * ProgramChildPlacementTypes Controller
 *
 * @property ProgramChildPlacementType $ProgramChildPlacementType
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class ProgramChildPlacementTypesController extends AppController {

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
		$this->ProgramChildPlacementType->recursive = 0;
		$this->set('programChildPlacementTypes', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->ProgramChildPlacementType->exists($id)) {
			throw new NotFoundException(__('Invalid program child placement type'));
		}
		$options = array('conditions' => array('ProgramChildPlacementType.' . $this->ProgramChildPlacementType->primaryKey => $id));
		$this->set('programChildPlacementType', $this->ProgramChildPlacementType->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->ProgramChildPlacementType->create();
			if ($this->ProgramChildPlacementType->save($this->request->data)) {
				$this->Session->setFlash(__('The program child placement type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program child placement type could not be saved. Please, try again.'), 'alert', array(
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
		if (!$this->ProgramChildPlacementType->exists($id)) {
			throw new NotFoundException(__('Invalid program child placement type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProgramChildPlacementType->save($this->request->data)) {
				$this->Session->setFlash(__('The program child placement type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program child placement type could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('ProgramChildPlacementType.' . $this->ProgramChildPlacementType->primaryKey => $id));
			$this->request->data = $this->ProgramChildPlacementType->find('first', $options);
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
		$this->ProgramChildPlacementType->id = $id;
		if (!$this->ProgramChildPlacementType->exists()) {
			throw new NotFoundException(__('Invalid program child placement type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProgramChildPlacementType->delete()) {
			$this->Session->setFlash(__('The program child placement type has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The program child placement type could not be deleted. Please, try again.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
