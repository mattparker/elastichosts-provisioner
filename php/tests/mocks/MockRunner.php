<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 16:33
 */

class MockRunner implements Runner {

    private $responses = [];

    private $calls = [];

    private $guids = [];

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

            'drives image' => [],
            
            'servers create' => [
                'boot ide:0:0',
                'cpu 500',
                'ide:0:0 6052916e-102f-4db7-abdd-fd98f0d9f8d',
                'ide:0:0:read:bytes 0',
                'ide:0:0:read:requests 0',
                'ide:0:0:write:bytes 0',
                'ide:0:0:write:requests 0',
                'mem 256',
                'name testserver1',
                'nic:0:dhcp auto',
                'nic:0:dhcp:ip 91.203.56.132',
                'nic:0:model e1000',
                'server {guid}',
                'smp:cores 1',
                'started 1403554639',
                'status active',
                'user eeeeeee-1111-1111-ffff-6f6f6f6f6f6'
            ]
        );
    }


    /**
     * Returns something like what the API returns.
     *
     * @param string $command
     *
     * @param array  $args
     *
     * @return array
     */
    public function run ($command, array $args = array()) {

        $command = trim($command);
        $bits = explode(' ', $command);

        $firstBit = $bits[0] . ' ' . $bits[1];
        if ($bits[1] == '{guid}') {
            $firstBit = $bits[0] . ' ' . $bits[2];
        }


        if (!array_key_exists($firstBit, $this->calls)) {
            $this->calls[$firstBit] = [];
        }
        $this->calls[$firstBit][] = $args;

        $response = $this->responses[$firstBit];
        return $this->substituteGuid($firstBit, $command . implode(' ', $args), $response);

    }


    /**
     *
     * Will substitute the $guid in the response for a particular command for a given
     * name of resource (e.g. server name=>bob)
     *
     * @param $commandType
     * @param $name
     * @param $guid
     */
    public function setGuidFor ($commandType, $name, $guid) {
        $this->guids[$commandType][$name] = $guid;
    }


    /**
     * Looks to see if this is a 'named' request, and if there's a guid substitute set,
     * and if so does the substitution.
     *
     * @param string $commandType
     * @param string $command
     * @param array $response
     *
     * @return array
     */
    private function substituteGuid ($commandType, $command, array $response = array()) {

        $foundMatches = preg_match('/name ([a-zA-Z0-9]*)/', $command, $matchedNames);
        if ($foundMatches == 0) {
            return $response;
        }
        $name = $matchedNames[1];
        if (!array_key_exists($commandType, $this->guids) || !array_key_exists($name, $this->guids[$commandType])) {
            return $response;
        }

        $guid = $this->guids[$commandType][$name];

        foreach ($response as $lineNum => $line) {
            $response[$lineNum] = str_replace('{guid}', $guid, $line);
        }
        return $response;

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