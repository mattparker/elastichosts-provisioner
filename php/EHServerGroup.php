<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:18
 */

class EHServerGroup implements ServerGroup {

    private $name;
    private $servers = [];

    public function __construct ($name) {
        $this->name = $name;
    }

    public function addServer (Server $server) {
        $this->servers[] = $server;
    }



} 