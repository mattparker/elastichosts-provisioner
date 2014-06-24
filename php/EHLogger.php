<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 14:52
 */

/**
 * @codeCoverageIgnore
 */
class EHLogger {


    /**
     * Just echos the message with the time
     * @param $msg
     */
    public function log ($msg) {

        echo "\n -- LOG " . date("H:i:s") . " -- " . $msg . "\n";

    }

} 