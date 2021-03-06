<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search\Localization;

use Massive\Bundle\SearchBundle\Search\LocalizationStrategyInterface;

/**
 * Index localization strategy
 *
 * Uses a separate index for each localization
 */
class IndexStrategy implements LocalizationStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function localizeIndexName($indexName, $locale)
    {
        if (null === $locale) {
            return $indexName;
        }

        return $indexName . '_' . $locale;
    }
}
