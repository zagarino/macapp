<?php
App::uses('AppController', 'Controller');
/**
 * ProgramChildren Controller
 *
 * @property ProgramChild $ProgramChild
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class ProgramChildrenController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array ('Training');

	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Security->unlockedActions = array('ajaxGetTrainingweek');
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->ProgramChild->exists($id)) {
			throw new NotFoundException(__('Invalid program child'));
		}
		$options = array('conditions' => array('ProgramChild.' . $this->ProgramChild->primaryKey => $id));
		$this->set('programChild', $this->ProgramChild->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add()
	{
		if ($this->Session->check('addrace.data.ProgramChild.program_id'))
		{
			$this->request->data['ProgramChild']['program_id'] = $this->Session->read('addrace.data.ProgramChild.program_id');
		}
		else
		{
			return $this->redirect(array('controller' => 'programs', 'action' => 'index'));
		}

		if ($this->request->is('post'))
		{
			$this->ProgramChild->set($this->request->data);
			if ($this->ProgramChild->validates())
			{
				if ($this->ProgramChild->save($this->data))
				{
					$this->Session->setFlash(__('The Add Race has been saved.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-success'
					));
					return $this->redirect(array('controller' => 'programs', 'action' => 'index'));
				}
				else
				{
					$this->Session->setFlash(__('The program child could not be saved. Please, try again.'), 'alert', array(
						'plugin' => 'BoostCake',
						'class' => 'alert-danger'
					));
				}
			}
		}

		$programs = $this->ProgramChild->Program->find('list', array(
			'conditions' => array(
				'Program.user_id' => $this->Auth->user('id'),
			),
		));

		$races = $this->ProgramChild->Race->find('list', array(
			'fields' => array(
				'Race.value', 'Race.name',
			),
			'conditions' => array(
				'not' => array(
					'Race.value' => 'ironman',
				),
			),
		));

		$sports = $this->ProgramChild->Sport->find('list', array(
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

		/*
		foreach($raceTypes as $key => $raceType)
		{
			$raceTypes[$key] = $raceTypes[$key] . ' ' . __('Added Race');
		}
		 */

		$this->set(compact('programs', 'raceTypes'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null)
	{
		if ($this->request->is(array('post', 'put')))
		{
			if ($this->ProgramChild->save($this->request->data)) {
				$this->Session->setFlash(__('The program child has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program child could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		else
		{
			$options = array('conditions' => array('ProgramChild.' . $this->ProgramChild->primaryKey => $id));
			$this->request->data = $this->ProgramChild->find('first', $options);
		}

		$programs = $this->ProgramChild->Program->find('list');
		$races = $this->ProgramChild->Race->find('list');
		$sports = $this->ProgramChild->Sport->find('list');
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
		$this->ProgramChild->id = $id;
		if (!$this->ProgramChild->exists()) {
			throw new NotFoundException(__('Invalid program child'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProgramChild->delete()) {
			$this->Session->setFlash(__('The Add Race has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The Add Race could not be deleted. Please, try again.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));
		}
		return $this->redirect(array('controller' => 'programs', 'action' => 'index'));
	}

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->set('programChildren', $this->Paginator->paginate());

		if($this->request->is('post'))
		{
			if(isset($this->request->data['Filter']['keywords']))
			{
				if($this->request->data['Filter']['keywords'] === "")
				{
					$this->Session->delete('Search');
					return $this->redirect(array('action' => 'index'));
				}

				$this->Session->write('Search.keywords', $this->request->data['Filter']['keywords']);
				return $this->redirect(array('action' => 'index'));
			}
		}
		$this->ProgramChild->recursive = 0;

		if($this->Session->check('Search.keywords'))
		{
			$this->set('keywords', $this->Session->read('Search.keywords'));

			$conditions = array();
			$searchTerms = explode(' ', $this->Session->read('Search.keywords'));
			foreach($searchTerms as $searchTerm ){
				$conditions[] = array('OR' => array(
						'ProgramChild.id Like' =>'%'. $searchTerm .'%',
						'ProgramChild.name Like' =>'%'. $searchTerm .'%',
					)
				);
			}
			$this->set('programChildren', $this->paginate('ProgramChild', $conditions));
		}
		else
		{
			$this->set('programChildren', $this->Paginator->paginate());
		}
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->ProgramChild->exists($id)) {
			throw new NotFoundException(__('Invalid program child'));
		}
		$options = array('conditions' => array('ProgramChild.' . $this->ProgramChild->primaryKey => $id));
		$this->set('programChild', $this->ProgramChild->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->ProgramChild->create();
			if ($this->ProgramChild->save($this->request->data)) {
				$this->Session->setFlash(__('The program child has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program child could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		}
		$programs = $this->ProgramChild->Program->find('list');
		$races = $this->ProgramChild->Race->find('list');
		$sports = $this->ProgramChild->Sport->find('list');
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
		if (!$this->ProgramChild->exists($id)) {
			throw new NotFoundException(__('Invalid program child'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProgramChild->save($this->request->data)) {
				$this->Session->setFlash(__('The program child has been saved.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-success'
				));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The program child could not be saved. Please, try again.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-danger'
				));
			}
		} else {
			$options = array('conditions' => array('ProgramChild.' . $this->ProgramChild->primaryKey => $id));
			$this->request->data = $this->ProgramChild->find('first', $options);
		}
		$programs = $this->ProgramChild->Program->find('list');
		$races = $this->ProgramChild->Race->find('list');
		$sports = $this->ProgramChild->Sport->find('list');
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
		$this->ProgramChild->id = $id;
		if (!$this->ProgramChild->exists()) {
			throw new NotFoundException(__('Invalid program child'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProgramChild->delete()) {
			$this->Session->setFlash(__('The program child has been deleted.'), 'alert', array(
					'plugin' => 'BoostCake',
					'class' => 'alert-info'
				));
		} else {
			$this->Session->setFlash(__('The program child could not be deleted. Please, try again.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));
		}
		return $this->redirect(array('action' => 'index'));
	}

	public function ajaxGetTrainingweek()
	{
		$this->autoRender = false;

		if ($this->request->is('ajax'))
		{
			$range = $this->ProgramChild->getTrainingweek($this->request->data('date'), $this->request->data('programId'));
			if($range)
			{
				return json_encode(array(
					'trainingweek' => $range,
					'error' => null,
				));
			}
			else
			{
				return json_encode(array(
					'error' => __('Out of training date range.'),
				));
			}
		}
	}

	public function program($program_id = null)
	{
		$program = $this->ProgramChild->Program->find('first', array(
			'conditions' => array(
				'Program.id' => $program_id,
				'Program.visible' => 1,
				'Program.user_id' => $this->Auth->user('id'),
			),
		));

		if(!empty($program))
		{
			if($this->Session->write('addrace.data.ProgramChild.program_id', $program_id))
			{
				return $this->redirect(array('action' => 'add'));
			}
		}

		if($this->request->is('post'))
		{
			$program = $this->ProgramChild->Program->find('first', array(
				'conditions' => array(
					'Program.id' => $this->request->data['ProgramChild']['program_id'],
					'Program.visible' => 1,
					'Program.user_id' => $this->Auth->user('id'),
				),
			));

			if(!empty($program))
			{
				if($this->Session->write('addrace.data.ProgramChild.program_id', $this->request->data['ProgramChild']['program_id']))
				{
					return $this->redirect(array('action' => 'add'));
				}
			}
		}

		$programs = $this->ProgramChild->Program->find('list', array(
			'conditions' => array(
				'Program.user_id' => $this->Auth->user('id'),
				'Program.visible' => 1,
			),
		));

		if(empty($programs))
		{
			$this->Session->setFlash(__('For Add Race create programs first.'), 'alert', array(
				'plugin' => 'BoostCake',
				'class' => 'alert-danger'
			));

			return $this->redirect(array('controller' => 'programs', 'action' => 'index'));
		}

		$this->set(compact('programs'));
	}
}
