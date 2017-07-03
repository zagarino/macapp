<?php
App::uses('AppController', 'Controller');
/**
 * TassPlacementTypes Controller
 *
 * @property TassPlacementType $TassPlacementType
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class TassPlacementTypesController extends AppController {

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


		$zones = $this->TassPlacementType->Zone->find('list');

		$this->set(compact('zones'));

		if ($this->request->is('post'))
		{
			$this->Session->write('TassPlacementType', $this->request->data);
		}
		else
		{
			$this->request->data = $this->Session->read('TassPlacementType');
		}

		$this->log($this->request->data);

		$conditions = array();

		if (!empty($this->request->data['Filter']))
		{
			foreach ($this->request->data['Filter'] as $key => $value)
			{
				if($key == 'zone_id' && !empty($value))
				{
					$conditions['TassPlacementType.zone_id'] = $value;
				}
			}
		}

		$this->Paginator->settings = array(
			'conditions' => $conditions,
			'limit' => 50,
			'order' => array(
				'TassPlacementType.zone_id' => 'asc',
			),
		);

		$this->TassPlacementType->recursive = 0;
		$this->set('tassPlacementTypes', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->TassPlacementType->exists($id)) {
			throw new NotFoundException(__('Invalid tass placement type'));
		}
		$options = array('conditions' => array('TassPlacementType.' . $this->TassPlacementType->primaryKey => $id));
		$this->set('tassPlacementType', $this->TassPlacementType->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->TassPlacementType->create();
			if ($this->TassPlacementType->save($this->request->data)) {
				$this->Session->setFlash(__('The tass placement type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The tass placement type could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		$zones = $this->TassPlacementType->Zone->find('list');
		$this->set(compact('zones'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->TassPlacementType->exists($id)) {
			throw new NotFoundException(__('Invalid tass placement type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->TassPlacementType->save($this->request->data)) {
				$this->Session->setFlash(__('The tass placement type has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The tass placement type could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('TassPlacementType.' . $this->TassPlacementType->primaryKey => $id));
			$this->request->data = $this->TassPlacementType->find('first', $options);
		}
		$zones = $this->TassPlacementType->Zone->find('list');
		$this->set(compact('zones'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->TassPlacementType->id = $id;
		if (!$this->TassPlacementType->exists()) {
			throw new NotFoundException(__('Invalid tass placement type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->TassPlacementType->delete()) {
			$this->Session->setFlash(__('The tass placement type has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The tass placement type could not be deleted. Please, try again.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
