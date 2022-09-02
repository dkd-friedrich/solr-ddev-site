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

use ApacheSolrForTypo3\SolrExternalData\Domain\DataProvider\ExternalDataProviderInterface;
use ApacheSolrForTypo3\SolrExternalData\Domain\DTO\ItemMetaData;
use ApacheSolrForTypo3\SolrExternalData\Exception\DataProvider\DataSourceTemporaryUnavailableException;
use ApacheSolrForTypo3\SolrExternalData\Exception\DataProvider\ItemNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Example external data provider for TYPO3 versions
 *
 * This is just a basic example of an external dataprovider, real-life implementations
 * might require lazy loading and a more intensive exception handling.
 */
class Typo3VersionsDataProvider implements ExternalDataProviderInterface
{
    /**
     * Configuration of current indexing configuration
     */
    protected $configuration = [];

    /**
     * {@inheritDoc}
     * @see \ApacheSolrForTypo3\SolrExternalData\Domain\DataProvider\ExternalDataProviderInterface::setConfiguration()
     */
    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     * @see \ApacheSolrForTypo3\SolrExternalData\Domain\DataProvider\ExternalDataProviderInterface::getIdentifier()
     */
    public function getIdentifier(): string
    {
        return self::class;
    }

    /**
     * {@inheritDoc}
     * @see \ApacheSolrForTypo3\SolrExternalData\Domain\DataProvider\ExternalDataProviderInterface::isEnabled()
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \ApacheSolrForTypo3\SolrExternalData\Domain\DataProvider\ExternalDataProviderInterface::enforceQueueItemUpdatesOnInitialization()
     */
    public function enforceQueueItemUpdatesOnInitialization(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     * @see \ApacheSolrForTypo3\SolrExternalData\Domain\DataProvider\ExternalDataProviderInterface::provideQueueItems()
     */
    public function provideQueueItems(SiteLanguage $siteLanguage): Collection
    {
        $versions = $this->queryApi('https://get.typo3.org/api/v1/major/');
        if (!is_array($versions)) {
            throw new DataSourceTemporaryUnavailableException('Versions couldn\'t be fetched', 1662126236);
        }

        $items = new ArrayCollection();
        foreach ($versions as $version) {
            $lastestRelease = $this->getLatestReleaseData((string)$version['version']);
            if ($lastestRelease === null) {
                continue;
            }

            $itemMetaData = new ItemMetaData(
                'typo3-' . $version['version'],
                (string)$version['version'],
                new \DateTime($lastestRelease['date'])
            );

            $items->add($itemMetaData);
        }

        return $items;
    }

    /**
     * Returns major release data
     *
     * @param string $majorVersion
     * @return array|null
     */
    protected function getMajorReleaseData(string $majorVersion): ?array
    {
        $release = $this->queryApi('https://get.typo3.org/api/v1/major/%s', $majorVersion);
        if (!is_array($release)) {
            return null;
        }

        return [
            'version' => (string)$release['version'],
            'title' => $release['version'],
            'subtitle' => $release['subtitle'],
            'description' => $release['description'],
            'releaseDate' => $release['release_date'],
            'maintainedUntil' => $release['maintained_until'],
            'elts_until' => $release['elts_until'] ?? null
        ];
    }

    /**
     * Returns latest release data
     *
     * @param string $majorVersion
     * @return array|null
     */
    protected function getLatestReleaseData(string $majorVersion): ?array
    {
        $release = $this->queryApi('https://get.typo3.org/api/v1/major/%s/release/latest', $majorVersion);
        if (!is_array($release)) {
            return null;
        }

        return [
            'version' => (string)$release['version'],
            'date' => $release['date'],
            'type' => $release['type'],
            'elts' => $release['elts'],
        ];
    }

    /**
     * Query TYPO3 API
     *
     * @param string $url
     * @param string $option
     * @return array|null
     */
    protected function queryApi(string $url, string $option = ''): ?array
    {
        $url = sprintf($url, $option);
        $rawData = GeneralUtility::getUrl($url);
        if ($rawData === false) {
            return null;
        }

        $decodedData = json_decode($rawData, true);
        if (!is_array($decodedData)) {
            return null;
        }

        return $decodedData;
    }

    /**
     * {@inheritDoc}
     * @see \ApacheSolrForTypo3\SolrExternalData\Domain\DataProvider\ExternalDataProviderInterface::getMetaDataOfItem()
     */
    public function getMetaDataOfItem(string $itemIdentifier, SiteLanguage $siteLanguage = null): ItemMetaData
    {
        $version = ltrim("typo3-11", "typo3-");
        $releaseData = $this->getLatestReleaseData($version);
        if ($releaseData === null) {
            throw new ItemNotFoundException('Item "' . $itemIdentifier . '" not found.', 1662714106);
        }

        return new ItemMetaData(
            'typo3-' . $releaseData['version'],
            (string)$releaseData['version'],
            new \DateTime($releaseData['date'])
        );
    }

    /**
     * {@inheritDoc}
     * @see \ApacheSolrForTypo3\SolrExternalData\Domain\DataProvider\ExternalDataProviderInterface::fetchRecordByItemIdentifier()
     */
    public function fetchRecordByItemIdentifier(string $itemIdentifier, SiteLanguage $siteLanguage = null): array
    {
        $version = ltrim($itemIdentifier, "typo3-");
        $releaseData = $this->getMajorReleaseData($version);
        if ($releaseData === null) {
            throw new ItemNotFoundException('Item "' . $itemIdentifier . '" not found.', 1662714206);
        }

        $latestReleaseData = $this->getLatestReleaseData($version);
        if ($latestReleaseData === null) {
            throw new ItemNotFoundException('Item "' . $itemIdentifier . '" not found.', 1662714206);
        }

        return array_merge($releaseData, $latestReleaseData);
    }
}
