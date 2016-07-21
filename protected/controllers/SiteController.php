<?php

class SiteController extends Controller
{

    public function actionIndex()
    {
        $model = new UploadForm();

        if (isset($_POST['UploadForm'])) {
            $csvFile = CUploadedFile::getInstance($model, 'csv');
            if ($csvFile !== null) {
                $tempLocation=$csvFile->getTempName();

                $contestants = $this->getFileData($tempLocation);
                $contestants = $this->nameKeysForContestants($contestants);
                $contestants = $this->calculateScore($contestants);
                $contestants = $this->assignPlace($contestants);

                $this->generateXML($contestants);
                exit();
            }
        }

        $this->render('index', array('model'=>$model));
    }

    public function getFileData($tempLocation)
    {
        $fileData = file($tempLocation);

        if ($fileData !== null) {
            foreach ($fileData as $row) {
                $contestants[] = str_getcsv($row, ';');
            }

            return $contestants;
        }
    }

    public function nameKeysForContestants($contestants)
    {
        $contestantsWithKeys = array();

        foreach ($contestants as $key => $contestant) {
            $contestantsWithKeys[$key]['name'] = $contestant[0];
            $contestantsWithKeys[$key]['score'] = '';
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

    public function calculateScore($contestants)
    {
        $scores = new Scores;
        foreach ($contestants as $key => $value) {
            $trackScore = $scores->calculateTrackEvents($contestants[$key]['100m'], $contestants[$key]['400m'], $contestants[$key]['110mH'], $contestants[$key]['1500m']);
            $fieldScore = $scores->calculateFieldEvents($contestants[$key]['LJ'], $contestants[$key]['SP'], $contestants[$key]['HJ'], $contestants[$key]['DT'], $contestants[$key]['PV'], $contestants[$key]['JT']);

            $contestants[$key]['score'] = $trackScore + $fieldScore;
        }
        return $contestants;
    }

    public function assignPlace($contestants)
    {

        function sortByScore($a, $b)
        {
            if ($a['score'] == $b['score']) {
                return 0;
            }
            return ($a['score'] > $b['score']) ? -1 : 1;
        }

        usort($contestants, 'sortByScore');

        $place = 1;

        foreach ($contestants as $key => $value) {
            if ($contestants[$key]['place'] !== "") {
                $place++;
                continue;
            }

            if (isset($contestants[$key+1]) && $contestants[$key]['score'] == $contestants[$key+1]['score']) {
                $contestants[$key]['place'] = strval($place) . strval('/') . strval($place+1);
                $contestants[$key+1]['place'] = $contestants[$key]['place'];
            } else {
                $contestants[$key]['place'] = $place;
            }
            
            $place++;
        }

        return $contestants;
    }

    public function generateXML($contestants)
    {

        $xml = new SimpleXMLElement("<results></results>");
        Header('Content-type: text/xml');

        foreach ($contestants as $contestant) {
            $row = $xml->addChild('contestant');
             $row->addChild('name', $contestant['name']);
             $row->addChild('score', $contestant['score']);
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
        if ($error=Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest) {
                echo $error['message'];
            } else {
                $this->render('error', $error);
            }
        }
    }
}
