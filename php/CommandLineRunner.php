<?php
/**
 * User: matt
 * Date: 23/06/14
 * Time: 15:37
 */

class CommandLineRunner implements Runner {

    public function run ($command, array $args = array()) {

        $file = false;
        $commandToRun = './elastichosts.sh ';
        if ($args) {
            $file = tempnam('./', 'ehprovision');
            file_put_contents($file, implode(PHP_EOL, $args));
            $commandToRun .= '-f ' . $file . ' ';
        }
        $commandToRun .= trim($command);

        $output = [];

        $commandToRun = escapeshellcmd($commandToRun);

        exec($commandToRun, $output);

        if ($file) {
            unlink($file);
        }
        return $output;

    }
} 