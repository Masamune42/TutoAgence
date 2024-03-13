<?php

namespace App;


use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class Weather
{
    // Partie test avec Laravel
    // Aproche 2 : Utilisation du constructeur avec le Repository
//    public function __construct(private Repository $cache)
//    {
//    }

    public function isSunnyTomorrow()
    {
        // Partie test avec Laravel
        // Approche 1 : utilisation de Facade dans les services
//        $result = Cache::get('weather');
        // Aproche 2 : Utilisation du constructeur avec le Repository
//        $result = $this->cache->get('weather');
//        if($result !== null) {
//            return $result;
//        }
//        ...
        return true;
    }
}
