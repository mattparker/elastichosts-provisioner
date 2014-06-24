<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 14:52
 */

class EHLogger {


    public function log ($msg) {

        echo "\n -- LOG " . date("H:i:s") . " -- " . $msg . "\n";

    }

} 