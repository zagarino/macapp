<?php
/**
 * Static content controller.
 *
 * This file will render views from views/pages/
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 */

App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{

/**
 * This controller does not use a model
 *
 * @var array
 */
	public $uses = array();
	public $components = array('Training');

	public function beforefilter()
	{
		parent::beforeFilter();
		$this->Security->unlockedActions = array('fullscreen', 'ajaxFindStats');
		$this->Auth->allow('home');

		$this->loadModel('Program');
    $this->helpers['Calendar'] = array(
			'shapes' => $this->Program->Workout->Behaviors->Adjustment->shapes,
			'shapeLabels' => $this->Program->Workout->Behaviors->Adjustment->shapeLabels,
		);
	}

	public function credit()
	{
		if ($this->request->is('post'))
		{
			$this->log($this->request->data);
		}
	}

/**
 * Displays a view
 *
 * @return void
 * @throws NotFoundException When the view file could not be found
 *   or MissingViewException in debug mode.
 */
	public function dashboard()
	{
		$this->log('geldbeutel');
		$this->set('title_for_layout', __('Dashboard'));
		$today = new DateTime();
		$this->loadModel('Program');

		$program = array();

		$currentProgram = $this->Program->find('first', array(
			'conditions' => array(
				'and' => array(
					'user_id =' => $this->Auth->user('id'),
					'Program.race >=' => $today->format('Y-n-j'),
					'Program.training <=' => $today->format('Y-n-j'),
					'Program.visible' => true,
				)
			),
			'fields' => array(
				'id',
				'name',
				'race',
				'training',
				'training_length',
				'Race.name',
			),
			'contain' => array(
				'Race',
			),
		));

		$nextProgram = $this->Program->find('first', array(
			'conditions' => array(
				'and' => array(
					'user_id =' => $this->Auth->user('id'),
					'Program.race >=' => $today->format('Y-n-j'),
					'Program.visible' => true,
				)
			),
			'fields' => array(
				'id',
				'name',
				'race',
				'training',
				'training_length',
				'Race.name',
			),
			'contain' => array(
				'Race',
			),
			'order' => array(
				'race' => 'asc'
			),
		));

		if (!empty($currentProgram))
		{
			$currentProgram['dashboard'] = 'current';
			$currentProgram['progress']  = $this->_progress($currentProgram['Program']['training'],$currentProgram['Program']['race']);
			$this->set('program', $currentProgram);
		}
		elseif (!empty($nextProgram))
		{
			$nextProgram['dashboard'] = 'next';
			$nextProgram['progress']  = $this->_progress($nextProgram['Program']['training'],$nextProgram['Program']['race']);
			$this->set('program', $nextProgram);
		}
		else
		{
			$program = array(
				'dashboard' => null
			);

			$this->set('program', $program);
		}

		$workouts = $this->Program->Workout->find('all', array(
			'conditions' => array(
				'and' => array(
					'Program.user_id =' => $this->Auth->user('id'),
					'Workout.date =' => $today->format('Y-n-j'),
				),
			),
			'contain' => array(
				'Program',
				'Zone',
				'Sport' => array(
					'Measurement'
				)
			)
		));

        $this->loadModel('User');
        $this->set('notTriedout', $this->User->notTriedout());
        $this->set('isTryingout', $this->User->isTryingout());

        if(!empty($workouts)) {
            $this->set('workouts', $workouts);
        }

	}

	private function _progress($training, $race)
	{
		$today = new Datetime();

		$training = new DateTime($training);
		$race = new DateTime($race);

		$max = $training->diff($race);
		$middle = $training->diff($today);

		return array(
			'percent' => round(100*$middle->days/$max->days),
			'middle' => $middle->days,
			'max' => $max->days,
		);
	}

	public function admin_panel()
	{
		#Configure::write('Config.language', 'chi');
	}

	public function calendar($year = 0, $month = 0)
	{
		$this->set('title_for_layout', __('Calendar'));

		$today = new DateTime();

		if(!$year || !$month)
		{
			$year = $today->format('Y');
			$month = $today->format('n');
		}

		$date = new DateTime($year.'-'.$month.'-'.'1');

		$this->set('year', $year);
		$this->set('month', $month);

		$this->loadModel('Program');

		$sortedEntries = $this->Program->findAndSortByDate($date, $this->Auth->user('id'));

		$sortedEntryChildren = $this->Program->ProgramChild->findAndSortByDate($date, $this->Auth->user('id'));

		/*

		$races = $this->Program->Race->find('all', array(
			'recursive' => -1,
		));

		$this->loadModel('Zone');
		$zones = $this->Zone->find('all', array(
			'recursive' => -1,
		));

		$dob = new DateTime($this->Auth->user('date_of_birth'));
		$now = new DateTime();
		$age = $now->diff($dob);

		for($program = 0; $program < count($programs); $program++)
		{
			$workouts = array(
				'workoutsNow' => $programs[$program]['Program']['workouts_now'],
				'workoutsLastYear' => $programs[$program]['Program']['workouts_last_year'],
				'workoutsPreviousYear' => $programs[$program]['Program']['workouts_previous_year'],
				'workoutsYearBefore' => $programs[$program]['Program']['workouts_year_before']
			);
			$programs[$program]['Program']['heart_rate'] = $this->Training->heartRate($age->y, $workouts, $zones, $races);
		}
		 */

		$this->set('sortedEntries', $sortedEntries);
		$this->set('sortedEntryChildren', $sortedEntryChildren);
		$this->set('shapes', $this->Program->Workout->Behaviors->Adjustment->shapes);
	}

	public function fullscreen()
	{
		$this->autoRender = false;
		if($cookie = $this->Cookie->read('fullscreen'))
		{
			$this->Cookie->write('fullscreen', 0, true, '12 months');
		}
		else
		{
			$this->Cookie->write('fullscreen', 1, true, '12 months');
		}

		if ($this->request->is('ajax'))
		{
			$data = array
			(

				'content' => $this->Cookie->read('fullscreen'),
				'error' => null
			);
			return json_encode($data);
		}
		else
		{
			$this->redirect(array('controller' => 'pages', 'action' => 'dashboard'));
		}
	}

	public function statistic()
	{
		$this->render('construction');
	}

	public function admin_email()
	{
		$hash = Security::hash(mt_rand(),'md5',true);
		$this->Session->setFlash('Has been sent', 'alert', array(
			'plugin' => 'BoostCake',
			'class' => 'alert-info'
		));

		$Email = new CakeEmail('default');
		//$Email->template('default', 'default');
		//$Email->emailFormat('both');
		$Email->from(array('support@markallencoaching.com' => 'Mark Allen Coaching'));
		$Email->to('uflaig@gearhead-consulting.com');
		$Email->subject('Password reset');
		$Email->send("My message lets make this message a bit longe and even a little bt morer\n".$hash);


		$this->redirect(array('action' => 'panel'));
	}
    public function admin_freeCharges()
    {
        $this->loadModel('User');
        //$users = array("aakenned","AaronW","abelbajan","aca5191","Adamgordon","aemiller","afrederick","aileengetty","alexcybul","Alisa77","Andres1984","ANDYALONSO","Angela","annadee","Annie123","Araujonil68","Arirang","AttilaKelemen","Bamorelli","bangtrinh","barbarabryan","BartFoster03","berardpb","billdieter","Billkelley","bkuhlsf","BlaneAddison","bsalbador","Bwallace","CactusCharlie","Cadencechem","Carabeth77","cbrockus","ceill44","Cfranklin","cgault","Champion","christherunner","clancyacpo","clcass","cmonty34","Conroym12","cornelnitu","Cresner","Criscr","csquared","Ctkeebler0804","dacronwall","dandamian","DanielMiccolis","danpoirier","dansd2001","darcyhoneycutt","darinvia","darylpt","davepredzin","DavidC","davischad90","DawnPaige12","Debbiepotts","dembury","diegopinilla","distfree","dmasterson","dmburg","drbrad11","drturner","dsihnfhg","EBurns2016","echebi","ellis1211","equigrupo","fariza","Farrunner64","fazio64","felipevillamizar","Ferust","fhager","Fran2016","FrenchySteph","g5fiddy","garthdavis","Garthpetersen","garyallentucci","Gatorpt","gcates","George1962","germanglzr","GettingBetter","Gfarm1990","ggeiger","Gobi6tank","Gokhan","Gom54321","Gracemcclure20","gukohl","gutomilano","guvnor","gwarnholtz","haakonth","Haydenh","hojedaelp","iamrobertmoore","Ignacio","imonaghan","Ironman76","Ironswiss","IWillBeIron","janarella","JasonQuero","Javierfuentes","Jaytri01","jderx2000","jeffindtw","Jfm225","jfronly1","jgagnon232","Jinx07","jjcarrol71","jlamoureux","jm1chevere","Joeydaman","JoeyHinton","JohnSolari","jpdavis","jrakoczy","jtimryan","juliekenar","jurgen54","Justus123","jyalof","karmaedwards","KDeMarco","kevinbsauer","kevinlowe","khanhnd","KKenney9","kmadrid1974","Kristin","Kurtholt","lancelover1976","Laurie1978","LCONSTANT","leavelez","LeeLock77","Leogaga","Lesmaas","LTSCHOPP","luaustx","luigipv1969","LyndaChase","marclundeberg","markemerg","markieconkie","MarkRichards","martin","massemus","matsallen","mbmcallister535","mdrironman","Medudzik","melbourne2016","melur39","Micdoc","miked77","mikejhope12","MikeRobi","Mikestevens089","millersw75","mkumbaraci","mlhat936","Moley1966","momika","Moneypenny","Moonsegason","mratner","mred62","MsHMFIC","msjohnston12","mtcole","Nachovega395","ncicerchi","ndpoland","Nomad575","nunomcdesousa","ojie68","oodescampe","Pascal","petercrisera","peterrichards","phelps1022","Potiphar","prbrotherton","pstephen","punkutsu","pvelez","ranchocortina","Raullace","raymondpd","razlanrazali","rbalfelor","renaldo","renmarco","rickypalau","riveraj2000","rlepage","rmwilkin","robertd","rodmorrison","RohanSmith","romanbravo10","roth22","safdetti","Samantha1116","sandersonjd","Saracen","schutze007","sciarrilli","scottbeasley11","scottbperrine","ScottG","sgoodfellow","shellyrud","shulessjoe","sideburn14","simonoldacre","sirenders","Smnewman","snoball27","Spchung","ssassman12","sscotttri","staciestraw","starchc","stefanoamantini","stefanoamantini2","StephenHarper65","SteveFinlay18","stevenhaywood","StevenKeller9","stonekara","SwimmerInTransition","tcummings72","tdoates","thebialeks","thrill1031","tishida","Tjcar9","TriathleteWes","trijuju","trisunred","tsukolsky","Uahogs","Varlw69","Vicenterangel","viniciusmotta","VitaminJ","Vsahlieh","wadsfit","Wall25061","wbworkman","wdelvallemd","Westgate77","WFSMAN","winky436","Wuttinon","Wynand001","zagarino");
        $freeCharges = array(2,4,7,16,4,1,1,2,1,2,2,1,1,1,14,12,1,1,6,5,1,4,1,1,5,5,11,2,1,1,2,7,20,11,10,1,7,1,1,13,1,1,3,1,6,1,6,1,5,2,3,1,1,2,1,1,8,4,2,1,5,2,20,4,16,2,1,12,3,1,1,1,3,2,1,2,12,8,12,5,1,1,1,6,1,1,9,4,2,15,6,3,1,2,18,6,15,10,3,1,4,1,1,4,7,1,11,1,2,1,7,1,1,4,9,3,1,2,12,1,7,13,2,2,3,10,1,1,8,9,1,1,1,8,1,3,3,6,2,12,1,2,4,1,1,1,2,6,15,1,3,12,1,5,7,2,7,5,1,7,8,8,1,1,7,2,3,3,2,9,12,7,1,2,5,9,9,3,1,2,1,5,12,4,1,2,1,6,6,1,3,1,6,1,1,1,2,1,1,2,6,9,5,1,13,7,7,12,1,1,10,5,12,1,5,6,2,1,4,6,12,1,5,1,6,3,8,3,1,9,24,4,9,1,4,6,9,2,2,1,5,1,1,5,1,1,24,1,2,12,7,12,3,1,10,1,6,5,1,1,11,2,34,11,1,2,2,1);
        $ids = array(1211,756,593,1354,1246,1233,280,1210,837,1231,1225,241,431,681,1331,1078,92,530,1317,424,761,1323,353,799,941,1270,440,253,414,95,1252,1306,538,1274,1003,34,590,1015,456,1266,195,240,1277,484,1255,57,1000,1071,474,1223,1234,516,862,142,478,71,821,1238,739,864,1046,348,137,911,682,318,190,192,514,385,291,354,692,1241,444,828,1359,750,589,548,804,401,300,338,276,1220,1194,1077,267,1284,822,351,117,865,983,1228,222,1047,54,143,165,976,884,1135,1260,311,889,53,295,86,921,519,36,1324,1169,1102,798,499,337,281,1232,180,120,914,1269,324,1161,814,155,699,94,225,850,815,428,1171,1136,785,667,1358,245,1215,1174,248,1315,1247,179,1134,18,425,254,1264,728,59,1086,1279,1237,1348,460,1250,1148,327,722,426,1353,873,144,1361,135,1248,528,383,1062,60,673,594,757,1314,325,221,132,1301,1362,1307,797,732,881,661,763,1140,450,601,73,500,430,317,419,423,835,260,700,1163,1316,283,1258,1111,1182,597,581,403,1112,436,1289,256,665,1118,475,106,1302,266,697,380,367,947,1334,1272,641,954,194,876,275,1290,1162,362,1164,1304,642,1074,312,1056,1282,35,623,1186,441,649,183,97,1139,1360,1063,738,574,470,227,993,98,1356,421,1081,825,1261,515,618,204,1221,1189,866);
        foreach ($ids as $key => $id) {
            $this->User->id = $id;
            $this->User->saveField('charged', $freeCharges[$key]);
        }

		$this->redirect(array('action' => 'panel'));
    }

	public function home()
	{
	}

	public function ajaxFindStats()
	{
		$today = new DateTime();
		$this->loadModel('Program');

		$program = $this->Program->find('first', array(
			'conditions' => array(
				'and' => array(
					'user_id =' => $this->Auth->user('id'),
					'Program.race >=' => $today->format('Y-n-j'),
					'Program.training <=' => $today->format('Y-n-j'),
				)
			),
			'contain' => array(
				'Workout' => array(
					'shape',
					'trainingweek',
					'Sport' => array(
						'value'
					)
				),
			),
		));
		$this->autoRender = false;

		return json_encode(array('success' => $program));
		//$workouts = $this->Workout->find('all', array( ));
	}

	public function isAuthorized($user)
	{
        if($this->request->params['action'] === 'calendar') {
            if(!$this->checkUserAccess()) {
                return $this->redirect(array('controller' => 'Subscriptions', 'action' => 'index'));
            }
        }

		return parent::isAuthorized($user);
	}

	public function english()
	{
		$this->autoRender = false;
		$this->Session->write('Config.language', 'eng');
		$this->redirect($this->referer());
	}

	public function chinese()
	{
		$this->autoRender = false;
		$this->Session->write('Config.language', 'zho');
		$this->redirect($this->referer());
	}
}
