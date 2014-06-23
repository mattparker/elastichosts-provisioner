<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:19
 */

class EHDrive implements Drive {




    /**
     * @var Object
     */
    private $config;

    /**
     * @var string
     */
    private $identifier;





    /**
     * @param $config Object
     */
    public function __construct ($config) {
        $this->config = $config;
    }




    /**
     * @return string
     */
    public function getName () {
        if (!property_exists($this->config, 'name')) {
            return '';
        }
        return $this->config->name;
    }




    /**
     * @return int
     */
    public function getSize () {
        if (!property_exists($this->config, 'size')) {
            return 0;
        }
        return $this->config->size;
    }


    /**
     * @return string
     */
    public function getImage () {
        if (!property_exists($this->config, 'image')) {
            return '';
        }
        return $this->config->image;
    }




    /**
     * The end-point generated ID for this drive.
     *
     * @param $id String
     */
    public function setIdentifier ($id) {
        $this->identifier = $id;
    }




    /**
     * The end-point generated ID for this drive
     *
     * @return String
     */
    public function getIdentifier () {
        return $this->identifier;
    }

}