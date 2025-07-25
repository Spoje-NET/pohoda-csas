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
use Office365\Runtime\Auth\ClientCredential;
use Office365\Runtime\Auth\UserCredentials;
use Office365\SharePoint\ClientContext;

require_once '../vendor/autoload.php';

\define('APP_NAME', 'Pohoda Csas Statements');

/**
 * Get today's Statements list.
 */
$options = getopt('o::e::', ['output::environment::']);
Shared::init(
    [
        'POHODA_URL', 'POHODA_USERNAME', 'POHODA_PASSWORD', 'POHODA_ICO',
        'CSAS_ACCESS_TOKEN', 'CSAS_SANDBOX_MODE', 'ACCOUNT_NUMBER',
        'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
    ],
    \array_key_exists('environment', $options) ? $options['environment'] : '../.env',
);
$destination = \array_key_exists('output', $options) ? $options['output'] : \Ease\Shared::cfg('RESULT_FILE', 'php://stdout');

$engine = new Statementor(Shared::cfg('ACCOUNT_NUMBER'));
$engine->setScope(Shared::cfg('IMPORT_SCOPE', 'last_month'));
$engine->logBanner('', 'Scope: '.$engine->scope);
$exitcode = 0;
$fileUrls = [];
$report = [
    'sharepoint' => [],
    'pohoda' => [],
    'pohodaSQL' => [],
];

$pdfStatements = $engine->downloadPDF();

if ($pdfStatements) {
    sleep(5);

    $pdfStatements = $engine->getPdfStatements();

    if (Shared::cfg('OFFICE365_USERNAME', false) && Shared::cfg('OFFICE365_PASSWORD', false)) {
        $credentials = new UserCredentials(Shared::cfg('OFFICE365_USERNAME'), Shared::cfg('OFFICE365_PASSWORD'));
        $engine->addStatusMessage('Using OFFICE365_USERNAME '.Shared::cfg('OFFICE365_USERNAME').' and OFFICE365_PASSWORD', 'debug');
    } else {
        $credentials = new ClientCredential(Shared::cfg('OFFICE365_CLIENTID'), Shared::cfg('OFFICE365_CLSECRET'));
        $engine->addStatusMessage('Using OFFICE365_CLIENTID '.Shared::cfg('OFFICE365_CLIENTID').' and OFFICE365_CLSECRET', 'debug');
    }

    $ctx = (new ClientContext('https://'.Shared::cfg('OFFICE365_TENANT').'.sharepoint.com/sites/'.Shared::cfg('OFFICE365_SITE')))->withCredentials($credentials);
    $targetFolder = $ctx->getWeb()->getFolderByServerRelativeUrl(Shared::cfg('OFFICE365_PATH'));

    $engine->addStatusMessage('ServiceRootUrl: '.$ctx->getServiceRootUrl(), 'debug');

    foreach ($pdfStatements as $filename) {
        $uploadFile = $targetFolder->uploadFile(basename($filename), file_get_contents($filename));

        try {
            $ctx->executeQuery();
            $uploaded = $ctx->getBaseUrl().'/_layouts/15/download.aspx?SourceUrl='.urlencode($uploadFile->getServerRelativeUrl());
            $engine->addStatusMessage(_('Uploaded').': '.$uploaded, 'success');
            $report['sharepoint'][basename($filename)] = $uploaded;
            $fileUrls[basename($filename)] = $uploaded;
        } catch (\Exception $exc) {
            fwrite(fopen('php://stderr', 'wb'), $exc->getMessage().\PHP_EOL);

            $exitcode = 1;
        }
    }
} else {
    if (null === $pdfStatements) {
        $engine->addStatusMessage(_('Error obtaining PDF statements'), 'error');
        $exitcode = 2;
    } else {
        $engine->addStatusMessage(_('No PDF statements obtained'), 'info');
    }
}

sleep(5);

try {
    $xmlStatements = $engine->downloadXML();
} catch (\VitexSoftware\Csas\ApiException $exc) {
    $engine->addStatusMessage($exc->getMessage(), 'error');
    $exitcode = (int) $exc->getCode();
    $xmlStatements = false;
}

if ($xmlStatements) {
    $inserted = $engine->import();
    $report['pohoda'] = $inserted;

    if ($inserted) {
        if ($fileUrls) {
            $engine->addStatusMessage(sprintf(_('Updating PohodaSQL to attach statements in sharepoint links to invoice for %d'), \count($inserted)), 'debug');

            $doc = new \SpojeNet\PohodaSQL\DOC();
            $doc->setDataValue('RelAgID', \SpojeNet\PohodaSQL\Agenda::BANK); // Bank

            $filename = key($fileUrls);
            $sharepointUri = current($fileUrls);

            foreach ($inserted as $importInfo) {
                $id = $importInfo['id'];

                try {
                    $result = $doc->urlAttachment((int) $id, $sharepointUri, basename($filename));
                    $doc->addStatusMessage(sprintf('#%d: %s %s', $id, $importInfo['number'], $sharepointUri), $result ? 'success' : 'error');
                    $report['pohodaSQL'][$id] = $importInfo['number'];
                } catch (\Exception $ex) {
                    $engine->addStatusMessage(_('Cannot Update PohodaSQL to attach statements in sharepoint links to invoice'), 'error');
                    $report['pohodaSQL'][$id] = $ex->getMessage();
                    $exitcode = 4;
                }
            }
        } else {
            $engine->addStatusMessage(_('No statements uploaded to Sharepoint; Skipping PohodaSQL update'), 'warning');
        }
    } else {
        $engine->addStatusMessage(_('Empty statement'), 'warning');
    }
} else {
    if (\is_array($xmlStatements)) {
        $engine->addStatusMessage(_('No XML statements obtained'), 'info');
    } else {
        $engine->addStatusMessage(_('Error Obtaining XML statements'), 'error');
        $exitcode = 3;
    }
}

$written = file_put_contents($destination, json_encode($report, Shared::cfg('DEBUG') ? \JSON_PRETTY_PRINT : 0));
$engine->addStatusMessage(sprintf(_('Saving result to %s'), $destination), $written ? 'success' : 'error');

exit($exitcode);
