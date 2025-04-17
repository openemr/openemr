<?php

/**
 * CreateReleaseChangelogCommand Is a helper utility to create a human readable changelog for a release
 * based on the milestone name.  It relies on the naming convention of prefixing each issue with the category
 * feat: for feature, bug: for bug, refactor: for changes, and chore: for chores.  If no category is specified
 * it will default to bug.  It will also look for the label developers and if it is present it will categorize the
 * issue as a developer issue.  That way regular users can see feature changes separated out from developer specific issues
 * such as api, code refactoring, etc.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\Common\Command\Runner\CommandContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateReleaseChangelogCommand extends Command
{
    const MAX_API_FETCH_COUNT = 15;

    protected function configure()
    {
        $this
            ->setName('openemr-dev:create-release-change-log')
            ->setDescription("Utility class to help test and use the client credentials grant assertion")
            ->addUsage('--site=default')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('milestone', 'm', InputOption::VALUE_REQUIRED, 'The milestone to generate the changelog for'),
                    new InputOption('token', 't', InputOption::VALUE_REQUIRED, 'Optional A github access token to use for API calls (unauthenticated requests are rate limited to 60 api calls per hour)'),
                    new InputOption('debug', 'd', InputOption::VALUE_NONE, 'Whether to turn on debug mode or not'),
                ])
            );
    }
    /**
     * Execute the command and spit any output to STDOUT and errors to STDERR
     * @param CommandContext $context All the context information needed for the CLI Command to execute
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // going to hit the github api endpoint for the milestone given in the api
        $milestoneName = $input->getOption('milestone');
        $accessToken = $input->getOption('token') ?? null;
        if (empty($milestoneName)) {
            $output->writeln($this->getSynopsis());
            return Command::INVALID;
        }
        $debug = false;
        if ($input->hasOption('debug')) {
            $debug = true;
        }

        try {
            $milestone = $this->getMilestoneNumberFromName($milestoneName, $accessToken);
//            $milestone = $this->getTestMilestoneNumberFromName($milestoneName, $accessToken);

            $issues = $this->getIssuesForMilestone($milestone, $accessToken);
//            $issues = $this->getTestIssues();

            if ($debug) {
                echo "Generating OpenEMR Changelog for Milestone " . $milestone . "\n\n";
                echo "Issues count: " . count($issues) . "\n\n";
            }
            echo "## [" . $milestoneName . "](https://github.com/openemr/openemr/milestone/" . $milestone . "?closed=1) - " . date("Y-m-d") . "\n\n";

            $uniqueCategories = [];
            $categorizedIssues = array_map(function ($issue) use (&$uniqueCategories) {
                // note the limit here means to limit our array elements to a maximum of two values, the last value will be the remainder of the string
                $categoryParts = explode(":", $issue['title'], 2);
                $title = $issue['title'];
                $category = "bug";
                $isDevelopment = false;
                if (count($categoryParts) > 1) {
                    $title = $categoryParts[1];
                    $category = $categoryParts[0];
                    $uniqueCategories[$category] = $category;
                }
                if (!empty($issue['labels'])) {
                    $filteredLabels = array_filter($issue['labels'], function ($label) {
                        return $label['name'] == 'developers';
                    });
                    $isDevelopment = !empty($filteredLabels);
                }

                return ['number' => $issue['number'], 'category' => $category, 'title' => $title
                    , 'url' => $issue['html_url'], 'isDevelopment' => $isDevelopment];
            }, $issues);

            $developerIssues = array_filter($categorizedIssues, function ($issue) {
                return $issue['isDevelopment'];
            });
            $standardIssues = array_filter($categorizedIssues, function ($issue) {
                return !$issue['isDevelopment'];
            });

            $this->printIssues($standardIssues, $uniqueCategories);
            echo "### OpenEMR Developer Changes\n\n";
            $this->printIssues($developerIssues, $uniqueCategories);
            return Command::SUCCESS;
        } catch (GuzzleException $exception) {
            if ($exception->getCode() == 403) {
                $this->printRateLimitMessage($exception->getResponse());
                throw $exception;
            }
            echo "Error getting issues from github. Exception message was: " . $exception->getMessage() . "\n";
            return Command::FAILURE;
        } catch (\Exception $e) {
            echo "Error getting issues from github. Exception message was: " . $e->getMessage() . "\n";
            return Command::FAILURE;
        }
    }

    private function getTestMilestoneNumberFromName($milestoneName, $accessToken)
    {
        return 12;
    }

    private function printRateLimitMessage($response)
    {
        $rateLimit = $response->getHeader('X-RateLimit-Limit')[0] ?? 0;
        $rateLimitRemaining = $response->getHeader('X-RateLimit-Remaining')[0] ?? 0;
        $rateLimitReset = $response->getHeader('X-RateLimit-Reset')[0] ?? 0;
        echo "Github API Rate Limit Exceeded\n";
        echo "Rate Limit: " . $rateLimit . "\n";
        echo "Rate Limit Remaining: " . $rateLimitRemaining . "\n";
        echo "Rate Limit Reset: " . $rateLimitReset . " Date: " . date(DATE_ATOM, $rateLimitReset) . "\n";
        echo "Please wait until the rate limit resets and try again\n";
    }

    private function getTestIssues()
    {
        $developerLabels = [
            ['name' => 'developers']
        ];
        $issues = [
            [
                'title' => 'bug: Telehealth Transfer Appointment fails to launch appointment on latest 7.0.1 patch'
                ,'number' => 6500
                ,'labels' => $developerLabels
            ]
            ,[
                'title' => 'feat: Add RestAPISecurityCheckEvent to allow module writers to extend or change the API security checks.'
                ,'number' => 6504
                ,'labels' => []
            ],[
                'title' => 'bug: lock down pnotes to user'
                ,'number' => 6550
                ,'labels' => []
            ],[
                'title' => 'chore: This is a test chore issue'
                ,'number' => 6551
                ,'labels' => $developerLabels
            ],[
                'title' => 'refactor: This is a test refactor issue'
                ,'number' => 6551
                ,'labels' => $developerLabels
            ]
        ];
        return $issues;
    }

    private function getMilestoneNumberFromName($milestoneName, $accessToken)
    {
        $milestone = $this->getMilestoneNumberForMilestoneStatus('open', $milestoneName, $accessToken);
        if ($milestone == false) {
            $milestone = $this->getMilestoneNumberForMilestoneStatus('closed', $milestoneName, $accessToken);
        }
        if ($milestone == false) {
            throw new \InvalidArgumentException("Could not find milestone number for milestone name " . $milestoneName);
        }
        return $milestone;
    }

    private function getMilestoneNumberForMilestoneStatus($milestoneStatus, $milestoneName, $accessToken)
    {
        $url = http_build_query(['state' => $milestoneStatus, 'per_page' => 100]);
        $url = "https://api.github.com/repos/openemr/openemr/milestones?" . $url;

        $guzzle = new Client();
        try {
            if (!empty($accessToken)) {
                $headers = ['Authorization' => 'token ' . $accessToken];
                $response = $guzzle->request('GET', $url, ['headers' => $headers]);
            } else {
                $response = $guzzle->get($url);
            }
            // now we need to find the milestone number by comparing the milestone name to the name in the JSON response
            if ($response->getStatusCode() == 200) {
                $milestones = json_decode($response->getBody(), true);
                foreach ($milestones as $milestone) {
                    if ($milestone['title'] == $milestoneName) {
                        return $milestone['number'];
                    }
                }
                // if we didn't find the milestone we should grab the next link from the header and find it here
                $headers = $response->getHeaders();
                $nextLink = $this->getNextLink($headers);
                $loopBreak = false;
                while ($nextLink && $loopBreak++ < self::MAX_API_FETCH_COUNT) {
                    echo "attempting to retrieve " . $nextLink . "\n";
                    if (!empty($accessToken)) {
                        $response = $guzzle->request('GET', $nextLink, ['headers' => ['Authorization' => 'token ' . $accessToken]]);
                    } else {
                        $response = $guzzle->get($nextLink);
                    }
                    if ($response->getStatusCode() == 200) {
                        $headers = $response->getHeaders();
                        $milestones = json_decode($response->getBody(), true);
                        foreach ($milestones as $milestone) {
                            if ($milestone['title'] == $milestoneName) {
                                return $milestone['number'];
                            }
                        }
                        $nextLink = $this->getNextLink($headers);
                    } elseif ($response->getStatusCode() == 403) {
                        $this->printRateLimitMessage($response);
                        $nextLink = false;
                    } else {
                        $nextLink = false;
                    }
                }
                if ($loopBreak >= self::MAX_API_FETCH_COUNT) {
                    throw new \RuntimeException("Error getting milestones from github.  Too many API calls\n");
                }
                return false;
            } else if ($response->getStatusCode() === 403) {
                $this->printRateLimitMessage($response);
                throw new \RuntimeException("Error getting milestones from github\n");
            } else {
                throw new \RuntimeException("Error getting milestones from github\n");
            }
        } catch (GuzzleException $exception) {
            if ($exception->getCode() == 403) {
                $this->printRateLimitMessage($exception->getResponse());
                throw $exception;
            }
            echo "Error getting milestones from github. Exception message was: " . $exception->getMessage() . "\n";
            throw $exception;
        } catch (\Exception $e) {
            echo "Error getting milestones from github. Exception message was: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    private function filterIssuesByCategory(&$issues, $category)
    {
        return array_filter($issues, function ($issue) use ($category) {
            return $issue['category'] == $category;
        });
    }

    private function printIssues(&$issues, $uniqueCategories)
    {

        $issuesByCategory = [];
        // yes its not performant, but there should be < 1000 issues in a release so it doesn't matter.
        $issuesByCategory['feat'] = $this->filterIssuesByCategory($issues, 'feat');
        $issuesByCategory['bug'] = $this->filterIssuesByCategory($issues, 'bug');
        $issuesByCategory['other'] = $this->filterOtherIsssues($issues, ['feat', 'bug']);

        $this->printIssuesForCategory($issuesByCategory['feat'] ?? [], "Added");
        $this->printIssuesForCategory($issuesByCategory['bug'] ?? [], "Fixed");
        $this->printIssuesForCategory($issuesByCategory['other'] ?? [], "Changed");
    }

    private function filterOtherIsssues(&$issues, $categories)
    {
        return array_filter($issues, function ($issue) use ($categories) {
            return !in_array($issue['category'], $categories);
        });
    }

    private function printIssuesForCategory($issues, $categoryLabel)
    {
        if (!empty($issues)) {
            echo "### " . $categoryLabel . "\n";
            foreach ($issues as $issue) {
                echo "  - " . $issue['title'] . " ([#" . $issue['number'] . "](" . $issue['url'] . "))\n";
            }
            echo "\n\n";
        }
    }

    /**
     * Gets the next link from the headers or returns false if no valid next link was found
     * @param array $headers
     * @return string|false
     */
    private function getNextLink(array $headers)
    {
        // link format is in the following format
        // Link: <https://api.github.com/repositories/679584/issues?milestone=12&per_page=100&state=closed&page=2>; rel="next", <https://api.github.com/repositories/67958
        //4/issues?milestone=12&per_page=100&state=closed&page=3>; rel="last"

        $link = $headers['Link'][0] ?? null;
        if (!empty($link)) {
            $linkParts = explode(",", $link);
            foreach ($linkParts as $linkPart) {
                $linkPart = trim($linkPart);
                $linkPartParts = explode(";", $linkPart);
                if (count($linkPartParts) > 1) {
                    $linkPartParts[1] = trim($linkPartParts[1]);
                    if ($linkPartParts[1] == 'rel="next"') {
                        $linkPartParts[0] = trim($linkPartParts[0]);
                        $linkPartParts[0] = str_replace("<", "", $linkPartParts[0]);
                        $linkPartParts[0] = str_replace(">", "", $linkPartParts[0]);
                        return $linkPartParts[0];
                    }
                }
            }
        }

        return false;
    }

    private function getIssuesForMilestone($milestone, mixed $accessToken)
    {

        // need to hit the issues and grab the title and the number
        // we don't filter on the pull requests because some people still commit without an issue number so if its
        // tagged to a milestone we will use that value
        // we grab the first batch and check the headers.  If there is a link header we check for next links
        // we keep grabbing our issues until there are no more next links
        // we do this in batches of 100 to grab the maximum number of issues completed for the release

        // curl --include --request GET \
        //--url "https://api.github.com/repos/octocat/Spoon-Knife/issues" \
        //--header "Accept: application/vnd.github+json"
        $url = http_build_query(['milestone' => $milestone, 'state' => 'closed', 'per_page' => 100]);
        $url = "https://api.github.com/repos/openemr/openemr/issues?" . $url;
        $guzzle = new Client();
        // make a guzzle request with an Authorization: token <token> header;
        if (!empty($accessToken)) {
            $response = $guzzle->request('GET', $url, ['headers' => ['Authorization' => 'token ' . $accessToken]]);
        } else {
            $response = $guzzle->get($url);
        }
        $loopBreak = 0;
        if ($response->getStatusCode() == 200) {
            $headers = $response->getHeaders();
            $issues = json_decode($response->getBody(), true);
            $nextLink = $this->getNextLink($headers);
            while ($nextLink && $loopBreak++ < self::MAX_API_FETCH_COUNT) { // 1500 issues would seem like quite a bit
                if (!empty($accessToken)) {
                    $response = $guzzle->request('GET', $nextLink, ['headers' => ['Authorization' => 'token ' . $accessToken]]);
                } else {
                    $response = $guzzle->get($nextLink);
                }
                if ($response->getStatusCode() == 200) {
                    $headers = $response->getHeaders();
                    $issues = array_merge($issues, json_decode($response->getBody(), true));
                    $nextLink = $this->getNextLink($headers);
                } else if ($response->getStatusCode() === 403) {
                    $this->printRateLimitMessage($response);
                    $nextLink = false;
                } else {
                    $nextLink = false;
                }
            }
            if ($loopBreak >= self::MAX_API_FETCH_COUNT) {
                throw new \RuntimeException("Error getting issues from github.  Too many API calls\n");
            }
        } else if ($response->getStatusCode() === 403) {
            $this->printRateLimitMessage($response);
        } else {
            echo "Error getting issues from github\n";
            return [];
        }
        // sort the issues
        usort($issues, function ($a, $b) {
            return strcmp($a['title'], $b['title']);
        });
        return $issues;
    }
}
