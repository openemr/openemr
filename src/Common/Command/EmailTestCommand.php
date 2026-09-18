<?php

/**
 * CLI command to test email sending via each of OpenEMR's email code paths
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Services\Email\EmailSendMethod;
use OpenEMR\Services\Email\EmailTestService;
use OpenEMR\Services\IGlobalsAware;
use OpenEMR\Services\Trait\GlobalInterfaceTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EmailTestCommand extends Command implements IGlobalsAware
{
    use GlobalInterfaceTrait;

    protected function configure(): void
    {
        $this
            ->setName('email:test')
            ->setDescription('Send a test email via one or more of OpenEMR\'s email code paths')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('sender', 's', InputOption::VALUE_REQUIRED, 'Sender email address'),
                    new InputOption('recipient', 'r', InputOption::VALUE_REQUIRED, 'Recipient email address'),
                    new InputOption('method', 'm', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Send method(s): direct, queue, queue_templated (repeatable; defaults to all)'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sender = $input->getOption('sender');
        if (!is_string($sender) || $sender === '') {
            $io->error('The --sender (-s) option is required.');
            return Command::FAILURE;
        }

        $recipient = $input->getOption('recipient');
        if (!is_string($recipient) || $recipient === '') {
            $io->error('The --recipient (-r) option is required.');
            return Command::FAILURE;
        }

        $methods = $this->parseMethods($input, $io);
        if ($methods === null) {
            return Command::FAILURE;
        }

        $service = new EmailTestService(ServiceContainer::getLogger());
        $results = $service->test($sender, $recipient, $methods);

        $hasFailure = false;
        foreach ($results as $result) {
            if ($result->success) {
                $io->success("[{$result->method->value}] {$result->message}");
            } else {
                $io->error("[{$result->method->value}] {$result->message}");
                $hasFailure = true;
            }
        }

        return $hasFailure ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @return non-empty-list<EmailSendMethod>|null
     */
    private function parseMethods(InputInterface $input, SymfonyStyle $io): ?array
    {
        /** @var list<string> $raw */
        $raw = $input->getOption('method');

        if ($raw === []) {
            return [EmailSendMethod::Direct, EmailSendMethod::Queue, EmailSendMethod::QueueTemplated];
        }

        $methods = [];
        foreach ($raw as $value) {
            $method = EmailSendMethod::tryFrom($value);
            if ($method === null) {
                $valid = implode(', ', array_map(fn(EmailSendMethod $m) => $m->value, EmailSendMethod::cases()));
                $io->error("Unknown method '{$value}'. Valid methods: {$valid}");
                return null;
            }
            $methods[] = $method;
        }
        return $methods;
    }
}
