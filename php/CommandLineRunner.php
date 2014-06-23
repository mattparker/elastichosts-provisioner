<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 15:37
 */

class CommandLineRunner implements Runner {

    public function run ($command) {

        $output = [];
        $command = escapeshellcmd('./elastichosts.sh ' . $command);
        exec($command, $output);
        return $output;

    }
} 