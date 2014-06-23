<?php
/**
 * User: matt
 * Date: 20/06/14
 * Time: 16:22
 */

class EHServerBuilder {




    const CREATE = 1;



    private $properties = [
        'name',
        'cpu',
        'smp',
        'mem',
        'persistent',
        'boot',
        'nic:0:model',
        'nic:0:dhcp',
        'nic:0:firewall:policy',
        'nic:0:firewall:accept',
        'nic:0:firewall:reject',
        'nic:1:model',
        'nic:1:vlan',
        'nic:1:mac',
        'vnc',
        'password',
        'vnc:tls',
        'tags',
        'avoid:drives',
        'avoid:servers'
    ];


    /**
     * @param EHServer $server
     *
     * @return string
     * @throws Exception
     */
    public function create (EHServer $server) {

        $uri = 'servers create';

        // initial properties
        foreach ($this->properties as $prop) {
            $value = $server->getConfigValue($prop);
            if ($value !== null) {
                $uri .= ' ' . $prop . ' ' . $value;
            }
        }

        // now drives
        $drives = $server->getDrives();
        $driveCount = 0;
        foreach ($drives as $drive) {
            /** @var EHDrive $drive */
            // get sequentially increasing ide:0:0, ide:0:1, ide:1:0, ide:1:1
            $deviceId = 'ide:' . (int)(($driveCount & 2) > 0) . ':' . (int)(($driveCount & 1) > 0);
            $uri .= ' ' . $deviceId . ' ' . $drive->getIdentifier();
            $driveCount++;
        }
        if ($driveCount > 4) {
            throw new Exception("Don't think you can have more than four drives on one server through the API");
        }


        return $uri;

    }


    /**
     * @param EHServer $server
     * @param array    $response
     * @param          $action
     *
     * @throws InvalidArgumentException
     */
    public function parseResponse (EHServer $server, array $response = array(), $action) {

        if ($action !== self::CREATE) {
            throw new InvalidArgumentException("The only thing we know how to handle right now is creation");
        }

        $publicIp = $this->searchResponseArrayForLine($response, '/^nic:0:dhcp:ip ([0-9\.]*)$/');
        $serverId = $this->searchResponseArrayForLine($response, '/^server (.*)$/');
        $server->setPublicIp($publicIp);
        $server->setIdentifier($serverId);

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
                return $matches[1];
            }
        }
    }
} 