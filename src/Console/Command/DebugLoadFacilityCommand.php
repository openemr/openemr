<?php

declare(strict_types=1);

namespace OpenEMR\Console\Command;

use Doctrine\ORM\EntityManagerInterface;
use OpenEMR\Entities\Facility;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'debug:load-facility')]
class DebugLoadFacilityCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $facility = $this->em->find(Facility::class, 52);
        $facility->name = base64_encode(random_bytes(8));
        $facility->color = '#CAC89D';
        print_r($facility);
        $this->em->flush();
        print_r($facility);

        // $f = new Facility();
        // $this->em->persist($f);
        // $this->em->flush();
        // print_r($f);

        return Command::SUCCESS;
    }
}
