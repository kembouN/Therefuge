<?php

use Illuminate\Support\Carbon;
use Nette\Utils\Random;
use Illuminate\Support\Str;

function generateCode($code){
    $num = Str::substr(date('Y'), -2, 2).''.Random::generate($code, '0-9A-Z');
    return 'log-'.$num;

}

function codeReaction($taille){
    return 'com-'.Random::generate($taille, 'A-Z0-9');
}

function fincontrat($duree, $debut){
    $dateFin = Carbon::parse($debut);
    return $dateFin->addMonths($duree);
}

function age($datenaissance){
    $age = Carbon::parse($datenaissance)->age;
    return $age;
}