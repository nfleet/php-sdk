<?php

function getLink($obj, $rel = 'self') {
    if (is_null($obj)) {
        throw new Exception("Argument is null.");
    }

    if (!is_object($obj)) {
        throw new Exception("Argument is not an object.");
    }

    if (!property_exists($obj, 'Meta')) {
        throw new Exception("Object does not contain Meta.");
    }

    $meta = $obj->Meta;
    $self = null;

    foreach($meta as $m) {
        if($m->Rel === "self") {
            $self = $m;
            break;
        }
    }
    unset($m);

    if ($rel === 'self') {
        if (is_object($obj) && property_exists($obj,'VersionNumber')) {
            $self->VersionNumber = $obj->VersionNumber;
        }
        return $self;
    }

    $op = null;

    foreach($meta as $m2) {
        if($m2->Rel === $rel) {
            $op = $m2;
            break;
        }
    }
    unset($m2);
    
    $newUri = $self->Uri.$op->Uri;
    $link = new stdClass();
    $link->Method = $op->Method;
    $link->Rel = $op->Rel;
    $link->Uri = $newUri;
    $link->Type = $op->Type;
    if (is_object($obj) && property_exists($obj,'VersionNumber')) {
        $link->VersionNumber = $obj->VersionNumber;
    }

    return $link;
}

function createVehicle($name, $lat, $lon) {
    $location = new stdClass();
    $location->Coordinate = new stdClass();
    $location->Coordinate->Latitude = $lat;
    $location->Coordinate->Longitude = $lon;
    $location->Coordinate->System = "WGS84";

    $vehicle = new stdClass();
    $vehicle->Name = $name;
    $vehicle->StartLocation = $location;
    $vehicle->EndLocation = $location;
    $vehicle->RelocationType = "None";

    $vehicle->TimeWindows = createTimeWindow(24);
    $vehicle->Capacities = array(array("Amount"=>100, "Name"=>"Weight"));

    return $vehicle;
}

function createTask($name, $startLat, $startLon, $endLat, $endLon) {

    $startLocation = new stdClass();
    $startLocation->Coordinate = new stdClass();
    $startLocation->Coordinate->Latitude = $startLat;
    $startLocation->Coordinate->Longitude = $startLon;
    $startLocation->Coordinate->System = "WGS84";

    $endLocation = new stdClass();
    $endLocation->Coordinate = new stdClass();
    $endLocation->Coordinate->Latitude = $endLat;
    $endLocation->Coordinate->Longitude = $endLon;
    $endLocation->Coordinate->System = "WGS84";

    $pickup = new stdClass();
    $pickup->Location = $startLocation;
    $pickup->TimeWindow = createTimeWindow(24);
    $pickup->Capacities = array(array("Amount"=>1, "Name"=>"Weight"));
    $pickup->Type = "Pickup";

    $delivery = new stdClass();
    $delivery->Location = $endLocation;
    $delivery->TimeWindow = createTimeWindow(24);
    $delivery->Capacities = array(array("Amount"=>1, "Name"=>"Weight"));
    $delivery->Type = "Delivery";

    $task = new stdClass();
    $task->Name = $name;
    $task->Info = $name;
    $task->RelocationType = "None";

    $task->TaskEvents = array($pickup, $delivery);
    return $task;
}

function createTimeWindow($duration) {
    $timeWindow = new stdClass();
    $now = new DateTime();
    $timeWindow->Start = $now->format('Y-m-d H:i:s');;
    $end = date_add($now, date_interval_create_from_date_string($duration.' hours'));
    $timeWindow->End = $end->format('Y-m-d H:i:s');

    return $timeWindow;
}

function pvar_dump($v) {
    echo '<pre>';
    var_dump($v);
    echo '</pre>';
}