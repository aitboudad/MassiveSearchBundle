Mapping
=======

The MassiveSearchBundle requires that you define which objects should be indexed
through *mapping*. Currently only **XML mapping** is natively supported:

.. code-block:: xml

    <!-- /path/to/YourBundle/Resources/config/massive-search/Product.xml -->
    <massive-search-mapping xmlns="http://massive.io/schema/dic/massive-search-mapping">

        <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
            <index name="product" />
            <id property="id" />
            <fields>
                <field name="title" type="string" />
                <field name="body" type="string" />
            </fields>
        </mapping>

    </massive-search-mapping>

This mapping will cause the fields ``title`` and ``body`` to be indexed into
an index named ``product`` and the ID obtained from the objects ``id`` field.

Mapping elements
----------------

The possible mappings are:

- ``index``: Name of the index in which to insert the record
- ``title``: Title to use in search results
- ``description``: A description for the search result
- ``url``: The URL to which the search resolt should link to
- ``image``: An image to associate with the search result
- ``fields``: List of ``<field />`` elements detailing which fields should be
  indexed (i.e. used when finding search results).

Each mapping can use either a ``property`` attribute or an ``expr`` attribute.
These attributes determine how the value is retrieved. ``property`` will use
the Symfony `PropertyAccess`_ component, and ``expr`` will use
`ExpressionLanguage`_.

PropertyAccess allows you to access properties of an object by path, e.g.
``title``, or ``parent.title``. The expression allows you to build expressions
which can be evaluated, e.g. ``'/this/is/' ~ object.id ~ '/a/path'``.

Fields
------

Fields dictate which fields are indexed in the underlying search engine.

Mapping:

- ``name``: Field name
- ``property``: Object property to map the field
- ``expr``: Mutually exclusive with ``property``

Types:

- ``string``: Store as a string
- ``complex``: Apply mapping to an array of values

Complex mapping
~~~~~~~~~~~~~~~

Complex mapping provides a way to map a nested data structure within the
subject object.

.. note::

    This feature is not currently supported by the XML driver and is therefore
    not available unless used in a custom driver.

Expression Language
~~~~~~~~~~~~~~~~~~~

The MassiveSearchBundle includes its own flavor of the Symfony expression
language.

.. code-block:: xml

    <massive-search-mapping xmlns="http://massive.io/schema/dic/massive-search-mapping">
        <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
            <!-- ... -->
            <url expr="'/path/to/' ~ article.title'" />
            <!-- ... -->
        </mapping>
    </massive-search-mapping>

Functions:

- ``join``: Maps to the ``implode`` function in PHP. e.g. ``join(',', ["one",
  "two"])`` equals ``"one,two"``
- ``map``: Maps to the ``array_map`` function in PHP. e.g. ``map([1, 2, 3],
  'el + 1')`` equals ``array(2, 3, 4)``.

Contexts
~~~~~~~~

Sometimes you will require different mappings based on the context of the web
application. For example, an Article may have one URL in the front office, and
another in the backoffice (i.e. for viewing and editing respectively).

MassiveSearchBundle solves this with ``contexts``. A ``context`` allows you
to map additional indexes:

.. code-block:: xml

    <!-- /path/to/YourBundle/Resources/config/massive-search/Product.xml -->
    <massive-search-mapping xmlns="http://massive.io/schema/dic/massive-search-mapping">

        <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
            <index name="product" />
            <id property="id" />
            <url expr="'/path/to/' ~ article.title'" />
            <fields>
                <field name="title" type="string" />
                <field name="body" type="string" />
            </fields>

            <context name="admin">
                <url exp="'/admin/edit/article/' ~ object.id" />
                <index name="product_foo" />
            </context>
        </mapping>

    </massive-search-mapping>

The above would create two mappings for the ``Product``. The second would use
the index name ``product_foo`` and override the ``url`` field.

Localization
------------

You can add a ``locale`` mapping which will cause the object to be stored in a
localized index (if configured, see :doc:`localization`).

.. code-block:: xml

    <!-- /path/to/YourBundle/Resources/config/massive-search/Product.xml -->
    <massive-search-mapping xmlns="http://massive.io/schema/dic/massive-search-mapping">

        <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
            <!-- ... -->
            <locale property="locale" />
            <!-- ... -->
        </mapping>

    </massive-search-mapping>

This assumes that the object has a property ``$locale`` which contiains the
objects current localization code.

If you do not map the ``locale`` or the ``locale`` is reosolved as ``NULL``
then it will be assumed that the object is not localized.

Full example
------------

The following example uses all the mapping options:

.. code-block:: xml

    <!-- /path/to/YourBundle/Resources/config/massive-search/Product.xml -->
    <massive-search-mapping xmlns="http://massive.io/schema/dic/massive-search-mapping">

        <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
            <index name="product" />
            <id property="id" />
            <locale property="locale" />
            <title property="title" />
            <url expr="'/path/to/' ~ object.id" />
            <description property="body" />
            <image expr="'/assets/images/' ~ object.type" />
            <fields>
                <field name="title" type="string" />
                <field name="body" type="string" />
            </fields>

            <context name="admin">
                <url exp="'/admin/edit/article/' ~ object.id" />
            </context>
        </mapping>

    </massive-search-mapping>

Note:

- This file **MUST** be located in ``YourBundle/Resources/config/massive-search``
- It must be named after the name of your class (without the namespace) e.g.
  ``Product.xml``
- Your ``Product`` class MUST be located in one of the following folders:
  - ``YourBundle/Document``
  - ``YourBundle/Entity``
  - ``YourBundle/Model``

.. note::

    It will be possible in the future to specify paths for mapping files.

.. note:: 

    The bundle automatically removes existing documents with the same
    ID. The ID mapping is mandatory.

.. _`PropertyAccess`: http://symfony.com/doc/current/components/property_access/index.html
.. _`ExpressionLanguage`: http://symfony.com/doc/current/components/expression_language/index.html

