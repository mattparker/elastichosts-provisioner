<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:22
 */

class EHServerBuilder {


    private $driveBuilder;



    public function __construct(DriveBuilder $driveBuilder) {
        $this->driveBuilder = $driveBuilder;
    }



    public function build (Server $server) {

        // first make drives

        // then make servers
    }
} 