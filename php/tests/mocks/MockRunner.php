<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 16:33
 */

class MockRunner implements Runner {

    private $responses = [];

    private $calls = [];

    // Set up some dummy responses for testing:
    public function __construct () {

        $this->responses = array(

            'drives create' => [
                'drive {guid}',
                'encryption:cipher aes-xts-plain',
                'name madeupname',
                'read:bytes 4096',
                'read:requests 1',
                'size 12582912',
                'status active',
                'tier disk',
                'user eeeeeee-1111-1111-ffff-6f6f6f6f6f6',
                'write:bytes 4096',
                'write:requests 1'
            ],

            'drives info' => [
                'drive {guid}',
                'encryption:cipher aes-xts-plain',
                'imaging false',
                'name madeupname',
                'read:bytes 4096',
                'read:requests 1',
                'size 12582912',
                'status active',
                'tier disk',
                'user eeeeeee-1111-1111-ffff-6f6f6f6f6f6',
                'write:bytes 4096',
                'write:requests 1'
            ],

            'drives image' => []
        );
    }


    /**
     * Returns something like what the API returns.
     * @param string $command
     *
     * @return array
     */
    public function run ($command) {

        $command = trim($command);
        $bits = explode(' ', $command);

        $firstBit = $bits[0] . ' ' . $bits[1];
        if ($bits[1] == '{guid}') {
            $firstBit = $bits[0] . ' ' . $bits[2];
        }


        if (!array_key_exists($firstBit, $this->calls)) {
            $this->calls[$firstBit] = [];
        }
        $this->calls[$firstBit][] = $command;

        return $this->responses[$firstBit];

    }


    /**
     * All the calls made
     * @return array
     */
    public function getCalls() {
        return $this->calls;
    }


    /**
     * @param string $mainBit e.g. drives create
     *
     * @return array
     */
    public function getCall ($mainBit) {
        return $this->calls[$mainBit];
    }
}