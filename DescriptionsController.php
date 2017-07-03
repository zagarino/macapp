<?php
App::uses('AppController', 'Controller');
/**
 * Descriptions Controller
 *
 * @property Description $Description
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class DescriptionsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator', 'Session');

	public $paginate = array(
		'limit' => 100,
	);

	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Security->unlockedActions = array('ajaxFind');
	}

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->Description->recursive = 0;

		for ($trainingweek = 1; $trainingweek <= 20; $trainingweek++)
		{
			$trainingweeks[$trainingweek] = __('Trainingweek') . ' ' . $trainingweek;
		}

		$tapers = array(
			__('No'),
			__('Yes'),
		);

		for ($value = 1; $value <= 12; $value++)
		{
			$values[$value * 500] = $value * 500;
		}

		$races = $this->Description->Race->find('list', array(
			'fields' => array(
				'Race.value',
			),
			'order' => array(
				'sort' => 'asc',
			),
		));

		$zones = $this->Description->Zone->find('list');
		$sports = $this->Description->Sport->find('list');
		$tassPlacementTypes = $this->Description->TassPlacementType->find('list');

		$this->set(compact('trainingweeks', 'values', 'races', 'zones', 'sports', 'tassPlacementTypes', 'tapers'));

		if ($this->request->is('post'))
		{
			$this->Session->write('Description', $this->request->data);
		}
		else
		{
			$this->request->data = $this->Session->read('Description');
		}

		$conditions = array();

		if (!empty($this->request->data['Filter']))
		{
			foreach ($this->request->data['Filter'] as $key => $value)
			{
				if($key == 'trainingweek' && !empty($value))
				{
					$conditions['Description.trainingweek'] = $value;
				}
				if($key == 'taper' && !empty($value))
				{
					$conditions['Description.taper'] = $value;
				}
				elseif($key == 'value' && !empty($value))
				{
					$conditions['Description.value'] = $value;
				}
				elseif($key == 'races' && !empty($value))
				{
					$conditions['Description.race_id'] = $value;
				}
				elseif($key == 'maintenance' && !empty($value))
				{
					$conditions['Description.maintenance'] = $value;
				}
				elseif($key == 'zones' && !empty($value))
				{
					$conditions['Description.zone_id'] = $value;
				}
				elseif($key == 'sports' && !empty($value))
				{
					$conditions['Description.sport_id'] = $value;
				}
				elseif($key == 'tass_placement_type_id' && !empty($value))
				{
					$conditions['Description.tass_placement_type_id'] = $value;
				}
			}
		}

		$this->log($conditions);

		$this->Paginator->settings = array(
			'conditions' => $conditions,
			'limit' => 50,
			'order' => array(
				'Description.created' => 'desc',
				'Description.trainingweek' => 'asc',
				'Race.sort' => 'asc',
				'Description.value' => 'asc',
			),
		);


		$this->set('descriptions', $this->Paginator->paginate());
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->Description->exists($id)) {
			throw new NotFoundException(__('Invalid description'));
		}
		$options = array('conditions' => array('Description.' . $this->Description->primaryKey => $id));
		$this->set('description', $this->Description->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->Description->create();
			if ($this->Description->save($this->request->data)) {
				$this->Session->setFlash(__('The description has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The description could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		$races = $this->Description->Race->find('list', array(
			'order' => array(
				'sort' => 'asc',
			),
		));
		$zones = $this->Description->Zone->find('list');
		$sports = $this->Description->Sport->find('list');
		$tassPlacementTypes = $this->Description->TassPlacementType->find('list');
		$this->set(compact('races', 'zones', 'sports', 'tassPlacementTypes'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->Description->exists($id)) {
			throw new NotFoundException(__('Invalid description'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Description->save($this->request->data)) {
				$this->Session->setFlash(__('The description has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The description could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('Description.' . $this->Description->primaryKey => $id));
			$this->request->data = $this->Description->find('first', $options);
		}
		$races = $this->Description->Race->find('list');
		$zones = $this->Description->Zone->find('list');
		$sports = $this->Description->Sport->find('list');
		$tassPlacementTypes = $this->Description->TassPlacementType->find('list');
		$this->set(compact('races', 'zones', 'sports', 'tassPlacementTypes'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->Description->id = $id;
		if (!$this->Description->exists()) {
			throw new NotFoundException(__('Invalid description'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Description->delete()) {
			$this->Session->setFlash(__('The description has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The description could not be deleted. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
		}
		return $this->redirect(array('action' => 'index'));
	}

	public function ajaxFind()
	{
		$this->autoRender = false;

		if ($this->request->is('ajax'))
		{
			if ($this->request->data('tass'))
			{
				if($this->request->data('sport') == 'strength')
				{
					$description = $this->Description->find('first', array(
						'conditions' => array(
							'Description.tass_placement_type_id' => $this->request->data('tass'),
							'Description.zone_id' => $this->request->data('zoneId'),
							'Sport.value' => $this->request->data('sport'),
						),
                        'contain' => array(
                            'Sport',
                        )
					));
				}
				else
				{
					$description = $this->Description->find('first', array(
						'conditions' => array(
							'Description.tass_placement_type_id' => $this->request->data('tass'),
							'Description.race_id' => $this->request->data('raceId'),
							'Sport.value' => $this->request->data('sport'),
						),
					));
				}
			}

			if(empty($description))
			{
				if ($this->request->data('sport') == 'swim')
				{
					$description = $this->Description->find('first', array(
						'conditions' => array(
							'Description.zone_id' => $this->request->data('zoneId'),
							'Description.value' => $this->request->data('value'),
							'Description.trainingweek' => $this->request->data('trainingweek'),
							'Sport.value' => $this->request->data('sport'),
						),
                        'contain' => array(
                            'Sport',
                        )
					));

					//tempo
					if (empty($description))
					{
						$description = $this->Description->find('first', array(
							'conditions' => array(
								'Description.zone_id' => $this->request->data('zoneId'),
								'Description.trainingweek' => $this->request->data('trainingweek'),
								'Sport.value' => $this->request->data('sport'),
							),
                            'contain' => array(
                                'Sport',
                            )
						));
					}
				}
			}
            // as
			if(empty($description))
			{
				if ($this->request->data('zoneId') == '4')
				{
					$description = $this->Description->find('first', array(
						'conditions' => array(
							'Description.zone_id' => $this->request->data('zoneId'),
							'Description.race_id' => $this->request->data('raceId'),
							'Description.trainingweek' => $this->request->data('trainingweek'),
							'Sport.value' => $this->request->data('sport'),

						),
                        'contain' => array(
                            'Sport',
                        )
					));
				}
			}

            //brick
			if(empty($description))
			{
				if ($this->request->data('sport') == 'brick')
				{
					$description = $this->Description->find('first', array(
						'conditions' => array(
							'Description.trainingweek' => $this->request->data('trainingweek'),
							'Description.race_id' => $this->request->data('raceId'),
							'TassPlacementType.value' => 'brick',
						),
                        'contain' => array(
                            'Sport',
                            'TassPlacementType',
                        )
					));
				}
			}

			if(empty($description))
			{
				if ($this->request->data('sport') == 'run' || $this->request->data('sport') == 'bike')
				{
                    if(filter_var($this->request->data('addrace'), FILTER_VALIDATE_BOOLEAN)) {
                        $this->loadModel('WorkoutChild');
                        $workout = $this->WorkoutChild->find('first', array(
                            'conditions' => array(
        	                   'WorkoutChild.id' => $this->request->data('workoutId'),
                            ),
                            'contain' => array(
                                'ProgramChild.Program'
                            )));

                        if(!empty($workout)) {
                            $this->log('jenni');
                            $this->log($workout);
                            $trainingLength = $workout['ProgramChild']['Program']['training_length'];
                        }
                    } else {
                        $this->loadModel('Workout');
                        $workout = $this->Workout->find('first', array(
                            'conditions' => array(
        	                   'Workout.id' => $this->request->data('workoutId'),
                            ),
                            'contain' => array(
                                'Program',
                            )
                        ));

                        if(!empty($workout)) {
                            $trainingLength = $workout['Program']['training_length'];
                        }
                    }


                    if (isset( $trainingLength )) {

                        $this->loadModel('DescriptionPlacement');
                        $descriptionPlacement = $this->DescriptionPlacement->find('first', array(
                            'conditions' => array(
        	                   'Sport.value' => $this->request->data('sport'),
        	                   'DescriptionPlacement.training_week' => $this->request->data('trainingweek'),
        	                   'DescriptionPlacement.training_length' => $trainingLength,
                            ),
                            'contain' => array(
                                'Sport',
                                'TassPlacementType'
                            )
                        ));

                        if (!empty($descriptionPlacement)) {
        					$description = $this->Description->find('first', array(
        						'conditions' => array(
        							'Sport.value' => $this->request->data('sport'),
        							'Description.zone_id' => $this->request->data('zoneId'),
        							'Description.race_id' => $this->request->data('raceId'),
        							'Description.tass_placement_type_id' => $descriptionPlacement['TassPlacementType']['id'],
        						),
                                'contain' => array(
                                    'Sport',
                                )
        					));
                        }
                    }
				}
			}

            //$description = array();
			//$description['Description']['text'] = $descriptionPlacement['TassPlacementType']['name'];
			//$description['Description']['text'] = $workout['Program']['name'];

			if (!empty($description))
			{
				$data = array
				(
					'description' => $description['Description']['text'],
					'error' => null,
				);
			}
			else
			{
				$data = array
				(
					'description' => null,
					'error' => "No description found",
				);
			}
			return json_encode($data);
		}
		else
		{
			$this->redirect(array('controller' => 'pages', 'action' => 'dashboard'));
		}
	}
}
