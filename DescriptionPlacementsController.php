<?php
App::uses('AppController', 'Controller');
/**
 * DescriptionPlacements Controller
 *
 * @property DescriptionPlacement $DescriptionPlacement
 * @property PaginatorComponent $Paginator
 * @property SessionComponent $Session
 */
class DescriptionPlacementsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Session');

/**
 * admin_index method
 *
 * @return void
 */
	public function admin_index() {
		$this->DescriptionPlacement->recursive = 0;
        $descriptionPlacements = $this->DescriptionPlacement->find('all', array(
            'contain' => array(
                'Sport',
                'TassPlacementType',
            )
        ));
        $sports = $this->DescriptionPlacement->Sport->find('all', array(
            'recursive' => -1,
        ));
        $descriptionPlacementsSorted = array();
        foreach ($sports as $sport) {
            if(!isset($descriptionPlacementsSorted[$sport['Sport']['value']])) {
                $descriptionPlacementsSorted[$sport['Sport']['value']] = array();
            }
            for($trainingWeek = 0; $trainingWeek <= 20; $trainingWeek++) {
                if(!isset($descriptionPlacementsSorted[$sport['Sport']['value']][$trainingWeek])) {
                    $descriptionPlacementsSorted[$sport['Sport']['value']][$trainingWeek] = array();
                }
                /*
                for($trainingLength = 20; $trainingLength >= 1; $trainingLength--) {
                }
                */
            }
        }

        foreach ($descriptionPlacements as $descriptionPlacement) {
            $length = $descriptionPlacement['DescriptionPlacement']['training_length'];
            $week = $descriptionPlacement['DescriptionPlacement']['training_week'];
            $sport = $descriptionPlacement['Sport']['value'];

            $descriptionPlacementsSorted[$sport][$length][$week] = $descriptionPlacement;
        }
		$this->set('descriptionPlacementsSorted', $descriptionPlacementsSorted);
		$this->set('sports', $sports);
	}

/**
 * admin_view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_view($id = null) {
		if (!$this->DescriptionPlacement->exists($id)) {
			throw new NotFoundException(__('Invalid description placement'));
		}
		$options = array('conditions' => array('DescriptionPlacement.' . $this->DescriptionPlacement->primaryKey => $id));
		$this->set('descriptionPlacement', $this->DescriptionPlacement->find('first', $options));
	}

/**
 * admin_add method
 *
 * @return void
 */
	public function admin_add() {
		if ($this->request->is('post')) {
			$this->DescriptionPlacement->create();
			if ($this->DescriptionPlacement->save($this->request->data)) {
				$this->Session->setFlash(__('The description placement has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The description placement could not be saved. Please, try again.'));
			}
		}
		$sports = $this->DescriptionPlacement->Sport->find('list');
		$tassPlacementTypes = $this->DescriptionPlacement->TassPlacementType->find('list');
		$this->set(compact('sports', 'tassPlacementTypes'));
	}

/**
 * admin_edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_edit($id = null) {
		if (!$this->DescriptionPlacement->exists($id)) {
			throw new NotFoundException(__('Invalid description placement'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->DescriptionPlacement->save($this->request->data)) {
				$this->Session->setFlash(__('The description placement has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The description placement could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('DescriptionPlacement.' . $this->DescriptionPlacement->primaryKey => $id));
			$this->request->data = $this->DescriptionPlacement->find('first', $options);
		}
		$sports = $this->DescriptionPlacement->Sport->find('list');
		$tassPlacementTypes = $this->DescriptionPlacement->TassPlacementType->find('list');
		$this->set(compact('sports', 'tassPlacementTypes'));
	}

/**
 * admin_delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function admin_delete($id = null) {
		$this->DescriptionPlacement->id = $id;
		if (!$this->DescriptionPlacement->exists()) {
			throw new NotFoundException(__('Invalid description placement'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->DescriptionPlacement->delete()) {
			$this->Session->setFlash(__('The description placement has been deleted.'));
		} else {
			$this->Session->setFlash(__('The description placement could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
