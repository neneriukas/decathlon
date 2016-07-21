<?php

class Scores
{
    const A_100M = 25.4347;
    const A_LJ = 0.14354;
    const A_SP = 51.39;
    const A_HJ = 0.8465;
    const A_400M = 1.53775;
    const A_110MH = 5.74352;
    const A_DT = 12.91;
    const A_PV = 0.2797;
    const A_JT = 10.14;
    const A_1500M = 0.03768;

    const B_100M = 18;
    const B_LJ = 220;
    const B_SP = 1.5;
    const B_HJ = 75;
    const B_400M = 82;
    const B_110MH = 28.5;
    const B_DT = 4;
    const B_PV = 100;
    const B_JT = 7;
    const B_1500M = 480;

    const C_100M = 1.81;
    const C_LJ = 1.4;
    const C_SP = 1.05;
    const C_HJ = 1.42;
    const C_400M = 1.81;
    const C_110MH = 1.92;
    const C_DT = 1.1;
    const C_PV = 1.35;
    const C_JT = 1.08;
    const C_1500M = 1.85;

    public static function calculateTrackEvents($m100, $m400, $mH110, $m1500)
    {
        //Points = INT(A(B — P)C) for track events (faster time produces a better score)
        $trackScore = pow((self::A_100M*abs((self::B_100M - $m100))), self::C_100M);
        $trackScore += pow((self::A_400M*abs((self::B_400M - $m400))), self::C_400M);
        $trackScore += pow((self::A_110MH*abs((self::B_110MH - $mH110))), self::C_110MH);
        $trackScore += pow((self::A_1500M*abs((self::B_1500M - $m1500))), self::C_1500M);

        return $trackScore;
    }

    public static function calculateFieldEvents($LJ, $SP, $HJ, $DT, $PV, $JT)
    {
        //Points = INT(A(P — B)C) for field events (greater distance or height produces a better score)
        $fieldScore = pow((self::A_LJ*abs(($LJ - self::B_LJ))), self::C_LJ);
        $fieldScore += pow((self::A_SP*abs(($SP - self::B_SP))), self::C_SP);
        $fieldScore += pow((self::A_HJ*abs(($HJ - self::B_HJ))), self::C_HJ);
        $fieldScore += pow((self::A_DT*abs(($DT - self::B_DT))), self::C_DT);
        $fieldScore += pow((self::A_PV*abs(($PV - self::B_PV))), self::C_PV);
        $fieldScore += pow((self::A_JT*abs(($JT - self::B_JT))), self::C_JT);

        return $fieldScore;
    }
}
