<?php
app::uses('Component', 'Controller');


class TrainingComponent extends Component
{
	protected $weekDays = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
	protected $max = 20;
	protected $min = 1; private $heartInit = 180;

	public function heartRate($age,$workouts, $zones, $races)
	{
		//=-ROUND(($'Main Race planner'.B5)-ABS($'Main Race planner'.B5-40)*0.25,0)
		$abs = abs($age-40);
		$multiplied = $abs * 0.25;
		$ageModifier = -round($age - $multiplied);
		$this->log('age');
		$this->log($age);
		$this->log('abs');
		$this->log($abs);
		$this->log('multiplied');
		$this->log($multiplied);

		$workoutModifier = 0;
		if($workouts['workoutsNow'] <= 0)
		{
			$workoutModifier -= 5;
		}
		if($workouts['workoutsLastYear'] >= 6)
		{
			$workoutModifier += 5;
		}
		if($workouts['workoutsPreviousYear'] >= 6)
		{
			$workoutModifier += 1;
		}
		if($workouts['workoutsYearBefore'] >= 6)
		{
			$workoutModifier += 1;
		}

		$totalMaxAerobic= $this->heartInit + $ageModifier + $workoutModifier;
		$zoneHeartRates = array();

		foreach ($zones as $zone)
		{
			switch ($zone['Zone']['initial'])
			{
				case 're' :
					$zoneHeartRates[$zone['Zone']['id']]['min'] = $totalMaxAerobic - 35;
					$zoneHeartRates[$zone['Zone']['id']]['max'] = $totalMaxAerobic - 10;
					breaK;
				case 'ad' :
					$zoneHeartRates[$zone['Zone']['id']]['min'] = $totalMaxAerobic - 25;
					$zoneHeartRates[$zone['Zone']['id']]['max'] = $totalMaxAerobic - 5;
					breaK;
				case 'ed' :
					$zoneHeartRates[$zone['Zone']['id']]['min'] = $totalMaxAerobic - 20;
					$zoneHeartRates[$zone['Zone']['id']]['max'] = $totalMaxAerobic;
					breaK;
				case 'as' :
					$zoneHeartRates[$zone['Zone']['id']]['min'] = $totalMaxAerobic - 10;
					$zoneHeartRates[$zone['Zone']['id']]['max'] = $totalMaxAerobic;
					breaK;
				case 'sp' :
					$zoneHeartRates[$zone['Zone']['id']]['min'] = $totalMaxAerobic;
					$zoneHeartRates[$zone['Zone']['id']]['max'] = abs($age - 220);
					breaK;
				case 't' :
					$zoneHeartRates[$zone['Zone']['id']]['min'] = $totalMaxAerobic;
					$zoneHeartRates[$zone['Zone']['id']]['max'] = $totalMaxAerobic + 15;
					breaK;
			}
			$zoneHeartRates[$zone['Zone']['id']]['value'] = $zone['Zone']['value'];
			$zoneHeartRates[$zone['Zone']['id']]['initial'] = $zone['Zone']['initial'];
			$zoneHeartRates[$zone['Zone']['id']]['name'] = $zone['Zone']['name'];
			$zoneHeartRates[$zone['Zone']['id']]['id'] = $zone['Zone']['id'];
		}


		foreach ($races as $race)
		{
			switch ($race['Race']['value'])
			{
				case 'sprint' :
					$raceHeartRates[$race['Race']['id']]['min'] = $totalMaxAerobic + 10;
					$raceHeartRates[$race['Race']['id']]['max'] = $totalMaxAerobic + 25;
					breaK;
				case 'olympic' :
					$raceHeartRates[$race['Race']['id']]['min'] = $totalMaxAerobic + 5;
					$raceHeartRates[$race['Race']['id']]['max'] = $totalMaxAerobic + 20;
					breaK;
				case 'half-ironman' :
					$raceHeartRates[$race['Race']['id']]['min'] = $totalMaxAerobic;
					$raceHeartRates[$race['Race']['id']]['max'] = $totalMaxAerobic + 15;
					breaK;
				case 'ironman' :
					$raceHeartRates[$race['Race']['id']]['min'] = $totalMaxAerobic - 15;
					$raceHeartRates[$race['Race']['id']]['max'] = $totalMaxAerobic;
					breaK;
			}
			$raceHeartRates[$race['Race']['id']]['value'] = $race['Race']['value'];
			$raceHeartRates[$race['Race']['id']]['name'] = $race['Race']['name'];
			$raceHeartRates[$race['Race']['id']]['id'] = $race['Race']['id'];
		}

		return array(
			'totalMaxAerobic' => $totalMaxAerobic,
			'Race' => $raceHeartRates,
			'Zone' => $zoneHeartRates,
		);
	}

	public function generateWorkouts($raceDate, $trainingLength, $placements, $brick, $ed, $ad, $as, $recovery, $tass, $dbr, $mts, $strength, $heartRates, $programId, $maintenance = false)
	{
		$workouts = array();
		$raceDate = new DateTime($raceDate['year'].'-'.$raceDate['month'].'-'.$raceDate['day']);
		$trainingDate = clone $raceDate;
		$trainingDate->modify('-'.$trainingLength.' weeks');

		//todo: better solution to switch between str1 and str2 tass types
		$firstStrengthInWeek = true;

		$weekday = (int)$trainingDate->format('w');

		if($weekday != 1)
		{
			$trainingDate->modify('next monday');
		}

		$date = $trainingDate;

		for ($week = 1; $week <= $trainingLength; $week++)
		{
            /*
			if ($trainingCharged >= $week)
			{
				$visible = true;
			}
			else
			{
				$visible = false;
			}
            */
            $visible = true;

			if ($week < $trainingLength || $maintenance)
			{
				for ($day = 0; $day < count($this->weekDays);$day++)
				{

					foreach ($placements as $placement)
					{
						if ($placement['WorkoutPlacement']['week_day'] == $day)
						{
							$workout = array();

							if ($placement['Sport']['value'] == 'strength')
							{
								if(isset($strength[$week]))
								{
									$workout['sport_id'] = $placement['WorkoutPlacement']['sport_id'];
									$workout['zone_id'] = $strength[$week];

									//if zone is power and first strength in week
									if($strength[$week] == 9 && $firstStrengthInWeek)
									{
										$workout['tass_placement_type_id'] = 63;
										$firstStrengthInWeek = false;
									}
									//if zone is power
									elseif($strength[$week] == 9)
									{
										$workout['tass_placement_type_id'] = 64;
										$firstStrengthInWeek = true;
									}
									elseif($week == 1)
									{
										$workout['tass_placement_type_id'] = 63;
									}
									else
									{
										$workout['tass_placement_type_id'] = 64;
									}
								}
							}

							if ($placement['Zone']['initial'] == 'ed')
							{
								$workout['value'] = $ed[$placement['WorkoutPlacement']['sport_id']][$week];

								// adding possible brick
								if($placement['Sport']['value'] == 'bike' && isset($brick[$week]))
								{
									$workout['brick'] = $brick[$week];
								}
							}
							elseif ($placement['Zone']['initial'] == 'ad')
							{
								$workout['value'] = $ad[$placement['WorkoutPlacement']['sport_id']][$week];
							}
							elseif ($placement['Zone']['initial'] == 're')
							{
								$workout['value'] = $recovery[$placement['WorkoutPlacement']['sport_id']][$week];
							}
							elseif (!isset($placement['Zone']['initial']) && $placement['Sport']['value'] != 'strength')
							{
								$tassType = $tass[$placement['WorkoutPlacement']['sport_id']][$week-1]['TassPlacementType'];

								$workout['zone_id'] = $tassType['Zone']['id'];
								$workout['sport_id'] = $placement['WorkoutPlacement']['sport_id'];
								$workout['heart_min'] = $heartRates['Zone'][$workout['zone_id']]['min'];
								$workout['heart_max'] = $heartRates['Zone'][$workout['zone_id']]['max'];

                // maintenance doesn't have speed workouts, convert al speed workouts into tempo
								$initial = $tassType['Zone']['initial'];
								if (isset($initial) && ($initial == 'sp') && $maintenance)
								{
                                    $tassType['Zone']['initial'] = 'as';
                                    $workout['zone_id'] = 4;
                                    $workout['heart_min'] = $heartRates['Zone'][4]['min'];
                                    $workout['heart_max'] = $heartRates['Zone'][4]['max'];
								}

								if (isset( $tassType['Zone']['initial'] ) && ($tassType['Zone']['initial'] == 'as'))
								{
									$workout['value'] = $as[$placement['WorkoutPlacement']['sport_id']][$week];
								}

								// swim speed and tempo needs a value so the description for swim sp/t will pop up
								if (isset($tassType['Zone']['initial']) && ($tassType['Zone']['initial'] == 't' || $tassType['Zone']['initial'] == 'sp'))
								{
									if($placement['Sport']['value'] == 'swim')
									{
										$workout['value'] = $as[$placement['WorkoutPlacement']['sport_id']][$week];
									}
								}

								if(isset($tassType['id']))
								{
									$workout['tass_placement_type_id'] = $tassType['id'];
								}

								if (isset( $tassType['Zone']['initial'] ) && $tassType['Zone']['initial'] == 'sp' && $placement['Sport']['value'] == 'swim' && $week == 19) //MTS1
								{
									foreach ($mts as $mt)
									{
										if($mt['TassPlacementType']['name'] == 'MTS1')
										{
											$workout['tass_placement_type_id'] = $mt['TassPlacementType']['id'];
										}
									}
								}
							}

							if ($placement['Zone']['initial'] == 'ed' || $placement['Zone']['initial'] == 'ad' || $placement['Zone']['initial'] == 're')
								//if ($placement['Sport']['value'] != 'strength')
							{
								$workout['zone_id'] = $placement['WorkoutPlacement']['zone_id'];
								$workout['sport_id'] = $placement['WorkoutPlacement']['sport_id'];
								$workout['heart_min'] = $heartRates['Zone'][$placement['WorkoutPlacement']['zone_id']]['min'];
								$workout['heart_max'] = $heartRates['Zone'][$placement['WorkoutPlacement']['zone_id']]['max'];
							}
							if (empty($workout['value']))
							{
								unset($workout['value']);
							}
							if(!empty($workout))
							{
								$workout['date'] = array(
										'year' => $date->format('Y'),
										'month' => $date->format('n'),
										'day' => $date->format('j'),
								);

								$workout['visible'] = $visible;
								$workout['trainingweek'] = $week;

								$workout['program_id'] = $programId;

								array_push($workouts, $workout);
							}
						}

					}
					$date->modify('next day');
				}
			}
			else
			{
				foreach ($dbr as $dayBeforeRace)
				{
					$dateBeforeRace = clone $raceDate;
					$dateBeforeRace->modify('-'.$dayBeforeRace['DaysBeforeRace']['day'].' days');

					if($dateBeforeRace >= $date) // don't add dbr if it is overlaying workouts from the second last week
					{
						$workout['value'] = $dayBeforeRace['DaysBeforeRace']['value'];
						$workout['race_id'] = $dayBeforeRace['DaysBeforeRace']['race_id'];
						$workout['zone_id'] = $dayBeforeRace['DaysBeforeRace']['zone_id'];
						$workout['sport_id'] = $dayBeforeRace['DaysBeforeRace']['sport_id'];
						$workout['trainingweek'] = $trainingLength;

						$workout['date'] = array(
							'year' => $dateBeforeRace->format('Y'),
							'month' => $dateBeforeRace->format('n'),
							'day' => $dateBeforeRace->format('j'),
						);

						foreach ($mts as $mt)
						{
                            $this->log('NEW ' . $dayBeforeRace['DaysBeforeRace']['day']);
							if($mt['TassPlacementType']['name'] == 'MT'.$dayBeforeRace['DaysBeforeRace']['day'])
							{
                                $this->log('WHAT ' . $mt['TassPlacementType']['name']);
								//$this->log($mt['TassPlacementType']['name']);
								$workout['tass_placement_type_id'] = $mt['TassPlacementType']['id'];
							}

						}

						array_push($workouts, $workout);
					}

				}

			}

		} //endfor

		for ($no = 0 ; $no < count($workouts) ; $no++)
		{
			$workouts[$no]['no'] = $no + 1;
		}

		return $workouts;
	}
	//public function preview($workoutsPerWeek)
	//{
		//$workoutsPerWeek = (int)$workoutsPerWeek;
		//$workouts = array();

		//if ($workoutsPerWeek === 8 || $workoutsPerWeek === 11 || $workoutsPerWeek === 14)
		//{
			//foreach ($this->lengths as $length)
			//{
				//if ($workoutsPerWeek === count($length))
				//{
					//foreach ($length as $workout)
					//{
						//if(isset($workout['zone']))
						//{
							//$zone = $this->zones[$workout['zone']];
						//}
						//else
						//{
							//$zone = null;
						//}

						//array_push($workouts, array
						//(
							//'day' => $workout['day'],
							//'sport' => $this->sports[$workout['sport']],
							//'zone' => $zone
						//));
					//}
				//}
			//}
		//}
		//else
		//{
			////throw error
		//}

		//return $workouts;
	//}

	public function firstTimeRace($trainingLength, $maintenance = false)
	{
		$date = new DateTime();
		$date->modify('+'.$trainingLength.' weeks');
		$date->modify('next monday');
        if (!$maintenance) {
            $date->modify('-1 day');
        }

		return array('day' => $date->format('j'), 'month' => $date->format('n'), 'year' => $date->format('Y'));
	}

	public function getTrainingDate($year, $month, $day, $trainingLength)
	{
			$date = new DateTime($year.'-'.$month.'-'.$day);
			$date->modify('-'.$trainingLength.' weeks');

			$weekday = (int)$date->format('w');

			if($weekday != 1)
			{
				$date->modify('next monday');
			}

			return $date;
	}

	public function firstTimeTraining()
	{
		$date = new DateTime();
		$date->modify('next monday');
		return array('day' => $date->format('j'), 'month' => $date->format('n'), 'year' => $date->format('Y'));
	}

	public function checkMove($placements, $weekDay, $key)
	{
		//$this->log(empty($placements[$key]['Zone']['id']));
		foreach ($placements as $placement)
		{
			if ($placement['WorkoutPlacement']['week_day'] == $weekDay)
			{
				if($placements[$key]['Sport']['value'] == 'run')
				{
					if ($placements[$key]['Sport']['value'] == $placement['Sport']['value'])
					{
						return __('No run workouts on the same day.');
					}
					elseif (empty($placements[$key]['Zone']['initial']) && $placements[$key]['Sport']['value'] != 'strength' && $placement['Zone']['initial'] == 'ed' && $placement['Sport']['value'] == 'bike')
					{
						return __('You can\'t move a Speed / Tempo / Aerobic Stimulation workout on a day with an Endurance Development bike workout.');
					}
					elseif ($placements[$key]['Zone']['initial'] == 'ed' && $placement['Zone']['initial'] == 'ed' && $placement['Sport']['value'] == 'bike')
					{
						return __('You can\'t move an Endurance Development workout on a day with an Endurance Development bike workout.');
					}
				}
				elseif($placements[$key]['Sport']['value'] == 'bike')
				{
					if ($placements[$key]['Sport']['value'] == $placement['Sport']['value'])
					{
						return __('No run workouts on the same day.');
					}
					elseif (empty($placements[$key]['Zone']['initial']) && $placements[$key]['Sport']['value'] != 'strength' && $placement['Zone']['initial'] == 'ed' && $placement['Sport']['value'] == 'run')
					{
						return __('You can\'t move a Speed / Tempo / Aerobic Stimulation workout on a day with an Endurance Development run workout.');
					}
					elseif ($placements[$key]['Zone']['initial'] == 'ed' && $placement['Zone']['initial'] == 'ed' && $placement['Sport']['value'] == 'run')
					{
						return __('You can\'t move an Endurance Development workout on a day with an Endurance Development run workout.');
					}
				}
				elseif($placements[$key]['Sport']['value'] == 'swim')
				{
					if ($placements[$key]['Sport']['value'] == $placement['Sport']['value'])
					{
						return __('No swim workouts on the same day.');
					}
				}
			}
		}
		return true;
	}

	public function progression($trainingLength, $peaks, $peakStart)
	{
		$progression = array();
		$down = ($trainingLength+1) % 3;

		for ($week = $trainingLength;$week >= 1;$week--)
		{
			$progression[$week] = array();
			if ($week == $trainingLength)
			{
				array_push($progression[$week],'wtr');
			}
			if($week > ($trainingLength - $peakStart+1) && $week != 1)
			{
				array_push($progression[$week],'taper');
			}
			else
			{

				if($week == $trainingLength-$peakStart+1 || $trainingLength-$peakStart < 0)
				{
					array_push($progression[$week],'longest');
					$peaks--;
				}
				elseif($week % 3 == ($trainingLength - $peakStart +1) % 3 && $peaks >0 && $week != 1)
				{
					array_push($progression[$week],'maximum');
					$peaks--;
				}
				if ($week != 1)
				{
					if($week % 3 == ($trainingLength - $peakStart-1) % 3 && $week != 1)
					{
						array_push($progression[$week],'roundDown');
					}
					elseif($week == $peakStart -1 && $week == 3)
					{
						array_push($progression[$week],'even');
					}
					else
					{
						array_push($progression[$week],'roundUp');
					}

				}
			}
		}

		return $progression;
	}

	public function adjustInput($inputs, $minimumInputs, $maximumInputs)
	{
		foreach ($inputs as $key => $input)
		{
			foreach($minimumInputs as $minimumInput)
			{
				if($minimumInput['Sport']['value'] == $key)
				{
					if($input < $minimumInput['Safeguard']['value'])
					{
						$inputs[$minimumInput['Sport']['value']] = $minimumInput['Safeguard']['value'];
					}
				}
			}

			foreach($maximumInputs as $maximumInput)
			{
				if($maximumInput['Sport']['value'] == $key)
				{
					if($input > $maximumInput['Safeguard']['value'])
					{
						$inputs[$maximumInput['Sport']['value']] = $maximumInput['Safeguard']['value'];
					}
				}
			}
		}
		return $inputs;
	}

	public function ed($trainingLength, $output, $sport, $progressions, $tapers, $modifier, $safeguard, $maintenance = 1.00)
	{
        $maintenanceEd = array(
            'eval' => array(7,11,15,19),
            'maximum' => array(4,8,12,16),
            'down' => array(5,9,13,17),
            'wtr' => array(
                'maximum' => array(2,3,4,6,8,10,12,14,16,18,20),
            ),
        );
		$reachedMaximum = false;
		$taperDay = 1;
        $maximumCount = 0;

        if($maintenance == 1.00) {
            foreach ($progressions as $progression)
            {
                if ($progression['ProgressionType']['value'] === 'maximum')
                {
                    $maximumCount++;
                }
            }
        }

		for($trainingWeek = 1; $trainingWeek <= $trainingLength ; $trainingWeek++)
		{
			if ($trainingWeek == 1)
			{
                if($maintenance == 1.00) {
                    $ed[$trainingWeek] = $output;
                } else {
                    $ed[$trainingWeek] = round( ( $output * $maintenance ) / $sport['Sport']['round_at']) * $sport['Sport']['round_at'];
                }
			}
			else
			{
                $roundUp = ceil( ( $ed[$trainingWeek-1] * $modifier['Modifier']['up'] ) / $sport['Sport']['round_at']) * $sport['Sport']['round_at'];
                $roundDown = floor( ( $ed[$trainingWeek-1] * $modifier['Modifier']['down'] ) / $sport['Sport']['round_at']) * $sport['Sport']['round_at'];

                if($maintenance == 1.00) {
                    foreach ($progressions as $progression)
                    {
                        if($progression['Progression']['training_week'] == $trainingWeek)
                        {

                            switch ($progression['ProgressionType']['value'])
                            {
                                case 'up' :
                                    if ($roundUp >= $safeguard['Safeguard']['value'])
                                    {
                                        if($reachedMaximum)
                                        {
                                            $ed[$trainingWeek] = $ed[$trainingWeek-1];
                                        }
                                        else
                                        {
                                            $ed[$trainingWeek] = $ed[$trainingWeek-1];
                                            #$ed[$trainingWeek] = $roundDown;
                                        }
                                    }
                                    else
                                    {
                                        $ed[$trainingWeek] = $roundUp;
                                    }
                                    break;
                                case 'down' :
                                        $ed[$trainingWeek] = $roundDown;
                                    break;
                                case 'eval' :
                                        $ed[$trainingWeek] = $ed[$trainingWeek-1];
                                        if ($ed[$trainingWeek-1] >= $safeguard['Safeguard']['value'])
                                        {
                                            $ed[$trainingWeek] = $roundDown;
                                        }
                                        else
                                        {
                                            $ed[$trainingWeek] = $ed[$trainingWeek-1];
                                        }
                                    break;
                                case 'maximum' :
                                    $reachedmaximum = true;

                                    if ($roundUp >= $safeguard['Safeguard']['value'])
                                    {
                                        if ($safeguard['Sport']['value'] !== 'swim' && $safeguard['Race']['value'] === 'ironman' && $maximumCount > 1)
                                        {
                                            $ed[$trainingWeek] = $ed[$trainingWeek-1];
                                        }
                                        else
                                        {
                                            $ed[$trainingWeek] = $safeguard['Safeguard']['value'];
                                        }
                                        $maximumCount--;
                                    }
                                    else
                                    {
                                        $maximumCount--;
                                        $ed[$trainingWeek] = $roundUp;
                                    }
                                    break;
                                case 'longest' :
                                    $reachedMaximum = true;

                                    if ($roundUp >= $safeguard['Safeguard']['value'])
                                    {
                                        $ed[$trainingWeek] = $safeguard['Safeguard']['value'];
                                    }
                                    else
                                    {
                                        $ed[$trainingWeek] = $roundUp;
                                    }
                                    break;
                                case 'taper' :
                                    foreach($tapers as $taper)
                                    {
                                        if($taper['Modifier']['taper_day'] == $taperDay) {
                                            $ed[$trainingWeek] = round( ( $ed[$trainingWeek-1] / $taper['Modifier']['up'] ) / $sport['Sport']['round_at']) * $sport['Sport']['round_at'];
                                        }
                                    }
                                    $taperDay++;
                                    break;
                                case 'wtr' :
                                    $ed[$trainingWeek] = null;
                                    break;
                            }


                        }
                    }  //endforeach
                // maintenance
                } else {
                    if(in_array($trainingWeek, $maintenanceEd['down'])) {
                        $ed[$trainingWeek] = $roundDown;
                    } elseif(in_array($trainingWeek, $maintenanceEd['eval'])) {
                        if ($ed[$trainingWeek-1] >= $safeguard['Safeguard']['value']) {
                            #$ed[$trainingWeek] = $roundDown;
                            $ed[$trainingWeek] = $ed[$trainingWeek-1];
                        } else {
                            $ed[$trainingWeek] = $ed[$trainingWeek-1];
                        }
                    } elseif(in_array($trainingWeek, $maintenanceEd['maximum'])) {
                        if ($roundUp >= $safeguard['Safeguard']['value']) {
                            $ed[$trainingWeek] = $safeguard['Safeguard']['value'];
                        } else {
                            $ed[$trainingWeek] = $roundUp;
                        }
                    } elseif($trainingWeek == $trainingLength) {
                        if(in_array($trainingWeek, $maintenanceEd['wtr'])) {
                            if ($roundUp >= $safeguard['Safeguard']['value']) {
                                $ed[$trainingWeek] = $safeguard['Safeguard']['value'];
                            } else {
                                $ed[$trainingWeek] = $roundUp;
                            }
                        } else {
                            $ed[$trainingWeek] = $ed[$trainingWeek-1];
                        }
                    } else {
                        if ($ed[$trainingWeek - 1]  >= $safeguard['Safeguard']['value']) {
                            #$ed[$trainingWeek] = $ed[$trainingWeek-1];
                            $ed[$trainingWeek] = $roundDown;
                        } else {
                            $ed[$trainingWeek] = $roundUp;
                        }
                    }
                }
			}

		}

		return $ed;
	}

	public function progressionAD($trainingLength, $progression)
	{
		for ($week = 1;$week < $trainingLength ; $week++)
		{
		}
	}

	public function progressionAs($trainingLength)
	{
	}

	public function progressionRecovery($trainingLength)
	{
	}

	public function MROUND($number,$multiple) {
		if ((is_numeric($number)) && (is_numeric($multiple))) {
			if ($multiple == 0) {
				return 0;
			}
			if (($this->SIGNTest($number)) == ($this->SIGNTest($multiple))) {
				$multiplier = 1 / $multiple;
				return round($number * $multiplier) / $multiplier;
			}
			return 'NAN';
		}
		return 'NAN';
	}   //  function MROUND()

	public function SIGNTest($number) {
		if (is_bool($number))
			return (int) $number;
		if (is_numeric($number)) {
			if ($number == 0.0) {
				return 0;
			}
			return $number / abs($number);
		}
		return 'NAN';
	}   //  function SIGN()
	public function progressionPreview($peaks, $peakStart)
	{
		$progressions = array();
		for ( $week = $this->max;$week >= 1 ; $week-- )
		{
			array_push($progressions,$this->progression($week, $peaks, $peakStart));
		}
		return $progressions;
	}
	public function getWeeks($singular = 'week', $punctuation = false)
	{
		for($week = 1; $week <= $this->max; $week++)
		{
			if ($week == 1 && !$punctuation)
			{
				$weeks[$week] = $week.' '.__($singular);
			}
			elseif ($week == 1 && $punctuation)
			{
				$weeks[$week] = $week.'. '.__($singular);
			}
			elseif (!$punctuation)
			{
				$weeks[$week] = $week.' '.__($singular.'s');
			}
			else
			{
				$weeks[$week] = $week.'. '.__($singular);
			}
		}

		return $weeks;
	}
}

?>
