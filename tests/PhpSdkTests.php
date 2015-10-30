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

    public function testGetRootLink() {
        //##BEGIN EXAMPLE accessingapi##
        $this->url = "https://test-api.nfleet.fi";
        $this->user = "clientkey";
        $this->pass = "clientsecret";
        $this->api = new Api($this->url, $this->user, $this->pass);
        $this->api->authenticate();
        $root = $this->api->getRoot();
        //##END EXAMPLE##
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

        //##BEGIN EXAMPLE creatingproblem##
        $problem = new stdClass();
        $problem->Name = "TestProblem";
        $response = $this->api->navigate(getLink($user, "create-problem"), $problem);
        //##END EXAMPLE##

        //##BEGIN EXAMPLE accessingnewproblem##
        $problem = $this->api->navigate($response);
        //##END EXAMPLE##
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
        //##BEGIN EXAMPLE creatingtask##
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

        $response = $this->api->navigate(getLink($problem, "create-task"), $task);
        //##END EXAMPLE##
        $t = $this->api->navigate($response);
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

    public function testListTasksAndUpdate() {
        $problem = $this->initWithProblem();
        $task1 = createTaskWithName("task1");
        $task2 = createTaskWithName("task2");
        $tasks = null;
        $task = null;
        try {
            $this->api->navigate(getLink($problem, "create-task"), $task1);
            $this->api->navigate(getLink($problem, "create-task"), $task2);

            //##BEGIN EXAMPLE listingtasks##
            $tasks = $this->api->navigate(getLink($problem, "list-tasks"));
            //##END EXAMPLE##

            //##BEGIN EXAMPLE updatingtask##
            $task = $this->api->navigate(getLink($tasks->Items[0], "self"));
            $task->Name = "updatedTask1";
            $this->api->navigate(getLink($task, "update"), $task);
            //##END EXAMPLE##
            $task = $this->api->navigate(getLink($task, "self"));
        } catch (NFleetException $ex) {
            var_dump($ex);
        }

        $this->assertEquals(2, count($tasks->Items));
        $this->assertEquals("updatedTask1", $task->Name);
    }

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
}