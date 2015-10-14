<?php
function createVehicleWithName($name) {
        $vehicle = new stdClass();

        $startlocation = new stdClass();
        $startlocation->Coordinate = new stdClass();
        $startlocation->Coordinate->Latitude = "62.254622";
        $startlocation->Coordinate->Longitude = "25.787020";
        $startlocation->Coordinate->System = "WGS84";

        $endlocation = new stdClass();
        $endlocation->Coordinate = new stdClass();
        $endlocation->Coordinate->Latitude = "62.254622";
        $endlocation->Coordinate->Longitude = "25.787020";
        $endlocation->Coordinate->System = "WGS84";


        $vehicle = new stdClass();
        $vehicle->Name = $name;
        $vehicle->StartLocation = $startlocation;
        $vehicle->EndLocation = $endlocation;
        $vehicle->RelocationType = "None";

        $timeWindow = new stdClass();
        $now = new DateTime();
        $timeWindow->Start = $now->format('Y-m-d H:i:s');;
        $end = date_add($now, date_interval_create_from_date_string('12 hours'));
        $timeWindow->End = $end->format('Y-m-d H:i:s');

        $vehicle->TimeWindows = $timeWindow;

        $vehicle->Capacities = array(array("Amount"=>100, "Name"=>"Weight"));

        return $vehicle;
    }

   function createTaskWithName($name) {
        $taskpickup = new stdClass();
        $taskpickup->Coordinate = new stdClass();
        $taskpickup->Coordinate->Latitude = "62.254622";
        $taskpickup->Coordinate->Longitude = "25.787020";
        $taskpickup->Coordinate->System = "WGS84";

        $taskdelivery = new stdClass();
        $taskdelivery->Coordinate = new stdClass();
        $taskdelivery->Coordinate->Latitude = "62.270538";
        $taskdelivery->Coordinate->Longitude = "26.057074";
        $taskdelivery->Coordinate->System = "WGS84";

        $pickup = new stdClass();
        $pickup->Location = $taskpickup;
        $pickup->TimeWindow = createTimeWindowWithDuration("12");
        $pickup->Capacities = array(array("Amount"=>1, "Name"=>"Weight"));
        $pickup->Type = "Pickup";

        $delivery = new stdClass();
        $delivery->Location = $taskdelivery;
        $delivery->TimeWindow = createTimeWindowWithDuration("12");
        $delivery->Capacities = array(array("Amount"=>1, "Name"=>"Weight"));
        $delivery->Type = "Delivery";


        $task = new stdClass();
        $task->Name = "ExampleTask";
        $task->RelocationType = "None";

        $task->TaskEvents = array($pickup, $delivery);

    }

   function createTimeWindowWithDuration($duration) {
        $timeWindow = new stdClass();
        $now = new DateTime();
        $timeWindow->Start = $now->format('Y-m-d H:i:s');;
        $end = date_add($now, date_interval_create_from_date_string($duration.' hours'));
        $timeWindow->End = $end->format('Y-m-d H:i:s');
        return $timeWindow;
    }
