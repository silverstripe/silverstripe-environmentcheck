<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\FullTextSearch\Solr\Solr;
use SilverStripe\FullTextSearch\Solr\SolrIndex;

/**
 * Check the availability of all Solr indexes
 *
 * If there are no indexes of given class found, the returned status will still be "OK".
 *
 * @package environmentcheck
 */
class SolrIndexCheck implements EnvironmentCheck
{
    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function check()
    {
        $brokenCores = [];

        if (!class_exists(Solr::class)) {
            return [
                EnvironmentCheck::ERROR,
                'Class `' . Solr::class . '` not found. Is the fulltextsearch module installed?'
            ];
        }

        $service = Solr::service();
        foreach (Solr::get_indexes() as $index) {
            /** @var SolrIndex $core */
            $core = $index->getIndexName();
            if (!$service->coreIsActive($core)) {
                $brokenCores[] = $core;
            }
        }

        if (!empty($brokenCores)) {
            return [
                EnvironmentCheck::ERROR,
                'The following indexes are unavailable: ' . implode(', ', $brokenCores)
            ];
        }

        return [EnvironmentCheck::OK, 'Expected indexes are available.'];
    }
}
