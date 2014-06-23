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
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $public_ip;



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
     * Gets the ElasticHosts assigned drive identifiers (guids)
     * @return array
     */
    public function getDriveIdentifiers () {

        $ret = [];
        foreach ($this->getDrives() as $drive) {
            $ret[] = $drive->getIdentifier();
        }
        return $ret;
    }


    /**
     * @return string
     */
    public function getName () {
        return $this->getConfigValue('name');
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
     * @param array $server_ids
     */
    public function avoidSharingHardwareWithServers (array $server_ids) {
        $prop = 'avoid:servers';
        $this->config->$prop = implode(' ', $server_ids);
    }

    /**
     * @param array $drive_ids
     */
    public function avoidSharingHardwareWithDrives (array $drive_ids) {
        $prop = 'avoid:drives';
        $this->config->$prop = implode(' ', $drive_ids);
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier ($identifier) {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier () {
        return $this->identifier;
    }

    /**
     * @param string $public_ip
     */
    public function setPublicIp ($public_ip) {
        $this->public_ip = $public_ip;
    }

    /**
     * @return string
     */
    public function getPublicIp () {
        return $this->public_ip;
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