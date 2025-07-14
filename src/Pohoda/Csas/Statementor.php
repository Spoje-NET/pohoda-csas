<?php

declare(strict_types=1);

/**
 * This file is part of the PohodaCsas package
 *
 * https://github.com/Spoje-NET/pohoda-csas
 *
 * (c) SpojeNetIT <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pohoda\Csas;

/**
 * Class Statementor
 * Handles the downloading and processing of bank statements.
 *
 * @no-named-arguments
 */
class Statementor extends \SpojeNet\CSas\Statementor
{
    /**
     * Instance of mServer\Bank for Pohoda integration.
     */
    protected \mServer\Bank $mServer;

    /**
     * IBAN of the account (if provided).
     */
    protected ?string $accountIban = null;

    /**
     * UUID of the account.
     */
    protected ?string $accountUuid = null;
    protected int $rateOffset = 0;
    private DefaultApi $this;

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
     * @param string                $bankAccount Account UUID or IBAN
     * @param array<string, string> $options     cnbCache,fixedRate,currency
     */
    /**
     * Statementor constructor.
     *
     * @param string                $bankAccount Account UUID or IBAN
     * @param string                $bankIds     Account identifier in Pohoda
     * @param array<string, string> $options     cnbCache,fixedRate,currency
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $bankAccount, string $bankIds, array $options = [])
    {
        $this->mServer = new \mServer\Bank([], $options);
        $this->csasApi = new \SpojeNet\CSas\Accounts\DefaultApi(/* new \SpojeNet\CSas\ApiClient($options) , $options */);
        $this->bankIds = $bankIds;
        $this->setObjectName($bankAccount.' 🏦 '.$bankIds);

        if (preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{1,30}$/', $bankAccount)) {
            // $bankAccount looks like an IBAN, obtain UUID for it
            $this->accountIban = $bankAccount;

            // The actual method to get UUID from IBAN may differ; adjust as needed for your API
            try {
                $accountInfo = self::getAccountByIban($this->csasApi, $bankAccount); // If not available, replace with correct method
                $this->accountUuid = $accountInfo->getId();
                $this->addStatusMessage(sprintf(_('Statementor was initialized using IBAN; the account UUID %s will be retrieved by an additional API call to the bank.'), $this->accountUuid), 'warning');
            } catch (\SpojeNet\CSas\ApiException $ex) {
                throw $ex; // TODO: Handle somehow in future
            }
        } elseif (preg_match('/^[0-9a-fA-F-]{36}$/', $bankAccount)) {
            // $bankAccount looks like a UUID
            $this->accountUuid = $bankAccount;
        } else {
            throw new \InvalidArgumentException(_('Invalid account identifier provided. It should be either an IBAN or a UUID.'));
        }

        parent::__construct($this->accountUuid, $bankAccount, \array_key_exists($options, 'scope') ? $options['scope'] : null);
    }

    /**
     * Download XML data from CSAS API.
     */
    /**
     * Download XML data from CSAS API.
     */
    public function downloadXML(): void
    {
        try {
            // Fetch statements from CSAS API
            // Replace with the correct method to get statements by UUID
            $statementList = $this->getAccountStatements($this->accountUuid);

            if ($statementList instanceof StatementList) {
                foreach ($statementList->getAccountStatements() as $statement) {
                    // Replace with correct methods for ID and XML content
                    $id = method_exists($statement, 'getId') ? $statement->getId() : ($statement->id ?? null);
                    $xml = method_exists($statement, 'getXmlContent') ? $statement->getXmlContent() : ($statement->xmlContent ?? null);

                    if ($id && $xml) {
                        $this->statementsXML[$id] = $xml;
                    }
                }
            }
        } catch (ApiException $e) {
            // Handle API exception
            echo 'Exception when calling DefaultApi->getAccountStatements: ', $e->getMessage(), \PHP_EOL;
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
