<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:19
 */

class EHServer implements Server {


    private $config;


    private $drives = null;


    public function __construct ($config) {
        $this->config = $config;
    }



    public function getDrives () {

        if ($this->drives === null) {
            $this->prepareDrives();
        }
        return $this->drives;

    }

    private function prepareDrives () {

        if (!property_exists($this->config, 'drives')) {
            throw new LogicException("A server needs at least one drive: none specified");
        }

        $driveInfo = $this->config->drives;

        foreach ($driveInfo as $drive) {
            $this->drives[] = new EHDrive($drive);
        }
    }

} 