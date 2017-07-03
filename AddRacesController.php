<?php
App::uses('AppController', 'Controller');
/**
 * AddRaces Controller
 *
 * @property AddRace $AddRace
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class AddRacesController extends AppController {

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
		$this->AddRace->recursive = 0;
		$this->set('addRaces', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->AddRace->exists($id)) {
			throw new NotFoundException(__('Invalid add race'));
		}
		$options = array('conditions' => array('AddRace.' . $this->AddRace->primaryKey => $id));
		$this->set('addRace', $this->AddRace->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->AddRace->create();
			if ($this->AddRace->save($this->request->data)) {
				$this->Session->setFlash(__('The add race has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The add race could not be saved. Please, try again.'));
			}
		}

		$programs = $this->AddRace->Program->find('list', array(
			'conditions' => array(
				'Program.user_id' => $this->Auth->user('id'),
			),
		));

		$races = $this->AddRace->Race->find('list', array(
			'fields' => array(
				'Race.value', 'Race.name',
			),
			'conditions' => array(
				'not' => array(
					'Race.value' => 'ironman',
				),
			),
		));

		$sports = $this->AddRace->Sport->find('list', array(
			'fields' => array(
				'Sport.value', 'Sport.name',
			),
			'conditions' => array(
				'not' => array(
					'Sport.value' => 'strength',
				),
			),
		));

		$raceTypes = array_merge($races, $sports);

		foreach($raceTypes as $key => $raceType)
		{
			$raceTypes[$key] = $raceTypes[$key] . ' ' . __('Added Race');
		}

		$this->log($raceTypes);

		$this->set(compact('programs', 'raceTypes'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->AddRace->exists($id)) {
			throw new NotFoundException(__('Invalid add race'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->AddRace->save($this->request->data)) {
				$this->Session->setFlash(__('The add race has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The add race could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('AddRace.' . $this->AddRace->primaryKey => $id));
			$this->request->data = $this->AddRace->find('first', $options);
		}
		$programs = $this->AddRace->Program->find('list');
		$races = $this->AddRace->Race->find('list');
		$sports = $this->AddRace->Sport->find('list');
		$this->set(compact('programs', 'races', 'sports'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->AddRace->id = $id;
		if (!$this->AddRace->exists()) {
			throw new NotFoundException(__('Invalid add race'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->AddRace->delete()) {
			$this->Session->setFlash(__('The add race has been deleted.'));
		} else {
			$this->Session->setFlash(__('The add race could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->AddRace->recursive = 0;
		$this->set('addRaces', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->AddRace->exists($id)) {
			throw new NotFoundException(__('Invalid add race'));
		}
		$options = array('conditions' => array('AddRace.' . $this->AddRace->primaryKey => $id));
		$this->set('addRace', $this->AddRace->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->AddRace->create();
			if ($this->AddRace->save($this->request->data)) {
				$this->Session->setFlash(__('The add race has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The add race could not be saved. Please, try again.'));
			}
		}
		$programs = $this->AddRace->Program->find('list');
		$races = $this->AddRace->Race->find('list');
		$sports = $this->AddRace->Sport->find('list');
		$this->set(compact('programs', 'races', 'sports'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->AddRace->exists($id)) {
			throw new NotFoundException(__('Invalid add race'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->AddRace->save($this->request->data)) {
				$this->Session->setFlash(__('The add race has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The add race could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('AddRace.' . $this->AddRace->primaryKey => $id));
			$this->request->data = $this->AddRace->find('first', $options);
		}
		$programs = $this->AddRace->Program->find('list');
		$races = $this->AddRace->Race->find('list');
		$sports = $this->AddRace->Sport->find('list');
		$this->set(compact('programs', 'races', 'sports'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->AddRace->id = $id;
		if (!$this->AddRace->exists()) {
			throw new NotFoundException(__('Invalid add race'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->AddRace->delete()) {
			$this->Session->setFlash(__('The add race has been deleted.'));
		} else {
			$this->Session->setFlash(__('The add race could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}

	public function start($id = null)
	{
		if (!$this->AddRace->Program->exists($id))
		{
			throw new NotFoundException(__('Invalid program'));
		}

		$this->Session->write('addRace.data.AddRace.program_id', $id);

		$this->Session->delete('addRace');

		$this->Session->write('addRace.params.maxProgress', 1);
		$this->Session->write('addRace.params.stepCurrent', 1);

		return $this->redirect(array('action' => 'step1'));
	}

	public function step1()
	{
		$this->Session->write('addRace.data.AddRace.program_id', '202');

		if (is_int($this->Session->read('addRace.params.maxProgress')))
		{
			$this->set('maxProgress', $this->Session->read('addRace.params.maxProgress'));
		}
		else
		{
			$this->redirect(array('action' => 'start'));
		}

		$this->Session->write('addRace.params.stepCurrent', 1);
		$this->set('stepCurrent', $this->Session->read('addRace.params.stepCurrent'));

		$this->set('stepNames', $this->stepNames);

		if ($this->request->is('post'))
		{
			$this->request->data['Program']['user_id'] = $this->Auth->user('id'); //check!
			$this->AddRace->set($this->request->data);
			if ($this->AddRace->validates())
			{
				$this->Session->write('addRace.params.maxProgress', 2);

				$prevSessionData = $this->Session->read('addRace.data');
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$this->Session->write('addRace.data', $currentSessionData);

				$this->redirect(array('action' => 'step2'));
			}
		}
		else
		{
			$this->request->data = $this->Session->read('addRace.data');
		}

		$programs = $this->AddRace->Program->find('list', array(
			'conditions' => array(
				'Program.user_id' => $this->Auth->user('id'),
			),
		));

		$races = $this->AddRace->Race->find('list', array(
			'fields' => array(
				'Race.value', 'Race.name',
			),
			'conditions' => array(
				'not' => array(
					'Race.value' => 'ironman',
				),
			),
		));

		$sports = $this->AddRace->Sport->find('list', array(
			'fields' => array(
				'Sport.value', 'Sport.name',
			),
			'conditions' => array(
				'not' => array(
					'Sport.value' => 'strength',
				),
			),
		));

		$raceTypes = array_merge($races, $sports);

		foreach($raceTypes as $key => $raceType)
		{
			$raceTypes[$key] = $raceTypes[$key] . ' ' . __('Added Race');
		}

		$this->log($raceTypes);

		$this->set(compact('programs', 'raceTypes'));
	}

	protected function _step($step, $redirect = true)
	{
		if (is_int($this->Session->read('addRace.params.stepCurrent')))
		{
			if ($this->Session->read('addRace.params.maxProgress') < $step)
			{
				$this->redirect(array('action' => 'step'.$this->Session->read('addRace.params.stepCurrent')));
			}
			else
			{
				$this->Session->write('addRace.params.stepCurrent', $step);
			}
		}
		else
		{
			$this->redirect(array('action' => 'start'));
		}

		if ($this->request->is('post'))
		{
			$this->AddRace->set($this->request->data);
			if ($this->AddRace->validates())
			{
				$this->Session->write('addRace.params.maxProgress', $step+1);

				$prevSessionData = $this->Session->read('addRace.data');
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$this->Session->write('addRace.data', $currentSessionData);

				if($redirect)
				{
					$this->redirect(array('action' => 'step'.($step+1)));
				}
			}
		}
	else
		{
			$this->request->data = $this->Session->read('addRace.data');
		}

		$this->set('stepCurrent', $this->Session->read('addRace.params.stepCurrent'));
		$this->set('maxProgress', $this->Session->read('addRace.params.maxProgress'));
		$this->set('stepNames', $this->stepNames);
	}
}
