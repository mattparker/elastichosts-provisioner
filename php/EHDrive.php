<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:19
 */

class EHDrive implements Drive {

    private $config;

    public function __construct ($config) {
        $this->config = $config;
    }

} 