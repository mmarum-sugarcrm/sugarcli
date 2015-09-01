<?php

namespace SugarCli\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

use Inet\SugarCRM\Application;
use Inet\SugarCRM\Database\Metadata;
use Inet\SugarCRM\Database\SugarPDO;
use Inet\SugarCRM\Exception\SugarException;

use SugarCli\Console\ExitCode;

class MetadataDumpCommand extends AbstractMetadataCommand
{
    protected function configure()
    {
        $this->setName('metadata:dumptofile')
            ->setDescription('Dump the contents of the table fields_meta_data for db migrations.')
            ->setHelp(<<<EOH
Manage the of the dump file based on the fields_meta_data table.
EOH
            );
        $descriptions = array(
            'add' => 'Add new fields from the DB to the definition file.',
            'del' => 'Delete fields not present in the DB from the metadata file.',
            'update' => 'Update the metadata file for modified fields in the DB.'
        );
        $this->setDiffOptions($descriptions);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getApplication()->getContainer()->get('logger');

        $path = $this->getDefaultOption($input, 'path');
        $metadata_file = $this->getMetadataOption($input);

        $diff_opts = $this->getDiffOptions($input);


        try {
            $pdo = new SugarPDO(new Application($logger, $path));
            $meta = new Metadata($logger, $pdo, $metadata_file);
            $base = array();
            if (is_readable($metadata_file)) {
                $base = $meta->loadFromFile();
            }
            $new = $meta->loadFromDb();
            $diff_res = $meta->diff(
                $base,
                $new,
                $diff_opts['mode'],
                $diff_opts['fields']
            );
            $logger->info("Fields metadata loaded from DB.");

            $meta->writeFile($diff_res);
            $output->writeln("Updated file $metadata_file.");
        } catch (SugarException $e) {
            $logger->error('An error occured while dumping the metadata.');
            $logger->error($e->getMessage());
            return ExitCode::EXIT_UNKNOWN_SUGAR_ERROR;
        }
    }
}
