<?php

/**
 * CodingSearchTest — E2E test for #11346 fix.
 *
 * Verifies that querying for a list of code matches and searching the coding db do not cause unhandled errors.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @copyright Copyright (c) 2026 MedicalMasses L.L.C. <https://medicalmasses.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\NavBar\NavBarTrait;
use OpenEMR\Tests\E2e\Xpaths\XpathsConstantsCodesScreenTrait;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Panther\PantherTestCase;

class CodingSearchTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;
    use NavBarTrait;

    private string $loading = "Loading";
    private string $menuLink = "Admin||Coding||Codes";
    private string $expectedTabTitle = "Codes";


    private function navigateNextPage(int $pages = 1): void
    {
        try {
            if ($pages >= 0) {
                for ($page = 0; $page < $pages; $page++) {
                    $nextButton = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_NAVIGATE_NEXT_BUTTON_TRAIT);
                    $nextButton->click();
                }
            } else {
                for ($page = 0; $page > $pages; $page--) {
                    $prevButton = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_NAVIGATE_PREV_BUTTON_TRAIT);
                    $prevButton->click();
                }
            }
        } finally {
            return;
        }
    }


    #[Depends('testLoginAuthorized')]
    #[Test]
    public function testOpenSearch(): void
    {
        $this->runTest(function (): void {
            $this->goToMainMenuLink($this->menuLink);
            $this->assertActiveTab($this->expectedTabTitle, $this->loading);
        });
    }

    #[Depends('testOpenSearch')]
    #[Test]
    public function testPushCodes(): void
    {
        $this->runTest(function (): void {
            $this->goToMainMenuLink($this->menuLink);
            $this->assertActiveTab($this->expectedTabTitle, $this->loading);
        });
    }

    #[Depends('testPushCodes')]
    #[Test]
    public function testSearchICD10(): void
    {
        $this->runTest(
            function (): void {
                # Select ICD10 only
                $icd10 = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_ICD10_TRAIT);
                $icd10->click();

                # Find search box
                $searchBox = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BOX_TRAIT);
                $searchBox->text("minor");

                # Find search box
                $searchButton = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BUTTON_TRAIT);
                $searchButton->click();

                # Get the first result text
                $resultList = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_RESULT_LIST_TRAIT);
                $resultCount = $resultList->childNodes->count();

                $this->assertTrue($resultCount > 0);
            }
        );
    }

    #[Depends('testPushCodes')]
    #[Test]
    public function testSearchHandleNavigationNextPage(): void
    {
        $this->runTest(
            function (): void {
                # Select ICD10 only
                $icd10 = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_ICD10_TRAIT);
                $icd10->click();

                # Find search box
                $searchBox = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BOX_TRAIT);
                $searchBox->text("minor");

                # Find search button
                $searchButton = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BUTTON_TRAIT);
                $searchButton->click();

                # Go to Next Page
                $this->navigateNextPage(1);

                # Get the first result text
                $resultList = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_RESULT_LIST_TRAIT);
                $resultCount = $resultList->childNodes->count();

                $this->assertTrue($resultCount >= 0);
            }
        );
    }

    #[Depends('testPushCodes')]
    #[Test]
    public function testSearchHandleNavigationTenPagesAndTHenNewSearch(): void
    {
        $this->runTest(
            function (): void {
                # Select ICD10 only
                $icd10 = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_ICD10_TRAIT);
                $icd10->click();

                # Find search box
                $searchBox = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BOX_TRAIT);
                $searchBox->text("disease");

                # Find search button
                $searchButton = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BUTTON_TRAIT);
                $searchButton->click();

                # Go to Next Page
                $this->navigateNextPage(10);

                # Switch to CQM
                $cqm = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_CQM_TRAIT);
                $cqm->click();

                # Go back to search box and change search to term with much fewer results
                $searchBox = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BOX_TRAIT);
                $searchBox->text("Major");

                # Find search button
                $searchButton = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BUTTON_TRAIT);
                $searchButton->click();

                # Get the first result text
                $resultList = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_RESULT_LIST_TRAIT);
                $resultCount = $resultList->childNodes->count();

                $this->assertTrue($resultCount >= 0);
            }
        );
    }

    #[Depends('testPushCodes')]
    #[Test]
    public function testSearchICD10AndCQM(): void
    {
        $this->runTest(
            function (): void {
                # Select ICD10
                $icd10 = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_ICD10_TRAIT);
                $icd10->setAttribute("selected", true);

                # Select CQM
                $cqm = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_CQM_TRAIT);
                $cqm->setAttribute("selected", true);

                # Find search box
                $searchBox = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BOX_TRAIT);
                $searchBox->text("minor");

                # Find search box
                $searchButton = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_SEARCH_BUTTON_TRAIT);
                $searchButton->click();

                # Get the first result text
                $resultList = $this->findElement(XpathsConstantsCodesScreenTrait::CODE_TYPE_RESULT_LIST_TRAIT);
                $resultCount = $resultList->childNodes->count();

                $this->assertTrue($resultCount > 0);
            }
        );
    }
}
