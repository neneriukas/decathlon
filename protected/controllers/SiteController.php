<?php

class SiteController extends Controller
{

	public function actionIndex()
	{
		$model = new UploadForm();

		if(isset($_POST['UploadForm']))
		{
			$csvFile = CUploadedFile::getInstance($model,'csv');
			if($csvFile !== null)
			{
				$tempLocation=$csvFile->getTempName();

				$contestants = $this->getFileData($tempLocation);
				$contestants = $this->nameKeysForContestants($contestants);
				$contestants = $this->calculateSpecialTotal($contestants);
				$contestants = $this->assignPlace($contestants);

				$this->generateXML($contestants);
				exit();
			}
		}

		$this->render('index', array('model'=>$model));
	}

	public function getFileData($tempLocation){

		$fileData = file($tempLocation);

		if($fileData !== null)
		{
			foreach($fileData as $row)
			{
				$contestants[] = str_getcsv($row, ';');
			}

			return $contestants;
		}

	}

	public function nameKeysForContestants($contestants){

			$contestantsWithKeys = array();

			foreach($contestants as $key => $contestant)
			{
				$contestantsWithKeys[$key]['name'] = $contestant[0];
				$contestantsWithKeys[$key]['ST'] = '';
				$contestantsWithKeys[$key]['place'] = '';
				$contestantsWithKeys[$key]['100m'] = $contestant[1];
				$contestantsWithKeys[$key]['LJ'] = $contestant[2];
				$contestantsWithKeys[$key]['SP'] = $contestant[3];
				$contestantsWithKeys[$key]['HJ'] = $contestant[4];
				$contestantsWithKeys[$key]['400m'] = $contestant[5];
				$contestantsWithKeys[$key]['110mH'] = $contestant[6];
				$contestantsWithKeys[$key]['DT'] = $contestant[7];
				$contestantsWithKeys[$key]['PV'] = $contestant[8];
				$contestantsWithKeys[$key]['JT'] = $contestant[9];
				$contestantsWithKeys[$key]['1500m'] = $contestant[10];
			}

			unset($contestants);
			return $contestantsWithKeys;
	}

	public function calculateSpecialTotal($contestants){
		//***************************************************************
		//Special Total (ST) is calculated by this formula:
		//ST = LJ×HJ×PV×JT×DT×SP / T(100m)×T(400m)×T(110mH)×T(1500m)
		//
		//Best ever decathlon performances:
		//  Šebrle (9026 pts):  ST = 2.29
		//  Dvořák (8994 pts): ST = 2.40
		//
		//source: https://nrich.maths.org/8346
		//***************************************************************

		if($contestants !== null)
		{

			foreach($contestants as $key => $value)
			{
				$contestants[$key]['ST'] = ($contestants[$key]['LJ'] * $contestants[$key]['HJ'] * $contestants[$key]['PV'] * $contestants[$key]['JT'] * $contestants[$key]['DT'] * $contestants[$key]['SP']) / ($contestants[$key]['100m'] * $contestants[$key]['400m'] * $contestants[$key]['110mH'] * $contestants[$key]['1500m'] );
				$contestants[$key]['ST'] = round($contestants[$key]['ST'], 2);
			}

			return $contestants;
		}
	}

	public function assignPlace($contestants){

		function sortByST($a, $b)
		{
			if($a['ST'] == $b['ST'])
			{
				return 0;
			}
			return ($a['ST'] > $b['ST']) ? -1 : 1;
		}

		usort($contestants, 'sortByST');

		for($i = 0; $i < count($contestants); $i++)
		{
			$contestants[$i]['place'] = $i + 1;
		}

		return $contestants;

	}

	public function generateXML($contestants){

		$xml = new SimpleXMLElement("<results></results>");
		Header('Content-type: text/xml');

		foreach($contestants as $contestant){
			$row = $xml->addChild('contestant');
			 $row->addChild('name', $contestant['name']);
			 $row->addChild('ST', $contestant['ST']);
			 $row->addChild('place', $contestant['place']);
			 $row->addChild('m100', $contestant['100m']);
			 $row->addChild('LJ', $contestant['LJ']);
			 $row->addChild('SP', $contestant['SP']);
			 $row->addChild('HJ', $contestant['HJ']);
			 $row->addChild('m400', $contestant['400m']);
			 $row->addChild('mH110', $contestant['110mH']);
			 $row->addChild('DT', $contestant['DT']);
			 $row->addChild('PV', $contestant['PV']);
			 $row->addChild('JT', $contestant['JT']);
			 $row->addChild('m1500', $contestant['1500m']);
		}

		echo $xml->asXML();
		unset($contestants);
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

}