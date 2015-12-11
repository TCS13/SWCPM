<?php

    include('includes/header.php');
    
    echo "Number of mats found: ".\ws::getMats()."<br/>";
    echo "Number of planets found: ".\ws::getPlanets()."<br/>";
    echo "Updating planet info: ".\ws::updatePlanets()."<br/>";
    echo "Deactivating unpassable planets: ".\ws::deactivateUnprospectablePlanets()."<br/>";