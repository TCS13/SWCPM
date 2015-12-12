<?php
    //Include standard header
    include('includes/header.php');
    
    //Update the database with various mats and planets, removing planets that
    //are suns, black holes or completely volcanic.
    echo "Number of mats found: ".\ws::getMats()."<br/>";
    echo "Number of planets found: ".\ws::getPlanets()."<br/>";
    echo "Updating planet info: ".\ws::updatePlanets()."<br/>";
    echo "Deactivating unpassable planets: ".\ws::deactivateUnprospectablePlanets()."<br/>";