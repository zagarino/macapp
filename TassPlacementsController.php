<?php
App::uses('AppController', 'Controller');
/**
 * TassPlacements Controller
 *
 * @property TassPlacement $TassPlacement
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class TassPlacementsController extends AppController {

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
		$this->TassPlacement->recursive = 0;
		$this->set('tassPlacements', $this->Paginator->paginate());
	}

/**
 * admin_visual method
 *
 * @return void
 */
	public function admin_visual() {
		if(empty($this->request->named['sport']))
		{
			$this->redirect(array('action' => 'index'));
		}

		$sport = $this->TassPlacement->Sport->find('first', array(
			'conditions' => array(
				'Sport.value' => $this->request->named['sport'],
				'not' => array(
					'Sport.value' => 'strength'
				),
			),
		));

		if(empty($sport))
		{
			$this->redirect(array('action' => 'index'));
		}

		$ranges = $this->TassPlacement->Age->findRanges();
		foreach ($ranges as $range)
		{
			if (!empty($range['Age']['range_max']))
			{
				$ages[$range['Age']['id']] = $range['Age']['range_min'] . ' - ' . $range['Age']['range_max'];
			}
			else
			{
				$ages[$range['Age']['id']] = $range['Age']['range_min'] . ' +';
			}
		}


		$placements = $this->TassPlacement->find('all', array(
			'conditions' => array(
				'Sport.value' => $this->request->named['sport'],
			),
			'contain' => array(
				'Sport',
				'TassPlacementType' => array(
					'Zone'
				),
			),
		));

		$this->set('sport', $sport);
		$this->set(compact('ages'));
		$this->set('placements', $placements);
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->TassPlacement->exists($id)) {
			throw new NotFoundException(__('Invalid tass placement'));
		}
		$options = array('conditions' => array('TassPlacement.' . $this->TassPlacement->primaryKey => $id));
		$this->set('tassPlacement', $this->TassPlacement->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			if (isset($this->request->data['TassPlacement']['training_length']))
			{
				$this->Session->write('TassPlacement.training_length', $this->request->data['TassPlacement']['training_length']);
			}
			$this->TassPlacement->create();
			if ($this->TassPlacement->save($this->request->data)) {
				$this->Session->setFlash(__('The tass placement has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'add'));
			} else {
				$this->Session->setFlash(__('The tass placement could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		else
		{
			$this->request->data['TassPlacement']['training_length'] = $this->Session->read('TassPlacement.training_length');
		}
		$sports = $this->TassPlacement->Sport->find('list');

		$ranges = $this->TassPlacement->Age->findRanges();
		foreach ($ranges as $range)
		{
			if (!empty($range['Age']['range_max']))
			{
				$ages[$range['Age']['id']] = $range['Age']['range_min'] . ' - ' . $range['Age']['range_max'];
			}
			else
			{
				$ages[$range['Age']['id']] = $range['Age']['range_min'] . ' +';
			}
		}

		$tassPlacementTypes = $this->TassPlacement->TassPlacementType->find('list');
		$this->set(compact('sports', 'ages', 'tassPlacementTypes'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->TassPlacement->exists($id)) {
			throw new NotFoundException(__('Invalid tass placement'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->TassPlacement->save($this->request->data)) {
				$this->Session->setFlash(__('The tass placement has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The tass placement could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('TassPlacement.' . $this->TassPlacement->primaryKey => $id));
			$this->request->data = $this->TassPlacement->find('first', $options);
		}
		$sports = $this->TassPlacement->Sport->find('list');
		$ages = $this->TassPlacement->Age->find('list');
		$tassPlacementTypes = $this->TassPlacement->TassPlacementType->find('list');
		$this->set(compact('sports', 'ages', 'tassPlacementTypes'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->TassPlacement->id = $id;
		if (!$this->TassPlacement->exists()) {
			throw new NotFoundException(__('Invalid tass placement'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->TassPlacement->delete()) {
			$this->Session->setFlash(__('The tass placement has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The tass placement could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
