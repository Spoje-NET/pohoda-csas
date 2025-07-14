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

/**
 * Description of CsasBankClient.
 *
 * @no-named-arguments
 */
abstract class CsasBankClient extends \mServer\Bank
{
    /**
     * DateTime Formating eg. 2021-08-01T10:00:00.0Z.
     */
    public const DATE_FORMAT = 'Y-m-d\TH:i:s.0\Z';

    /**
     * CSAS API client.
     */
    protected DefaultApi $csasApi;

    /**
     * CsasBankClient constructor.
     */
    public function __construct(DefaultApi $csasApi)
    {
        $this->csasApi = $csasApi;
        parent::__construct();
    }

    /**
     * Download XML data from CSAS API.
     */
    public function downloadXML(): void
    {
        try {
            // Fetch accounts from CSAS API
            $accounts = $this->csasApi->getAccounts();
            // Process the accounts data as needed
        } catch (ApiException $e) {
            // Handle API exception
            echo 'Exception when calling DefaultApi->getAccounts: ', $e->getMessage(), \PHP_EOL;
        }
    }

    /**
     * Check certificate validity.
     */
    public static function checkCertificate(string $certFile, string $certPass): void
    {
        // Implement the logic to check certificate validity
    }
}
