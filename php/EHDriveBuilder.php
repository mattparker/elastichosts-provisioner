<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:24
 */


/**
 * Class EHDriveBuilder
 *
 * Constructs command line instructions to create drives using the ElasticHosts API
 * and command line tool
 *
 */
class EHDriveBuilder {

    /**
     * Elastic hosts drive images we can use
     */
    const CENTOS_65        = '8d5c93b8-e4e4-4943-b41e-873576b7fcd1';
    const DEBIAN_74        = 'ce85ef47-9794-4ed7-a8bd-af902ec0eddc';
    const UBUNTU_1204      = '62f512cd-82c7-498e-88d8-a09ac2ef20e7';
    const UBUNTU_1310      = '4f31382b-5098-4610-8993-bbebb844febd';
    const WIN_WEB_2008     = '11b84345-7169-4279-8038-18d6ba1a7712';
    const WIN_WEB_2008_SQL = 'b23e81b9-103e-4f9d-8ce5-b57bb529007c';
    const WIN_2008         = '6c0c3072-f55f-4dd2-9308-951dacf41ce3';
    const WIN_2008_SQL     = '63677762-4423-464f-92fd-5c43d449a716';
    const WIN_2012         = 'cdea53be-2511-4c91-9779-f6421f623a49';
    const WIN_2012_SQL     = '7b9807cc-3c92-425f-878c-1d45927f3f9c';


    /**
     * What we can do
     */
    const CREATE = 1;
    const IS_IMAGING_COMPLETE = 2;



    /**
     * @param EHDrive $drive
     * @param array   $avoidingDrives
     *
     * @return array  First is command (ie drives create), second is params
     * @throws LogicException
     */
    public function create (EHDrive $drive, array $avoidingDrives = null) {

        $uri = ' drives create';
        $args = array();

        $name = $drive->getName();
        $size = $drive->getSize();

        if ($name) {
            $args[] = 'name ' . $name;
        }
        if (!$size) {
            throw new LogicException("A drive needs a size");
        }
        $args[] = 'size ' . $size;

        $args[] = 'tier ' . $drive->getTier();


        if ($avoidingDrives) {
            $args[] = 'avoid ' . implode(' ', $avoidingDrives);
        }

        return [$uri, $args];
    }




    /**
     *
     * Create a drive from an existing image
     *
     * @param EHDrive $drive
     * @param string  $imageName   Use a constant, e.g. EHDriveBuilder::DEBIAN_74
     *
     * @return string
     * @throws LogicException
     */
    public function image (EHDrive $drive, $imageName) {

        $id = $drive->getIdentifier();
        if (!$id) {
            throw new LogicException("The drive needs to be created and have and ID before imaging");
        }
        return [' drives ' . $id . ' image ' . $imageName . ' gunzip', []];

    }


    /**
     * Get information about a particular drive
     *
     * @param EHDrive $drive
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function info (EHDrive $drive) {
        $id = $drive->getIdentifier();
        if (!$id) {
            throw new InvalidArgumentException("Cannot get info from a drive that does not have an ID");
        }
        return [' drives ' . $id . ' info', []];
    }


    /**
     * @param EHDrive $drive
     * @param array   $response
     * @param         $action
     *
     * @return mixed
     */
    public function parseResponse (EHDrive $drive, array $response, $action) {
        switch ($action) {
            case EHDriveBuilder::CREATE:
                return $this->parseResponseCreate($drive, $response);
                break;
            case EHDriveBuilder::IS_IMAGING_COMPLETE:
                return $this->parseResponseForImagingComplete($response);
                break;
        }
        return null;
    }

    /**
     * @param EHDrive $drive
     * @param array   $response
     *
     * @return mixed
     */
    private function parseResponseCreate (EHDrive $drive, array $response) {
        $driveIdentifier = $this->searchResponseArrayForLine($response, '/^drive (.*)$/');
        $drive->setIdentifier($driveIdentifier);
        return $driveIdentifier;

    }

    /**
     * @param array $response
     *
     * @return string
     */
    private function parseResponseForImagingComplete (array $response) {
        $searchLine = '/^imaging (.*)$/';
        $foundImaging = $this->searchResponseArrayForLine($response, $searchLine);
        if ($foundImaging) {
            return $foundImaging;
        }
        $searchForActive = '/^status (active)/';
        $foundActive = $this->searchResponseArrayForLine($response, $searchForActive);
        if ($foundActive) {
            // it's active, so not imaging:
            return 'false';
        }
        return '';
    }

    /**
     * @param array $response
     * @param string $searchLine  Regexp to look for, with one parameterised subexpression
     *
     * @return mixed
     */
    private function searchResponseArrayForLine (array $response, $searchLine) {
        $matches = [];
        foreach ($response as $imagingLine) {
            if (preg_match($searchLine, $imagingLine, $matches)) {
                if (is_array($matches) && array_key_exists(1, $matches)) {
                    return $matches[1];
                }
            }
        }
        return false;
    }
} 