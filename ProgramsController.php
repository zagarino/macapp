<?php
App::uses('AppController', 'Controller');
/**
 * Programs Controller
 *
 * @property Program $Program
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class ProgramsController extends AppController
{

/**
 * Components
 *
 * @var array
 */
	public $components = array ('Training');

	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Security->unlockedActions = array('ajaxUpdateTraining', 'ajaxMove');
	}

	public $stepNames = array
	(
		'Program Selection',
		'Racing Goal',
		'Fitness Questions',
		'Program Preview',
		'Summary'
	);

	public function beforeRender()
	{
	}

/**
 * ndex method
 *
 * @return void
 */

	public function index()
	{
		$this->Program->recursive = -1;

		$this->Paginator->settings = array(
			'conditions' => array(
				'user_id' => $this->Auth->user('id'),
				'visible' => 1,
			),
			'contain' => array(
				'WorkoutLevel',
				'Race',
				'ProgramChild' => array(
					'Race',
				),
			),
		);

		$programs = $this->Paginator->paginate('Program');

		$this->set('programs', $programs);
	}

	public function start()
	{
		$this->Session->delete('program');

		$this->Session->write('program.params.maxProgress', 1);
		$this->Session->write('program.params.stepCurrent', 1);

		return $this->redirect(array('action' => 'step1'));
	}

	public function step1()
	{
		if (is_int($this->Session->read('program.params.maxProgress')))
		{
			$this->set('maxProgress', $this->Session->read('program.params.maxProgress'));
		}
		else
		{
			$this->redirect(array('action' => 'start'));
		}

		$this->Session->write('program.params.stepCurrent', 1);
		$this->set('stepCurrent', $this->Session->read('program.params.stepCurrent'));

		$this->set('stepNames', $this->stepNames);

		if ($this->request->is('post'))
		{
			$this->request->data['Program']['user_id'] = $this->Auth->user('id');
			$this->Program->set($this->request->data);
			if ($this->Program->validates())
			{
				$this->Session->write('program.params.maxProgress', 2);

				$prevSessionData = $this->Session->read('program.data');
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$this->Session->write('program.data', $currentSessionData);

				$this->redirect(array('action' => 'step2'));
			}
		}
		else
		{
			$this->request->data = $this->Session->read('program.data');
		}
	}

	public function step2()
	{
		$this->_step(2);

		$races = $this->Program->Race->find('list', array
		(
			'order' => array('sort' => 'asc')
		));
		foreach ($races as $key => $race ) {
			$races[ $key ] = __( $race );
		}
		$levels = array();
		$levels = $this->Program->WorkoutLevel->find('all');

		$levelCounter = 1;
		foreach ($levels as  $level)
		{
			$workoutLevels[$levelCounter] = __($level['WorkoutLevel']['name']) . ' ('.$level['WorkoutLevel']['value']. ' ' . __('Workouts per Week') . ')';
			$levelCounter++;
		}
		$this->set(compact('races', 'workoutLevels'));

		if (!$this->request->is('post'))
		{
			if (!$this->Session->check('program.data.Program.race'))
			{
                if($this->Session->read('program.data.Program.type') === 'maintenance') {
	                $this->request->data['Program']['race'] = $this->Training->firstTimeRace($this->Training->max, true);
                } else {
	                $this->request->data['Program']['race'] = $this->Training->firstTimeRace($this->Training->max);
                }

				$this->request->data['Program']['training_visual'] = $this->Training->firstTimeTraining();
			}
		}
		else
		{
			$training = $this->getTrainingDate($this->request->data['Program']['race']['year'], $this->request->data['Program']['race']['month'], $this->request->data['Program']['race']['day'], $this->request->data['Program']['training_length']);

			$this->request->data['Program']['training_visual']['year'] = $training->format('Y');
			$this->request->data['Program']['training_visual']['month'] = $training->format('m');
			$this->request->data['Program']['training_visual']['day'] = $training->format('d');
		}
	}

	public function step3()
	{
		$this->_step(3);
		$this->Session->delete('program.data.WorkoutPlacement');

		/*
		if (!$this->request->is('post')) {

			$now = new DateTime('now');
			$training = new DateTime($this->request->data['Program']['training']['year'] . '-' . $this->request->data['Program']['training']['month'] . '-' . $this->request->data['Program']['training']['day']);
			#$race = new DateTime($this->request->data['Program']['race']['year'] . '-' . $this->request->data['Program']['race']['month'] . '-' . $this->request->data['Program']['race']['day']);

			if ($now < $training || $now < $race) {
				$this->Session->SetFlash(__('%sWarning:%s You are creating a program in the past!','<strong>', '</strong>'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-warning'
				));
			}
		}
		 */

		//$this->log($this->Session->read('program.data.Program.race.year'));
		$trainingDate = $this->getTrainingDate($this->Session->read('program.data.Program.race.year'),$this->Session->read('program.data.Program.race.month'),$this->Session->read('program.data.Program.race.day'), $this->Session->read('program.data.Program.training_length'));
		$this->Session->write('program.data.Program.training', array('year' => $trainingDate->format('Y'), 'month' => $trainingDate->format('n'), 'day' => $trainingDate->format('j')));

	}

	public function ajaxMove()
	{
		$this->autoRender = false;
		if ($this->request->is('ajax'))
		{
			$workoutPlacements = $this->Session->read('program.data.WorkoutPlacement');
			$check = $this->Training->checkMove($workoutPlacements, $this->request->data('weekday'), $this->request->data('key'));

			if ($check  === true)
			{
				$this->Session->write('program.data.WorkoutPlacement.'.$this->request->data('key').'.WorkoutPlacement.week_day', $this->request->data('weekday'));
				$data = array
				(

					'content' => $this->request->data('key'),
					'error' => null
				);

				return json_encode($data);
			}
			else
			{

				$data = array
				(
					'error' => $check
				);

				return json_encode($data);
			}
		}
	}

	private function _movePlacement($weekDay, $key)
	{
		$workoutPlacements = $this->Session->read('program.data.WorkoutPlacement');

		$check = $this->Training->checkMove($workoutPlacements, $weekDay, $key);

		if ($check  === true)
		{
			$this->Session->setFlash(__('Your workout has been moved on another weekday.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-success'
			));
			$this->Session->write('program.data.WorkoutPlacement.'.$key.'.WorkoutPlacement.week_day', $weekDay);
		}
		else
		{
				$this->Session->setFlash(__($check), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
	}

	public function step4($id = null)
	{
		if($this->request->is('post') && isset($this->request->data['Weekday']['week_day']))
		{
			//$this->log($this->request->data);
			$this->_movePlacement($this->request->data['Weekday']['week_day'], $id);
			$this->redirect(array('action' => 'step4'));
		}
		else
		{
			$this->_step(4);
		}

		$this->loadModel('WorkoutPlacement');

		$placements = $this->WorkoutPlacement->find('all', array(
			'conditions' => array(
				'workout_level_id' => $this->request->data['Program']['workout_level_id']
			),
		));

		if (!$this->Session->read('program.data.WorkoutPlacement'))
		{
			$placements = $this->WorkoutPlacement->find('all', array(
				'conditions' => array(
					'workout_level_id' => $this->request->data['Program']['workout_level_id']
				),
			));

			$this->Session->write('program.data.WorkoutPlacement', $placements);
		}

		if (isset($id))
		{
			$this->set('placementKey', $id);
		}
		//$this->log($placements);

				//$this->Session->write('program.params.maxProgress', $step+1);

				//$prevSessionData = $this->Session->read('program.data');
				//$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				//$this->Sssion->write('program.data', $currentSessionData);


		$this->set('workoutPlacements', $this->Session->read('program.data.WorkoutPlacement'));
		$this->set('weekDays', $this->Training->weekDays);

		$workouts = $this->workouts();
		$this->log($workouts);
	}

	public function workouts($programId = null)
	{
		$this->loadModel('Safeguard');
		$this->loadModel('Zone');
		$this->loadModel('Strength');
		$this->loadModel('Sport');
		$this->loadModel('Modifier');

		$placements = $this->Session->read('program.data.WorkoutPlacement');

		$output = $this->adjustInput();

		$this->loadModel('Progression');

		$workoutFields = array(
			'workoutsNow' => $this->Session->read('program.data.Program.workouts_now'),
			'workoutsLastYear' => $this->Session->read('program.data.Program.workouts_last_year'),
			'workoutsPreviousYear' => $this->Session->read('program.data.Program.workouts_previous_year'),
			'workoutsYearBefore' => $this->Session->read('program.data.Program.workouts_year_before'),
		);

		$dob = new DateTime($this->Auth->user('date_of_birth'));
		$now = new DateTime();
		$age = $now->diff($dob);


		$sports = $this->Sport->find('all', array(
			'conditions' => array(
				'not' => array(
					'Sport.value' => 'strength',
				),
			),
			'contain' => array(),
		));

		foreach ($sports as $sport)
		{
			$progression[$sport['Sport']['id']] = $this->Progression->find('all', array(
				'conditions' => array(
					'training_length' => $this->Session->read('program.data.Program.training_length'),
					'sport_id' => $sport['Sport']['id'],
				),
				'contain' => array(
					'ProgressionType',
					'Sport',
				),
			));

			//$this->log($output[$sport['Sport']['value']]);

			$ed[$sport['Sport']['id']] = $this->getEd($progression[$sport['Sport']['id']], $output[$sport['Sport']['value']], $sport);


			$ad[$sport['Sport']['id']] = $this->getAd($ed[$sport['Sport']['id']], $sport);
			$as[$sport['Sport']['id']] = $this->getAs($ed[$sport['Sport']['id']], $sport);
			$recovery[$sport['Sport']['id']] = $this->getRecovery($ed[$sport['Sport']['id']], $sport);
			$tass[$sport['Sport']['id']] = $this->getTass($workoutFields, $age->y, $sport['Sport']['id']);
		}
		$this->loadModel('TassPlacementType');
		$mts = $this->TassPlacementType->find('all', array(
			'conditions' => array(
				'TassPlacementType.name LIKE' => 'MT%',
			),
			'order' => array(
				'TassPlacementType.name' => 'asc',
			),
			'contain' => array(
				'Zone',
			)
		));

		$brick = $this->getBrick();
		$dbr = $this->getDbr();

		$strength = $this->getStrength();
		$heartRates = $this->getHeartRate($age->y, $workoutFields);

		//$this->log($age->y, 'Your age');
		//$this->log($heartRates, 'Heart rates (1 for recovery, 2 for ad, 3 for ed, 4 for as, 5 for tempo and 6 for speed)');
		//$this->log($ed, 'ed (1 for swim, 2 for bike and 3 for run)');
		//$this->log($ad, 'ad(1 for swim, 2 for bike and 3 for run)');
		//$this->log($as, 'as (1 for swim, 2 for bike and 3 for run)');
		//$this->log($recovery, 'recovery (1 for swim, 2 for bike and 3 for run)');
		//$this->log($tass, 'Tempo, As or Speed (1 for swim, 2 for bike and 3 for run)');
		//$this->log($strength, 'Strength, As or Speed (1 for swim, 2 for bike and 3 for run)');

		if($this->Session->read('program.data.Program.type') !== 'maintenance') {
			$maintenance = false;
		} else {
			$maintenance = true;
		}


		return $this->Training->generateWorkouts($this->Session->read('program.data.Program.race'), $this->Session->read('program.data.Program.training_length'), $placements, $brick, $ed, $ad, $as, $recovery, $tass, $dbr, $mts, $strength, $heartRates, $programId, $maintenance);

	}

	public function getDbr()
	{
		$this->loadModel('DaysBeforeRace');
		$daysBeforeRaces = $this->DaysBeforeRace->find('all', array(
			'conditions' => array(
				'race_id' => $this->Session->read('program.data.Program.race_id'),
			),
			'order' => array(
				'DaysBeforeRace.day' => 'desc',
			),
			'recursive' => -1
		));

		return $daysBeforeRaces;
	}

	public function getBrick()
	{
		$this->loadModel('Brick');
		$bricks = $this->Brick->find('all', array(
			'conditions' => array(
				'training_length' => $this->Session->read('program.data.Program.training_length'),
				'race_id' => $this->Session->read('program.data.Program.race_id'),
			),
		));

		$result = array();

		foreach ($bricks as $brick)
		{
			$result[$brick['Brick']['week']] = $brick['Brick']['value'];
		}

		return $result;
	}

	public function getHeartRate($age, $workouts)
	{
		$this->loadModel('Zone');
		$zones = $this->Zone->find('all', array(
			'recursive' => -1,
		));

		$races = $this->Program->Race->find('all', array(
			'order' => array(
				'Race.sort' => 'asc',
			),
			'recursive' => -1,
		));

		return $this->Training->heartRate($age, $workouts, $zones, $races);
	}

	public function ajaxUpdateTraining()
	{
		$this->autoRender = false;

		if($this->request->is('ajax'))
		{
			$date = $this->getTrainingDate($this->request->data('year'), $this->request->data('month'), $this->request->data('day'),$this->request->data('trainingLength'));

			$data = array(
				'content' => array(
					'day' => $date->format('j'),
					'month' => $date->format('n'),
					'year' => $date->format('Y')
				)
			);
			return json_encode($data);
		}
	}

	protected function getTrainingDate($year, $month, $day, $trainingLength)
	{
			$date = new DateTime($year . '-' . $month . '-' . $day);
			$date->modify('-' . $trainingLength . ' weeks');

			$weekday = (int)$date->format('w');

			if($weekday != 1)
			{
				$date->modify('next monday');
			}

			return $date;
	}

	public function step5()
	{
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		$this->_step(5, false);

		$this->set('name', $this->Session->read('program.data.Program.name'));

		$this->loadModel('Race');

		$race = $this->Race->find('first', array(
			'conditions' => array(
				'Race.id' => $this->Session->read('program.data.Program.race_id'),
			),
		));

		$dob = new DateTime($this->Auth->user('date_of_birth'));
		$now = new DateTime();
		$age = $now->diff($dob);
		$workoutFields = array(
			'workoutsNow' => $this->Session->read('program.data.Program.workouts_now'),
			'workoutsLastYear' => $this->Session->read('program.data.Program.workouts_last_year'),
			'workoutsPreviousYear' => $this->Session->read('program.data.Program.workouts_previous_year'),
			'workoutsYearBefore' => $this->Session->read('program.data.Program.workouts_year_before'),
		);

		$heartRates = $this->getHeartRate($age->y, $workoutFields);

		$this->Session->write('program.data.Program.heart_min',$heartRates['Race'][$race['Race']['id']]['min']);
		$this->Session->write('program.data.Program.heart_max',$heartRates['Race'][$race['Race']['id']]['max']);

		$this->set('race', $race['Race']['name']);

		$this->loadModel('WorkoutLevel');

		$workoutLevel = $this->WorkoutLevel->find('first', array(
			'conditions' => array(
				'WorkoutLevel.id' => $this->Session->read('program.data.Program.workout_level_id'),
			),
		));

		$this->set('training_length', $this->Session->read('program.data.Program.training_length'));
		$this->set('type', $this->Session->read('program.data.Program.type'));

		$this->set('workout_level', $workoutLevel['WorkoutLevel']['name']);
        $this->set('canTryout', $this->Program->User->canTryout());

		if ($this->request->is('post'))
		{
			$this->Session->write('program.data.Program.visible', 1);

			if ($this->Program->save($this->Session->read('program.data')))
			{

				$workouts = $this->workouts($this->Program->id);
				$this->loadModel('Workout');

				if($this->Workout->saveMany($workouts))
				{
					$year = $this->Session->read('program.data.Program.training.year');
					$month = $this->Session->read('program.data.Program.training.month');

					$this->Session->delete('program');

                    if ($this->Program->User->canTryout()) {
                        $this->Program->User->id = $this->Auth->user('id');
                        $this->Program->User->saveField('tryout', date('Y-m-d'));
                    }

					$this->Session->setFlash(__('Your program has been generated.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-success'
					));

					return $this->redirect(array('controller' => 'pages', 'action' => 'calendar',$year,$month));
				}
				else
				{
					$this->Session->delete('program');
					$this->Session->setFlash(__('Oops! An error generating some workouts occured.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger'
					));

					return $this->redirect(array('action' => 'index'));
					//debug($this->Workout->validationErrors); die();
				}
			}
			else
			{
				$this->Session->delete('program');
				$this->Session->setFlash(__('Oops! An program error occured.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger'
					));
				return $this->redirect(array('action' => 'index'));
			}
		}
	}

	protected function _step($step, $redirect = true)
	{
		if (is_int($this->Session->read('program.params.stepCurrent')))
		{
			if ($this->Session->read('program.params.maxProgress') < $step)
			{
				$this->redirect(array('action' => 'step'.$this->Session->read('program.params.stepCurrent')));
			}
			else
			{
				$this->Session->write('program.params.stepCurrent', $step);
			}
		}
		else
		{
			$this->redirect(array('action' => 'start'));
		}

		if ($this->request->is('post'))
		{
			$this->Program->set($this->request->data);
			if ($this->Program->validates())
			{
				$this->Session->write('program.params.maxProgress', $step+1);

				$prevSessionData = $this->Session->read('program.data');
				$currentSessionData = Hash::merge( (array) $prevSessionData, $this->request->data);
				$this->Session->write('program.data', $currentSessionData);

				if($redirect)
				{
					$this->redirect(array('action' => 'step'.($step+1)));
				}
			}
		}
	else
		{
			$this->request->data = $this->Session->read('program.data');
		}

		$this->set('stepCurrent', $this->Session->read('program.params.stepCurrent'));
		$this->set('maxProgress', $this->Session->read('program.params.maxProgress'));
		$this->set('stepNames', $this->stepNames);
	}

	public function getTass($workouts, $age, $sportId)
	{
		$lastYear = $this->Session->read('program.data.Program.workouts_last_year');
		$previousYear = $this->Session->read('program.data.Program.workouts_previous_year');
		$yearBefore = $this->Session->read('program.data.Program.workouts_year_before');
		$now = $this->Session->read('program.data.Program.workouts_now');

		if ( $lastYear >= 6 || $previousYear >= 6 || $yearBefore >= 6 || $now >= 6 )
		{
			$hasExperience = true;
		}
		else
		{
			$hasExperience = false;
		}

		$this->loadModel('Age');
		$ageGroup = $this->Age->find('first', array(
			'conditions' => array(
				'Age.value <=' => $age,
			),
			'order' => array(
				'Age.value' => 'desc',
			),
		));

		$this->loadModel('TassPlacement');
		$tassPlacements = $this->TassPlacement->find('all', array(
			'conditions' => array(
				'TassPlacement.age_id' => $ageGroup['Age']['id'],
				'TassPlacement.sport_id' => $sportId,
				'TassPlacement.has_experience' => $hasExperience,
				'TassPlacement.training_length' => $this->Session->read('program.data.Program.training_length'),
			),
			'contain' => array(
				'TassPlacementType' => array(
					'Zone',
				),
			),
			'order' => array(
				'TassPlacement.training_week' => 'asc',
			),
		));


		//$this->log($tassPlacements);
		return $tassPlacements;
	}

	public function getStrength()
	{
		$strength = $this->Strength->find('list', array(
			'conditions' => array(
				'training_length' => $this->Session->read('program.data.Program.training_length'),
				'type' => $this->Session->read('program.data.Program.type'),
			),
			'fields' => array(
				'week',
				'zone_id',
			),
		));

		return $strength;
	}

	public function getAs($ed, $sport)
	{
		$this->loadModel('Modifier');
		$as = array();

		$modifier = $this->Modifier->find('first', array(
			'conditions' => array(
				'Zone.value' => 'aerobic_stimulation',
				'ModifierType.value' => 'zone',
				'sport_id' => $sport['Sport']['id'],
			),
			'fields' => array(
				'down',
			),
		));

		for ($week = 1; $week <= $this->Session->read('program.data.Program.training_length'); $week++)
		{
			if ($week == $this->Session->read('program.data.Program.training_length') && $this->Session->read('program.data.Program.type') != 'maintenance') {
				$as[$week] = null;
			}
			else
			{
				$as[$week] = round(($ed[$week]*$modifier['Modifier']['down'])/$sport['Sport']['round_at'])*$sport['Sport']['round_at'];
			}
		}
		return $as;
	}

	public function getRecovery($ed, $sport)
	{
		$this->loadModel('Modifier');
		$recovery = array();

		$safeguard = $this->Safeguard->find('first', array(
			'conditions' => array(
				'SafeguardType.value' => 'recovery',
				'race_id' => $this->Session->read('program.data.Program.race_id'),
				'sport_id' => $sport['Sport']['id'],
			),
			'fields' => array(
				'value',
			)
		));

		$modifier = $this->Modifier->find('first', array(
			'conditions' => array(
				'ModifierType.value' => 'zone',
				'Zone.value' => 'recovery',
				'sport_id' => $sport['Sport']['id'],
			),
			'fields' => array(
				'down',
			)
		));

		for ($week = 1; $week <= $this->Session->read('program.data.Program.training_length'); $week++)
		{
			if ($week == $this->Session->read('program.data.Program.training_length') && $this->Session->read('program.data.Program.type') != 'maintenance') {
				$recovery[$week] = null;
			}
			else
			{
				$round = round( ($ed[$week] * $modifier['Modifier']['down']) / $sport['Sport']['round_at'] ) * $sport['Sport']['round_at'];

				if($round > $safeguard['Safeguard']['value'])
				{
					$recovery[$week] = $safeguard['Safeguard']['value'];
				}
				else
				{
					$recovery[$week] = $round;
				}
			}
		}

		return $recovery;
	}

	public function getAd($ed, $sport)
	{
		$this->loadModel('Modifier');
		$ad = array();

		$modifier = $this->Modifier->find('first', array(
			'conditions' => array(
				'ModifierType.value' => 'zone',
				'Zone.value' => 'aerobic_development',
				'sport_id' => $sport['Sport']['id'],
			),
			'fields' => array(
				'down',
			)
		));

		for ($week = 1; $week <= $this->Session->read('program.data.Program.training_length'); $week++)
		{
			if ($week == $this->Session->read('program.data.Program.training_length') && $this->Session->read('program.data.Program.type') != 'maintenance') {
				$ad[$week] = null;
			} else {
				$ad[$week] = round(($ed[$week]*$modifier['Modifier']['down'])/$sport['Sport']['round_at'])*$sport['Sport']['round_at'];
			}
		}

		return $ad;
	}

	public function getEd($progressions, $output, $sport)
	{
		$output = ceil($output / $sport['Sport']['round_at']) * $sport['Sport']['round_at'];
		if($this->Session->read('program.data.Program.type') !== 'maintenance') {
			$safeguard = $this->Safeguard->find('first',array(
				'conditions' => array(
					'SafeguardType.value' => 'maximum',
					'race_id' => $this->Session->read('program.data.Program.race_id'),
					'sport_id' => $sport['Sport']['id'],
				),
			));

			$maintenance = 1.00;
		} else {
			$safeguard = $this->Safeguard->find('first',array(
				'conditions' => array(
					'SafeguardType.value' => 'maximum_maintenance',
					'race_id' => $this->Session->read('program.data.Program.race_id'),
					'sport_id' => $sport['Sport']['id'],
				),
			));

			$maintenance = 0.65;
		}


		$tapers = $this->Modifier->find('all', array(
			'conditions' => array(
				'ModifierType.value' => 'taper',
				'sport_id' => $sport['Sport']['id'],
			),
		));

		$modifier = $this->Modifier->find('first', array(
			'conditions' => array(
				'ModifierType.value' => 'zone',
				'Zone.value' => 'endurance_development',
				'sport_id' => $sport['Sport']['id'],
			),
		));

		return $this->Training->ed($this->Session->read('program.data.Program.training_length'), $output, $sport, $progressions, $tapers, $modifier, $safeguard, $maintenance);
	}

	public function adjustInput()
	{
		$input = array(
			'run' => ($this->Session->read('program.data.Program.run_hour') * 60) + $this->Session->read('program.data.Program.run_minute'),
			'bike' => ($this->Session->read('program.data.Program.bike_hour') * 60) + $this->Session->read('program.data.Program.bike_minute'),
			'swim' => $this->Session->read('program.data.Program.swim_meter'),
		);

		$this->Session->write('program.data.Program.longest_run', $input['run']);
		$this->Session->write('program.data.Program.longest_bike', $input['bike']);
		$this->Session->write('program.data.Program.longest_swim', $input['swim']);

		if($this->Session->read('program.data.Program.type') !== 'maintenance') {
			$maximumInput = $this->Safeguard->find('all',array(
				'conditions' => array(
					'SafeguardType.value' => 'maximum',
					'race_id' => $this->Session->read('program.data.Program.race_id'),
				),
				'contain' => array(
					'Sport',
					'SafeguardType'
				),
				'order' => array(
					'Sport.value' => 'desc'
				)
			));
		} else {
			$maximumInput = $this->Safeguard->find('all',array(
				'conditions' => array(
					'SafeguardType.value' => 'maximum_maintenance',
					'race_id' => $this->Session->read('program.data.Program.race_id'),
				),
				'contain' => array(
					'Sport',
					'SafeguardType'
				),
				'order' => array(
					'Sport.value' => 'desc'
				)
			));
		}

		$minimumInput = $this->Safeguard->find('all',array(
			'conditions' => array(
				'SafeguardType.value' => 'minimum_input',
			),
			'contain' => array(
				'Sport',
				'SafeguardType'
			),
			'order' => array(
				'Sport.value' => 'desc'
			)
		));

		return $this->Training->adjustInput($input, $minimumInput, $maximumInput);
	}
/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Program->exists($id)) {
			throw new NotFoundException(__('Invalid program'));
		}

		$program = $this->Program->find('first', array(
			'conditions' => array(
				'Program.'.$this->Program->primaryKey => $id,
			),
			'contain' => array(
				'Race.name',
				'WorkoutLevel.name',
				'Workout' => array(
					'Zone.name',
					'Sport.name',
					'TassPlacementType.name',
				)
			)
		));
        $endTryout = $this->Program->User->isTryingout();
        $isSubscribed = $this->Program->User->isSubscribed();
        $hasFreeCharge = $this->Program->User->hasFreeCharge();



		$this->set('endTryout', $endTryout);
		$this->set('isSubscribed', $isSubscribed);
		$this->set('hasFreeCharge', $hasFreeCharge);
		$this->set('program', $program);
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Program->exists($id)) {
			throw new NotFoundException(__('Invalid program'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Program->save($this->request->data)) {
				$this->Session->setFlash(__('The program has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Program.' . $this->Program->primaryKey => $id));
			$this->request->data = $this->Program->find('first', $options);
		}
		$races = $this->Program->Race->find('list');
		$workoutLevels = $this->Program->WorkoutLevel->find('list');
		$this->set(compact('races', 'workoutLevels'));
	}

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index()
	{
        $this->Paginator->settings = array('Program' => array(
            'order' => array('desc' => 'asc'),
        ));

		if($this->request->is('post'))
		{
			if(isset($this->request->data['Filter']['keyword']))
			{
				if($this->request->data['Filter']['keyword'] === "")
				{
					return $this->admin_search_reset();
				}

				$this->Session->write('Search.keyword', $this->request->data['Filter']['keyword']);
				return $this->redirect(array('action' => 'index'));
			}
		}
		$this->Program->recursive = 0;

		if($this->Session->check('Search.keyword'))
		{
			$this->set('keyword', $this->Session->read('Search.keyword'));

			$this->set('programs', $this->Paginator->paginate('Program', array(
					'or' => array(
						'Program.id' => $this->Session->read('Search.keyword'),
						'Program.name LIKE' => "%" . $this->Session->read('Search.keyword') . "%",
						'User.user_name LIKE' => "%" . $this->Session->read('Search.keyword') . "%",
						'User.first_name LIKE' => "%" . $this->Session->read('Search.keyword') . "%",
						'User.last_name LIKE' => "%" . $this->Session->read('Search.keyword') . "%",
						'User.email LIKE' => "%" . $this->Session->read('Search.keyword') . "%",
					),
			)));
		}
		else
		{
			$this->set('programs', $this->Paginator->paginate());
		}
	}

	public function admin_search_reset()
	{
		$this->Session->delete('Search');
		return $this->redirect(array('action' => 'index'));
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Program->exists($id)) {
			throw new NotFoundException(__('Invalid program'));
		}
		$program = $this->Program->find('first', array(
			'conditions' => array(
				'Program.'.$this->Program->primaryKey => $id,
			),
			'contain' => array(
				'User',
				'Race',
				'WorkoutLevel',
				'Workout' => array(
					'Zone',
					'Sport',
					'TassPlacementType',
				),
			),
		));

		$this->set('program', $program);
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Program->exists($id)) {
			throw new NotFoundException(__('Invalid program'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Program->save($this->request->data)) {
				$this->Session->setFlash(__('The program has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Program.' . $this->Program->primaryKey => $id));
			$this->request->data = $this->Program->find('first', $options);
		}
		$races = $this->Program->Race->find('list');
		$levels = $this->Program->WorkoutLevel->find('list');
		$this->set(compact('races', 'levels'));
	}

/**
 * admin_visibility method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_visibility($id = null) {
		if (!$this->Program->exists($id)) {
			throw new NotFoundException(__('Invalid program'));
		}
		if ($this->request->is(array('post', 'put'))) {

			if($this->Program->visible($id))
			{
				$visible = 0;
			}
			else
			{
				$visible = 1;
			}

			$this->Program->id = $id;
			if ($this->Program->saveField('visible', $visible))
			{
				if ($visible)
				{
					$this->Session->setFlash(__('The program has been restored.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-success'
					));
				}
				else
				{
					$this->Session->setFlash(__('The program has been archived.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-info'
					));
				}
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Program.' . $this->Program->primaryKey => $id));
			$this->request->data = $this->Program->find('first', $options);
		}
		$races = $this->Program->Race->find('list');
		$levels = $this->Program->WorkoutLevel->find('list');
		$this->set(compact('races', 'levels'));
	}

/**
 * admin_hide method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */

	public function admin_hide($id = null) {
		$this->Program->id = $id;
		if (!$this->Program->exists()) {
			throw new NotFoundException(__('Invalid program'));
		}

		$this->request->allowMethod('post');
		if ($this->Program->saveField('visible', 0))
		{
			$this->loadModel('Workout');

			if ($this->Workout->deleteAll(array( 'Workout.program_id' => $id), false))
			{
				$this->Session->setFlash(__('The program has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
			}
			else
			{
				$this->Session->setFlash(__('Whoops! An error occured.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		else
		{
			$this->Session->setFlash(__('The program could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}

/**
 * admin_reset method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */

	public function admin_reset()
	{
		$this->request->allowMethod('post', 'delete');

		if (!Configure::read('debug'))
		{
			$this->Session->setFlash(__('Not allowed on production.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));

			return $this->redirect(array('controller' => 'pages', 'action' => 'panel'));
		}

		if ($this->Program->deleteAll(array('1=1'), false))
		{
			$this->loadModel('Workout');

			if ($this->Workout->deleteAll(array('1=1'), false))
			{
				$this->Session->setFlash(__('The programs has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
			}
			else
			{
				$this->Session->setFlash(__('Whoops! An error occured.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		else
		{
			$this->Session->setFlash(__('The programs could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('controller' => 'pages', 'action' => 'panel'));
	}

	public function isAuthorized($user)
	{
        if(!$this->checkUserAccess()) {
            return $this->redirect(array('controller' => 'Subscriptions', 'action' => 'index'));
        }
		return parent::isAuthorized($user);
	}
}
