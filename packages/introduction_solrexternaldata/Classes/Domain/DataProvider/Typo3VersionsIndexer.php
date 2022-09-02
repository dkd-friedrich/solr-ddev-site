<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Dkd\ApacheSolrForTypo3SitepackageIntroductionSolrExternalData\Domain\DataProvider;


use ApacheSolrForTypo3\SolrExternalData\Domain\Index\ExternalDataIndexer;
use ApacheSolrForTypo3\SolrExternalData\Domain\Queue\Item;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Example external data indexer for TYPO3 versions
 */
class Typo3VersionsIndexer extends ExternalDataIndexer
{
    /**
     * {@inheritDoc}
     * @see \ApacheSolrForTypo3\SolrExternalData\Domain\Index\ExternalDataIndexer::getFullItemData()
     */
    protected function getFullItemData(Item $item): ?array
    {
        $data = parent::getFullItemData($item);
        $data['crdateTstamp'] = (new \DateTime($data['releaseDate']))->getTimestamp();
        $data['changedTstamp'] = (new \DateTime($data['date']))->getTimestamp();

        /** @var Typo3Version $typo3Version */
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $data['currentVersion'] = $typo3Version->getVersion();
        $data['currentBranch'] = $typo3Version->getBranch();
        $data['currentMajorVersion'] = $typo3Version->getMajorVersion();

        return $data;
    }
}
