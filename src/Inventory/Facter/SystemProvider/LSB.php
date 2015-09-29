<?php

namespace SugarCli\Inventory\Facter\SystemProvider;

use Symfony\Component\Process\Process;
use SugarCli\Inventory\Facter\FacterInterface;

class LSB implements FacterInterface
{
    public function parseOutput($output)
    {
        $lsb = array();
        foreach (explode("\n", $output) as $line) {
            list($key, $value) = explode("\t", $line, 2);
            $lsb[trim($key, ':')] = $value;
        }
        return $lsb;
    }

    public function getFacts()
    {
        $process = new Process('lsb_release -cidr');
        $process->mustRun();
        $lsb = $this->parseOutput($process->getOutput());
        list($major, $minor) = explode('.', $lsb['Release'], 2);
        return array(
            'os' => array(
                'name' => $lsb['Distributor ID'],
                'release' => array(
                    'major' => $major,
                    'minor' => $minor,
                    'full' => $lsb['Release'],
                ),
                'lsb' => array(
                    'distcodename' => $lsb['Codename'],
                    'distid' => $lsb['Distributor ID'],
                    'distdescription' => $lsb['Description'],
                    'distrelease' => $lsb['Release'],
                    'majdistrelease' => $major,
                    'minordistrelease' => $minor,
                ),
            ),
        );
    }
}