<?php

namespace SilverStripe\EnvironmentCheck\Checks;

use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\FullTextSearch\Solr\Solr;
use SilverStripe\FullTextSearch\Solr\SolrIndex;
use SilverStripe\Dev\Deprecation;

/**
 * Check the availability of all Solr indexes
 *
 * If there are no indexes of given class found, the returned status will still be "OK".
 *
 * @package environmentcheck
 * @deprecated 3.1.0 Will be removed without equivalent functionality to replace it in a future major release.
 */
class SolrIndexCheck implements EnvironmentCheck
{
    public function __construct()
    {
        Deprecation::noticeWithNoReplacment('3.1.0', scope: Deprecation::SCOPE_CLASS);
    }

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
