<?php
App::uses('AppController', 'Controller');
/**
 * Workouts Controller
 *
 * @property Workout $Workout
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class WorkoutsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session');

	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Security->unlockedActions = array('ajaxMove', 'ajaxShape', 'ajaxComplete', 'ajaxAll');
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Workout->exists($id)) {
			throw new NotFoundException(__('Invalid workout'));
		}

		$workout = $this->Workout->find('first', array(
			'conditions' => array(
				'Workout.' . $this->Workout->primaryKey => $id,
			),
			'contain' => array(
				'Program',
				'Sport' => array(
					'Measurement',
				),
				'Zone',
				'StrengthType',
			),
		));

		if (!$workout['Workout']['visible'])
		{
			throw new NotFoundException(__('Invalid workout'));
		}

		$this->set('workout', $workout);
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Workout->exists($id)) {
			throw new NotFoundException(__('Invalid workout'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Workout->save($this->request->data)) {
                $this->Session->setFlash(__('The workout has been saved.'), 'alert', array( 'plugin' => 'BoostCake', 'class' => 'alert-success' ));
				return $this->redirect(array('controller' => 'programs', 'action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Workout.' . $this->Workout->primaryKey => $id));
			$this->request->data = $this->Workout->find('first', $options);
		}
		$programs = $this->Workout->Program->find('list');
		$sports = $this->Workout->Sport->find('list');
		$zones = $this->Workout->Zone->find('list');
		$tassPlacementTypes = $this->Workout->TassPlacementType->find('list');
		$strengthTypes = $this->Workout->StrengthType->find('list');
		$this->set(compact('programs', 'sports', 'zones', 'tempoTypes', 'strengthTypes', 'tassPlacementTypes'));
	}

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index()
	{
		$this->Workout->recursive = 0;
		$this->set('workouts', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Workout->exists($id)) {
			throw new NotFoundException(__('Invalid workout'));
		}
		$options = array('conditions' => array('Workout.' . $this->Workout->primaryKey => $id));
		$this->set('workout', $this->Workout->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Workout->create();
			if ($this->Workout->save($this->request->data)) {
				$this->Session->setFlash(__('The workout has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout could not be saved. Please, try again.'));
			}
		}
		$programs = $this->Workout->Program->find('list');
		$sports = $this->Workout->Sport->find('list');
		$zones = $this->Workout->Zone->find('list');
		$strengthTypes = $this->Workout->StrengthType->find('list');
		$this->set(compact('programs', 'sports', 'zones', 'tempoTypes', 'strengthTypes'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Workout->exists($id)) {
			throw new NotFoundException(__('Invalid workout'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Workout->save($this->request->data)) {
				$this->Session->setFlash(__('The workout has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workout could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Workout.' . $this->Workout->primaryKey => $id));
			$this->request->data = $this->Workout->find('first', $options);
		}
		$programs = $this->Workout->Program->find('list');
		$sports = $this->Workout->Sport->find('list');
		$zones = $this->Workout->Zone->find('list');
		$tassPlacementTypes = $this->Workout->TassPlacementType->find('list');
		$strengthTypes = $this->Workout->StrengthType->find('list');
		$this->set(compact('programs', 'sports', 'zones', 'tassPlacementTypes', 'strengthTypes'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Workout->id = $id;
		if (!$this->Workout->exists()) {
			throw new NotFoundException(__('Invalid workout'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Workout->delete()) {
			$this->Session->setFlash(__('The workout has been deleted.'));
		} else {
			$this->Session->setFlash(__('The workout could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}

	public function ajaxMove()
	{
		$this->autoRender = false;

		if (!$this->request->is('post'))
		{
			return $this->redirect(array('controller' => 'pages', 'action' => 'dashboard'));
		}

		$date = $this->request->data('year').'-'.$this->request->data('month').'-'.$this->request->data('day');

		//convert to boolean
		$addRace = ($this->request->data('addRace') === 'true');

		if($addRace) {
			return $this->_moveAddRace($date);
		}

		$workout = $this->Workout->find('first', array(
			'conditions' => array(
				'Workout.id' => $this->request->data('workoutId'),
				'Program.user_id' => $this->Auth->user('id'),
			),
		));

		if(empty($workout)) {
			$this->Session->setFlash(__('The workout could not have been moved. Please try again.'), 'alert', array( 'plugin' => 'BoostCake', 'class' => 'alert-danger' ));
			return json_encode(array ( 'success' => null, 'error' => 'Wrong, permitted or false entry.', ));
		}

		$workout = $this->Workout->find('first', array(
			'conditions' => array(
				'Workout.id' => $this->request->data('workoutId'),
				'Program.user_id' => $this->Auth->user('id'),
				'Program.training <' => $date,
				'Program.race >' => $date,
			),
		));

		if(empty($workout))
		{
			$this->Session->setFlash(__('You cannot move a workout outside your Program date range.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));

			return json_encode(array
			(
				'success' => null,
				'error' => 'date out of program range.',
			));
		}

		$workout = $this->Workout->find('first', array(
			'conditions' => array(
				'Program.user_id' => $this->Auth->user('id'),
				'Program.visible' => true,
				'Workout.date' => $date,
				'Sport.value' => $this->request->data('sport'),
			),
		));

		if(!empty($workout))
		{
			$this->Session->setFlash(__('There is already a %s workout on this date.', $this->request->data('sport')), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));

			return json_encode(array
			(
				'success' => null,
				'error' => 'another' . $this->request->data('sport') . 'on this date.',
			));
		}

		$this->Workout->clear();
		$this->Workout->id = $this->request->data('workoutId');

		if($this->Workout->saveField('date', $date))
		{
			$this->Session->setFlash(__('The workout has been moved.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-success'
			));

			return json_encode(array
			(
				'success' => array('year' => $this->request->data('year'), 'month' => $this->request->data('month')),
				'error' => 'date out of program range.',
				'error' => null,
			));
		}
		else
		{
			$this->Session->setFlash(__('The workout could not have been moved. Please try again.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));

			$data = array
			(
				'success' => null,
				'error' => 'Error saving.',
			);
		}
	}

	protected function _moveAddRace($date)
	{
		$this->loadModel('WorkoutChild');

		$workout = $this->WorkoutChild->find('first', array(
			'conditions' => array(
				'WorkoutChild.id' => $this->request->data('workoutId'),
			),
			'contain' => array(
				'ProgramChild' => array(
					'Program' => array(
						'conditions' => array(
							'user_id' => $this->Auth->user('id'),
							'training <' => $date,
							'race >' => $date,
						),
					),
				),
			),
		));

		if(empty($workout)) {
			$this->Session->setFlash(__('The workout could not have been moved. Please try again.'), 'alert', array( 'plugin' => 'BoostCake', 'class' => 'alert-danger' ));
			return json_encode(array ( 'success' => null, 'error' => 'Wrong, permitted or false entry.', ));
		}

		$this->WorkoutChild->clear();
		$this->WorkoutChild->id = $this->request->data('workoutId');

		if($this->WorkoutChild->saveField('date', $date)) {
			$this->Session->setFlash(__('The workout has been moved.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-success'
			));

			return json_encode(array ( 'success' => array('year' => $this->request->data('year'), 'month' => $this->request->data('month')), 'error' => null, ));
		} else {
			$this->Session->setFlash(__('The workout could not have been moved. Please try again.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));

			return json_encode(array ( 'success' => null, 'error' => 'Wrong, permitted or false entry.', ));
		}
	}

	public function ajaxComplete()
	{
		$this->autoRender = false;

		if (!$this->request->is('post'))
		{
			return $this->redirect(array('controller' => 'pages', 'action' => 'dashboard'));
		}

		$complete = intval($this->request->data('complete'));
		$id = intval($this->request->data('id'));

		$workouts = $this->Workout->updateComplete($id, $this->Auth->user('id'), $complete);
		$shape = $this->Workout->getShape($id, $this->Auth->user('id'));

		return json_encode(array
		(
			'success' => array(
				'triggerId' => $id,
				'complete' => $complete,
				'shape' => $shape,
				'workouts' => $workouts,
			),
			'error' => null,
		));
	}

	public function ajaxShape()
	{
		$this->autoRender = false;

		if (!$this->request->is('post'))
		{
			return $this->redirect(array('controller' => 'pages', 'action' => 'dashboard'));
		}

		$shape = intval($this->request->data('shape'));
		$id = intval($this->request->data('id'));

		$workouts = $this->Workout->updateShape($id, $this->Auth->user('id'), $shape);
		$complete = $this->Workout->getComplete($id, $this->Auth->user('id'));


		return json_encode(array
		(
			'success' => array(
				'triggerId' => $id,
				'complete' => $complete,
				'shape' => $shape,
				'workouts' => $workouts,
			),
			'error' => null,
		));
	}

	public function ajaxAll()
	{
		$this->autoRender = false;

		if (!$this->request->is('post'))
		{
			return $this->redirect(array('controller' => 'pages', 'action' => 'dashboard'));
		}

		$id = intval($this->request->data('id'));
		$shape = intval($this->request->data('shape'));
		$complete = intval($this->request->data('complete'));

		$this->Workout->updateComplete($id, $this->Auth->user('id'), $complete);
		$workouts = $this->Workout->updateShape($id, $this->Auth->user('id'), $shape);

		$this->Workout->updateComplete($id, $this->Auth->user('id'), $complete);
		$workouts = $this->Workout->updateShape($id, $this->Auth->user('id'), $shape);

		return json_encode(array
		(
			'success' => array(
				'triggerId' => $id,
				'complete' => $complete,
				'shape' => $shape,
				'workouts' => $workouts,
			),
			'error' => null,
		));
	}
}
