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

\define('APP_NAME', 'Pohoda Csas Statements');
$options = getopt('o::e::', ['output::environment::']);

/**
 * Get today's Statements list.
 */
\Ease\Shared::init(
    [
        'POHODA_URL', 'POHODA_USERNAME', 'POHODA_PASSWORD', 'POHODA_ICO',
        'CSAS_ACCESS_TOKEN', 'CSAS_SANDBOX_MODE', 'ACCOUNT_NUMBER',
    ],
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
);
$destination = \array_key_exists('o', $options) ? $options['o'] : (\array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout'));

$engine = new Statementor(\Ease\Shared::cfg('ACCOUNT_NUMBER'));

$apiInstance = new \SpojeNET\CsasAccountsApi\AccountsApi\DefaultApi();

// $engine->setScope(\Ease\Shared::cfg('IMPORT_SCOPE', 'last_month'));
// $engine->logBanner('', 'Scope: '.$engine->scope);
//
// $engine->downloadXML();
// $inserted = $engine->import();
