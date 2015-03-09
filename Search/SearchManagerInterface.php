<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Search;

use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadataInterface;

interface SearchManagerInterface
{
    /**
     * @param object $object
     * @return IndexMetadataInterface
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getMetadata($object);

    /**
     * Search with the given query string
     *
     * @param SearchQuery
     * @return QueryHit[]
     * @throws \Exception
     */
    public function search(SearchQuery $searchQuery);

    /**
     * Create a search query builder
     *
     * @param string $queryString
     * @return SearchQueryBuilder
     */
    public function createSearch($query);

    /**
     * Attempt to index the given object
     *
     * @param object $object
     */
    public function index($object);

    /**
     * Remove the given mapped objects entry from
     * its corresponding index
     *
     * @param object $object
     */
    public function deindex($object);

    /**
     * Return an array of arbitrary information
     * about the current state of the adapter
     *
     * @return array
     */
    public function getStatus();

    /**
     * Return a list of all the category names
     *
     * @return string[]
     */
    public function getCategoryNames();

    /**
     * Flush the adapter.
     *
     * The manager should keep track of the indexes that need flushing.
     */
    public function flush();
}
