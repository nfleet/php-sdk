<?php

function findMimeTypeUsingUrl( $url )
{
    $UserMime = "application/vnd.jyu.nfleet.user-2.1+json";
    $ProblemMime = "application/vnd.jyu.nfleet.problem-2.0+json";
    $ProblemSetMime = "application/vnd.jyu.nfleet.problemset-2.0+json";
    $ProblemSettingsMime = "application/vnd.jyu.nfleet.problemsettings-2.1+json";
    $VehicleSetMime = "application/vnd.jyu.nfleet.vehicleset-2.1+json";
    $VehicleMime = "application/vnd.jyu.nfleet.vehicle-2.1+json";
    $TaskSetMime = "application/vnd.jyu.nfleet.taskset-2.1+json";
    $TaskMime = "application/vnd.jyu.nfleet.task-2.1+json";
    $PlanMime = "application/vnd.jyu.nfleet.plan-2.0+json";
    $ImportMime = "application/vnd.jyu.nfleet.import-2.2+json";
    $RouteMime = "application/vnd.jyu.nfleet.route-2.0+json";
    $RouteEventMime = "application/vnd.jyu.nfleet.routeevent-2.0+json";
    $RouteEventSetMime = "application/vnd.jyu.nfleet.routeeventset-2.0+json";
    $DepotMime = "application/vnd.jyu.nfleet.depot-2.2+json";
    $DepotSetMime = "application/vnd.jyu.nfleet.depotset-2.2+json";

    if ( strlen( $url ) == 0 ) return "application/json";

    if ( endsWith( $url, "/settings" ) ) return $ProblemSettingsMime;
    if ( contains( $url, "plan" ) ) return $PlanMime;
    if ( endsWith( $url, "/route" ) ) return $RouteMime;
    if ( endsWith( $url, "/events" ) || endsWith( $url, "/events/" ) ) return $RouteEventSetMime;
    if ( contains( $url, "/events/" ) ) return $RouteEventMime;
    if ( endsWith( $url, "tasks" ) ) return $TaskSetMime;
    if ( contains( $url, "tasks/" ) ) return $TaskMime;
    if ( endsWith( $url, "vehicles" ) ) return $VehicleSetMime;
    if ( contains( $url, "vehicles/" ) ) return $VehicleMime;
    if ( endsWith( $url, "depots" ) ) return $DepotSetMime;
    if ( contains( $url, "depots/" ) ) return $DepotMime;
    if ( endsWith( $url, "imports" ) ) return $ImportMime;
    if ( contains( $url, "imports/" ) ) return $ImportMime;
    if ( endsWith( $url, "problems" ) ) return $ProblemSetMime;
    if ( contains( $url, "problems/" ) ) return $ProblemMime;


    return "application/json";
}

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function contains($string, $word) {
    return strpos($string, $word) !== false;
}