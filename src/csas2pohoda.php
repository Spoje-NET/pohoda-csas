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

use Ease\Shared;

require_once '../vendor/autoload.php';

\define('APP_NAME', 'Pohoda CSAS Statements');
$exitcode = 0;
/**
 * Get today's Statements list.
 */
$options = getopt('o::e::', ['output::environment::']);
Shared::init(
    [
        'POHODA_URL', 'POHODA_USERNAME', 'POHODA_PASSWORD', 'POHODA_ICO', 'POHODA_BANK_IDS',
        'CSAS_API_KEY', 'CSAS_ACCESS_TOKEN', // , 'CSAS_ACCOUNT_IBAN'
    ],
    \array_key_exists('environment', $options) ? $options['environment'] : '../.env',
);
$destination = \array_key_exists('output', $options) ? $options['output'] : Shared::cfg('RESULT_FILE', 'php://stdout');

$report = [
    'sharepoint' => [],
    'pohoda' => [],
];

try {
    $engine = new Statementor(Shared::cfg('CSAS_ACCOUNT_UUID', Shared::cfg('CSAS_ACCOUNT_IBAN')), Shared::cfg('POHODA_BANK_IDS'));
    $engine->downloadXML();

    $engine->import();
    $report['messages'] = $engine->getMessages();
    $report['exitcode'] = $engine->getExitCode();

    if ($engine->getExitCode()) {
        $exitcode = $engine->getExitCode();
    }
} catch (\SpojeNET\Csas\ApiException $exc) {
    $report['mesage'] = $exc->getMessage();
    $exitcode = $exc->getCode();

    if (!$exitcode) {
        if (preg_match('/cURL error ([0-9]*):/', $report['mesage'], $codeRaw)) {
            $exitcode = (int) $codeRaw[1];
        }
    }
}

$written = file_put_contents($destination, json_encode($report, Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));

if (isset($engine)) {
    $engine->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');
}

exit($exitcode);
