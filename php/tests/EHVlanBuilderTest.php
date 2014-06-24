<?php
/**
 * User: matt
 * Date: 24/06/14
 * Time: 14:16
 */
require_once 'phpunit-bootstrap.php';

class EHVlanBuilderTest extends PHPUnit_Framework_TestCase {


    public function test_instance () {
        new EHVlanBuilder();
    }


    public function test_create () {
        $builder = new EHVlanBuilder();
        $output = $builder->create('bob');
        $this->assertEquals(['resources vlan create', ['name bob']], $output);
    }

    public function test_parse_response () {
        $guid = '003ced45-654b-4302-a30c-b8d7b1fce808';
        $response = [
            'name 2G',
            'resource ' . $guid,
            'type vlan',
            'user cccccccc-0000-cccc-8888-eeeeeeeeeee'
        ];
        $builder = new EHVlanBuilder();
        $found = $builder->parseResponse($response);

        $this->assertEquals($guid, $found);
    }
}
 