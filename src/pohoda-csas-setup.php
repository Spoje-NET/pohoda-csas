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

require_once '../vendor/autoload.php';
/**
 * Get List of bank accounts and import it into Pohoda.
 */
// \Ease\Shared::init(['POHODA_URL', 'POHODA_USERNAME', 'POHODA_PASSWORD', 'POHODA_ICO', 'CERT_FILE', 'CERT_PASS', 'XIBMCLIENTID'], $argv[1] ?? '../.env');
$apiInstance = new \SpojeNET\Csas\Accounts\DefaultApi();
$x_request_id = time(); // string | Unique request id provided by consumer application for reference and auditing.
