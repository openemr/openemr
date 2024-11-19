<?php

namespace OpenEMR\Common\Command;

require_once __DIR__ . '/../../../library/layout.inc.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FormLayoutAddField extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('form:layout:field:add')
            ->setDescription('Add a field to a form layout')
            ->addUsage('--layout_id=1 --data={data}')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('layout_id', null, InputOption::VALUE_REQUIRED, 'Form layout id'),
                    new InputOption('newseq', null, InputOption::VALUE_REQUIRED, 'New sequence number'),
                    new InputOption('newid', null, InputOption::VALUE_REQUIRED, 'New field id'),
                    new InputOption('newtitle', null, InputOption::VALUE_REQUIRED, 'Field label'),
                    new InputOption('newuor', null, InputOption::VALUE_REQUIRED, 'Field UOR'),
                    new InputOption('newlengthWidth', null, InputOption::VALUE_REQUIRED, 'Size Width'),
                    new InputOption('newlengthHeight', null, InputOption::VALUE_REQUIRED, 'Size Height'),
                    new InputOption('newmaxSize', null, InputOption::VALUE_REQUIRED, 'Max Size'),
                    new InputOption('newtitlecols', null, InputOption::VALUE_REQUIRED, 'Label Cols'),
                    new InputOption('newdatacols', null, InputOption::VALUE_REQUIRED, 'Data Cols'),
                    new InputOption('data', null, InputOption::VALUE_REQUIRED, 'Data to add to form layout'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = [
            'newsource' => 'F',
            'newfieldgroupid' => '1',
            'newdefault' => '',
            'newdesc' => '',
            'newdatatype' => '',
            'newlistid' => '',
            'newcodes' => '',
            'newbackuplistid' => '',
        ];

        $data = array_merge($data, $input->getOptions());

        // 1. addField
        // 2. addColumn
        // 3. setLayoutTimestamp
        addField($data['layout_id'], $data);
        addColumn($data['layout_id'], $data['newid']);
        setLayoutTimestamp($data['layout_id']);

        return 0;
    }
}
