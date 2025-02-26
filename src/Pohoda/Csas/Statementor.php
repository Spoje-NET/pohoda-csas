<?php

declare(strict_types=1);

/**
 * This file is part of the PohodaCsas package
 *
 * https://github.com/Spoje-NET/pohoda-csas
 *
 * (c) Vítězslav Dvořák <vitezslav.dvorak@spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pohoda\Csas;

use SpojeNET\Csas\Accounts\DefaultApi;
use SpojeNET\Csas\ApiException;
use SpojeNET\Csas\Model\StatementList;

/**
 * Class Statementor
 * Handles the downloading and processing of bank statements.
 */
class Statementor extends \mServer\Bank
{
    protected int $rateOffset = 0;
    private DefaultApi $obtainer;

    /**
     * Downloaded XML statements.
     *
     * @var array<string, string>
     */
    private array $statementsXML = [];

    /**
     * Downloaded PDF statements.
     *
     * @var array<string, string>
     */
    private array $statementsPDF = [];

    /**
     * Bank Statement Helper.
     *
     * @param array<string, string> $options cnbCache,fixedRate,currency
     */
    public function __construct(string $bankAccount, DefaultApi $obtainer, array $options = [])
    {
        $this->account = $bankAccount;
        $this->obtainer = $obtainer;
        parent::__construct($bankAccount, $options);
    }

    /**
     * Download XML data from CSAS API.
     */
    public function downloadXML(): void
    {
        try {
            // Fetch statements from CSAS API
            $statementList = $this->obtainer->getStatements($this->account);

            if ($statementList instanceof StatementList) {
                foreach ($statementList->getAccountStatements() as $statement) {
                    $this->statementsXML[$statement->getId()] = $statement->getXmlContent();
                }
            }
        } catch (ApiException $e) {
            // Handle API exception
            echo 'Exception when calling DefaultApi->getStatements: ', $e->getMessage(), \PHP_EOL;
        }
    }

    /**
     * Import statements into Pohoda.
     */
    public function import(): void
    {
        // Implement the logic to import statements into Pohoda
    }

    /**
     * Get messages.
     */
    public function getMessages(): array
    {
        // Implement the logic to get messages
        return [];
    }

    /**
     * Get exit code.
     */
    public function getExitCode(): int
    {
        // Implement the logic to get exit code
        return 0;
    }
}
