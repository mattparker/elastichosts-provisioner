<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:18
 */

class EHServerGroup implements ServerGroup {

    /**
     * @var string
     */
    private $name;


    /**
     * @var EHServer[]
     */
    private $servers = [];


    /**
     * @param string $name
     */
    public function __construct ($name) {
        $this->name = $name;
    }


    /**
     * @param Server $server
     */
    public function addServer (Server $server) {
        $this->servers[] = $server;
    }

    /**
     * @return string
     */
    public function getName () {
        return $this->name;
    }

    /**
     * @return EHServer[]
     */
    public function getServers () {
        return $this->servers;
    }



} 