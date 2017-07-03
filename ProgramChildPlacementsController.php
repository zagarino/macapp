<?php
App::uses('AppController', 'Controller');
/**
 * ProgramChildPlacements Controller
 *
 * @property ProgramChildPlacement $ProgramChildPlacement
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class ProgramChildPlacementsController extends AppController {

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
	public function admin_index()
	{
		$this->ProgramChildPlacement->recursive = 0;
		$this->Paginator->settings = array(
			/*
			'conditions' => array(
				'or' => array(
					array('ProgramChildPlacementType.value' => 'addrace-taper-up'),
					array('ProgramChildPlacementType.value' => 'addrace-another-race-taper-up'),
				),
			),
			'order' => array(
				'ProgramChildPlacement.id' => 'asc',
			),
			 */
			'limit' => 300,
		);

		$this->set('programChildPlacements', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->ProgramChildPlacement->exists($id)) {
			throw new NotFoundException(__('Invalid program child placement'));
		}
		$options = array('conditions' => array('ProgramChildPlacement.' . $this->ProgramChildPlacement->primaryKey => $id));
		$this->set('programChildPlacement', $this->ProgramChildPlacement->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->ProgramChildPlacement->create();
			if ($this->ProgramChildPlacement->save($this->request->data)) {
				$this->Session->setFlash(__('The program child placement has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program child placement could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}

		$races = $this->ProgramChildPlacement->Race->find('list', array(
			'conditions' => array(
				'not' => array(
					'value' => 'ironman',
				),
			),
		));

		$sports = $this->ProgramChildPlacement->Sport->find('list');
		$programChildPlacementTypes = $this->ProgramChildPlacement->ProgramChildPlacementType->find('list');
		$tassPlacementTypes = $this->ProgramChildPlacement->TassPlacementType->find('list');
		$this->set(compact('races', 'sports', 'tassPlacementTypes', 'programChildPlacementTypes'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->ProgramChildPlacement->exists($id)) {
			throw new NotFoundException(__('Invalid program child placement'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProgramChildPlacement->save($this->request->data)) {
				$this->Session->setFlash(__('The program child placement has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program child placement could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('ProgramChildPlacement.' . $this->ProgramChildPlacement->primaryKey => $id));
			$this->request->data = $this->ProgramChildPlacement->find('first', $options);
		}

		$races = $this->ProgramChildPlacement->Race->find('list', array(
			'conditions' => array(
				'not' => array(
					'value' => 'ironman',
				),
			),
		));

		$sports = $this->ProgramChildPlacement->Sport->find('list');
		$tassPlacementTypes = $this->ProgramChildPlacement->TassPlacementType->find('list');
		$programChildPlacementTypes = $this->ProgramChildPlacement->ProgramChildPlacementType->find('list');
		$this->set(compact('races', 'sports', 'tassPlacementTypes', 'programChildPlacementTypes'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->ProgramChildPlacement->id = $id;
		if (!$this->ProgramChildPlacement->exists()) {
			throw new NotFoundException(__('Invalid program child placement'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProgramChildPlacement->delete()) {
			$this->Session->setFlash(__('The program child placement has been deleted.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-info'
			));
		} else {
			$this->Session->setFlash(__('The program child placement could not be deleted. Please, try again.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
