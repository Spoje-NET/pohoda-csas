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

use SpojeNET\Csas\Accounts\DefaultApi;
use SpojeNET\Csas\ApiException;
use SpojeNET\Csas\Model\TransactionList;

/**
 * Handle bank transactions.
 *
 * @no-named-arguments
 */
class Transactor extends CsasBankClient
{
    /**
     * Transaction Handler.
     */
    public function __construct(string $bankAccount, DefaultApi $apiClient, array $options = [])
    {
        parent::__construct($apiClient);
        $this->account = $bankAccount;
    }

    /**
     * Download transactions from CSAS API.
     */
    public function downloadTransactions(): void
    {
        try {
            // Fetch transactions from CSAS API
            $transactionList = $this->csasApi->getTransactions($this->account);

            if ($transactionList instanceof TransactionList) {
                foreach ($transactionList->getTransactions() as $transaction) {
                    // Process each transaction as needed
                    self::processTransaction($transaction);
                }
            }
        } catch (ApiException $e) {
            // Handle API exception
            echo 'Exception when calling DefaultApi->getTransactions: ', $e->getMessage(), \PHP_EOL;
        }
    }

    /**
     * Process a single transaction.
     *
     * @param \SpojeNET\Csas\Model\TransactionListTransactionsInner $transaction
     */
    private static function processTransaction($transaction): void
    {
        // Implement the logic to process each transaction
    }
}
