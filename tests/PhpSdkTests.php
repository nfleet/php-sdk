<?php
include "../src/Api.php";
include "../src/Util.php";
include "TestHelper.php";
use NFleet\Api;


class PhpSdkTests extends  PHPUnit_Framework_TestCase {

    private $api;
    private $url;
    private $user;
    private $pass;

    public function init()  {
        $this->url = "https://test-api.nfleet.fi";
        $this->user = "clientkey";
        $this->pass = "clientsecret";
        $this->api = new Api($this->url, $this->user, $this->pass);
        $auth = $this->api->authenticate();
    }

    public function initWithUser() {
        $this->init();
        $user = null;
        $root = $this->api->getRoot();

        $resp = $this->api->navigate(getLink($root, "create-user"));
        $user = $this->api->navigate($resp);
        return $user;
    }

    public function initWithProblem() {
        $this->init();
        $user = null;
        $root = $this->api->getRoot();

        $resp = $this->api->navigate(getLink($root, "create-user"));
        $user = $this->api->navigate($resp);

        $update = new stdClass();

        $update->Name = "TestProblem";

        $resp = $this->api->navigate(getLink($user, "create-problem"), $update);
        $problem = $this->api->navigate($resp);

        return $problem;
    }

    public function initWithDemoCase() {
        $problem = $this->initWithProblem();

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

        $vehicle = createVehicleWithName("vehicle1");
        $task = createTaskWithName("Task1");

        try {
            $this->api->navigate(getLink($problem, "create-vehicle"), $vehicle);
            $this->api->navigate(getLink($problem, "create-task"), $task);
        } catch (NFleetException $e) {
            var_dump($e);
        }

        return $problem;
    }

    public function testGetRootLink() {
        $this->init();
        $root = $this->api->getRoot();
        $this->assertNotNull($root);
        unset($api);
    }

    public function testCreateUser() {
        $this->init();
        $root = $this->api->getRoot();

        $resp = $this->api->navigate(getLink($root, "create-user"));
        $user = $this->api->navigate($resp);

        $this->assertNotNull($user);
        unset($api);
    }

    public function testCreateProblem() {
        $user = $this->initWithUser();
        $update = new stdClass();

        $update->Name = "TestProblem";

        $resp = $this->api->navigate(getLink($user, "create-problem"), $update);
        $problem = $this->api->navigate($resp);

        $this->assertNotNull($problem);
        unset($api);
    }

    public function testCreateVehicle() {
        $problem = $this->initWithProblem();

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
        $vehicle->Name = "Vehicle1";
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

        $resp = $this->api->navigate(getLink($problem, "create-vehicle"), $vehicle);

        $v = $this->api->navigate($resp);

        $this->assertEquals("Vehicle1", $v->Name);
        unset($api);
    }

    public function testCreateTask() {
        $problem = $this->initWithProblem();

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

        $resp = $this->api->navigate(getLink($problem, "create-task"), $task);
        $t = $this->api->navigate($resp);
        $this->assertEquals("ExampleTask", $t->Name);
    }

    public function testInvalidRoutingProblemCreation() {
        $user = $this->initWithUser();
        $exception = null;
        try {
            $resp = $this->api->navigate(getLink($user, "create-problem"));
        } catch (NFleetException $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception);
        $this->assertEquals(3401, $exception->Items[0]->Code);
    }

    public function testInvalidTaskCreationWithMultipleErrors() {
        $problem = $this->initWithProblem();

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
        $delivery->Capacities = array(array("Amount"=>1, "Name"=>"W"));
        $delivery->Type = "Delivery";

        $task = new stdClass();
        $task->RelocationType = "None";

        $task->TaskEvents = array($pickup, $delivery);
        $exception = null;

        try {
            $resp = $this->api->navigate(getLink($problem, "create-task"), $task);
        } catch (NFleetException $e) {
            $exception = $e;
        }

        echo $exception;
        $this->assertNotNull($exception);
        $this->assertEquals(2, count($exception->Items));
    }

    public function testListingTasks() {
        $problem = $this->initWithDemoCase();
        $tasks = null;
        try {
            $tasks = $this->api->navigate(getLink($problem, "list-tasks"));
        } catch (NFleetException $e) {
            var_dump($e);
            $this->assertFail();
        }
        $this->assertNotNull($tasks);
        $this->assertEquals(1, count($tasks->Items));
    }

    public function testCreatingDepot() {
        $problem = $this->initWithProblem();

        $created = null;
        $depot = new stdClass();

        $depot->Name = "depot";
        $location= new stdClass();
        $location->Coordinate = new stdClass();
        $location->Coordinate->Latitude = "62.270538";
        $location->Coordinate->Longitude = "26.057074";
        $location->Coordinate->System = "WGS84";

        $depot->Capacities = array(array("Amount"=>1000, "Name"=>"Weight"));
        $depot->Location = $location;

        try {
            $res = $this->api->navigate(getLink($problem, "create-depot"), $depot);
            $created = $this->api->navigate($res);
        } catch (NFleetException $e) {
            echo $e;
            $this->assertFail();
        }

        $this->assertNotNull($created);
        $this->assertEquals("depot", $created->Name);
    }
}