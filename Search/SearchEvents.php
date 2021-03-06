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

/**
 * Event names that are used by the Search event dispatcher
 */
class SearchEvents
{
    const SEARCH = 'massive_search.search';
    const HIT = 'massive_search.hit';
    const PRE_INDEX = 'massive_search.pre_index';
}
