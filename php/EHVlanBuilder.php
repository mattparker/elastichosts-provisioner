<?php
/**
 * User: matt
 * Date: 24/06/14
 * Time: 14:07
 */

class EHVlanBuilder {


    /**
     * @param string $name
     *
     * @return array
     */
    public function create ($name = '') {

        $uri = 'resources vlan create';
        return [$uri, ['name ' . $name]];

    }


    /**
     * @param $response
     *
     * @return string Resource guid
     */
    public function parseResponse (array $response) {
        return $this->searchResponseArrayForLine($response, '/resource (.*)$/');
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
        return null;
    }
} 