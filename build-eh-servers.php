#!/usr/bin/php
<?php


echo "\n\n This is a script to build some servers on elastichosts \n\n ";
echo "\n And then output an ansible inventory file\n\n ";

spl_autoload_register(function ($class) {
    include 'php/' . $class . '.php';
});


require_once 'php/set-eh-credentials-test.php';



$server_definition = file_get_contents('server-inventory.json');
$json_server_definition = json_decode($server_definition, true);

$allGroups = [];



$ehServerBuilder = new EHServerBuilder();
$ehDriveBuilder = new EHDriveBuilder();
$ehBuilder = new EHBuilder($ehServerBuilder, $ehDriveBuilder);



foreach ((array)$json_server_definition['servers'] as $serverGroupName => $serverInfo) {

    $group = new EHServerGroup($serverGroupName);
    foreach ($serverInfo as $server) {

        $server = new EHServer($server);

        $ehBuilder->build($server);

        $group->addServer($server);
        $allGroups[] = $group;
    }

}





