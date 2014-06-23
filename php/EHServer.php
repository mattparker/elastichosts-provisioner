<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:19
 */

class EHServer implements Server {


    /**
     * @var object
     */
    private $config;


    /**
     * @var null | array
     */
    private $drives = null;


    /**
     * @param array|object $config Properties for server - see EH docs
     */
    public function __construct ($config) {
        $this->config = (object)$config;
    }


    /**
     * @return EHDrive[]
     */
    public function getDrives () {

        if ($this->drives === null) {
            $this->prepareDrives();
        }
        return $this->drives;

    }


    /**
     * @param $prop string
     *
     * @return mixed
     */
    public function getConfigValue ($prop) {
        if (property_exists($this->config, $prop)) {
            return $this->config->{$prop};
        }
        return null;
    }


    /**
     * Sets up EHDrive instances for the server
     *
     * @throws LogicException
     */
    private function prepareDrives () {

        if (!property_exists($this->config, 'drives')) {
            throw new LogicException("A server needs at least one drive: none specified");
        }

        $driveInfo = $this->config->drives;

        foreach ($driveInfo as $drive) {
            $this->drives[] = new EHDrive((object)$drive);
        }
    }

} 