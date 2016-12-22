<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use EnvironmentCheck;
use Solr;


/**
 * Check the availability of all Solr indexes of given class.
 *
 * If there are no indexes of given class found, the returned status will still be "OK".
 */
class SolrIndexCheck implements EnvironmentCheck
{
    /**
     * @var null|string
     */
    protected $indexClass;

    /**
     * @param string $indexClass Limit the index checks to the specified class and all its subclasses.
     */
    public function __construct($indexClass = null)
    {
        $this->indexClass = $indexClass;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function check()
    {
        $brokenCores = array();

        if (!class_exists('Solr')) {
            return array(
                EnvironmentCheck::ERROR,
                'Class `Solr` not found. Is the fulltextsearch module installed?'
            );
        }

        $service = Solr::service();
        foreach (Solr::get_indexes($this->indexClass) as $index) {
            $core = $index->getIndexName();
            if (!$service->coreIsActive($core)) {
                $brokenCores[] = $core;
            }
        }

        if (!empty($brokenCores)) {
            return array(
                EnvironmentCheck::ERROR,
                'The following indexes are unavailable: ' . implode($brokenCores, ', ')
            );
        }

        return array(EnvironmentCheck::OK, 'Expected indexes are available.');
    }
}
