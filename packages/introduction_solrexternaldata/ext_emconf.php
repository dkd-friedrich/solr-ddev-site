<?php

/**
 * Extension Manager/Repository config file for ext "introduction_solrexternaldata".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Solr development site : EXT:solrexternaldata for Introduction',
    'description' => 'Solr development site : EXT:Solrfal Introduction',
    'category' => 'distribution',
    'constraints' => [
        'depends' => [
            'typo3' => '*',
            'solrexternaldata' => '*'
        ],
        'conflicts' => [
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'dkd Internet Service GmbH',
    'author_email' => 'solr-eb-suport@dkd.de',
    'author_company' => 'dkd',
    'version' => '1.0.0',
];
