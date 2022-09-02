<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup('
  @import \'EXT:solrexternaldata/Configuration/TypoScript/Example/setup.typoscript\'
');