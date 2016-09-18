<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\CouchDB\Tools\Console\Command;

use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Command\Command;

class ReplicationCancelCommand extends Command
{
    protected function configure()
    {
        $this->setName('couchdb:replication:cancel')
             ->setDescription('Cancel replication from a given source to target.')
             ->setDefinition(array(
                new InputArgument('source', InputArgument::REQUIRED, 'Source Database', null),
                new InputArgument('target', InputArgument::REQUIRED, 'Target Database', null),
                new InputOption('continuous', 'c', InputOption::VALUE_NONE, 'Enable continuous replication', null),
             ))->setHelp(<<<EOT
With this command you cancel the replication between a given source and target.
All the options to POST /db/_replicate are available. Example usage:

    doctrine-couchdb couchdb:replication:cancel example-source-db example-target-db
    doctrine-couchdb couchdb:replication:cancel example-source-db http://example.com:5984/example-target-db

EOT
                );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $couchClient = $this->getHelper('couchdb')->getCouchDBClient();
        /* @var $couchClient \Doctrine\CouchDB\CouchDBClient */
        $data = $couchClient->replicate(
            $input->getArgument('source'),
            $input->getArgument('target'),
            true,
            $input->getOption('continuous') ? true : false
        );
        
        $output->writeln("Replication canceled.");
    }
}
