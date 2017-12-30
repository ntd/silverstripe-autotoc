Usage
-----

`silverstripe-autotoc` is basically a [SilverStripe 4](http://www.silverstripe.org/)
module that extends a controller (if the CMS is present the
[ContentController](http://api.silverstripe.org/4/SilverStripe/CMS/Controllers/ContentController.html)
will be extended out of the box) to provide:

* the *$Autotoc* tag, containing the table of contents dynamically
  generated from the content of the current page. The tree is provided as
  a mixture of
  [ArrayData](http://api.silverstripe.org/4/SilverStripe/View/ArrayData.html) and
  [ArrayList](http://api.silverstripe.org/4/SilverStripe/ORM/ArrayList.html),
  ready to be consumed by templates;
* the *$ContentField* tag, containing the HTML content properly augmented with
  the destinations of the TOC links;
* the *$OriginalContentField* tag, for accessing the original (non-augmented)
  content field;
* an automatic override of the value of the content field (*$Content* by
  default): accessing that field will automatically return the augmented HTML.
  Use *$OriginalContentField* to access the non-augmented content, if needed.

You will need to modify your templates for embedding the *$Autotoc* tag
(see [AutoTOC format](format.md) for the gory details) or directly
include the sample template, e.g.:

    <% include Autotoc %>

Specifying the content field
----------------------------

If you want to tocify a field different from *Content*, set the desired
name in the `content_field` property, e.g.:

    ContentController:
        content_field: 'Content' # Bogus: this is the default
    ProductPage_Controller:
        content_field: 'Details'
    AuthorHandler:
        content_field: 'Biography'

WARNING: the content field is not expected to be changed dynamically, so it
should be properly set **before** the first instantiation of the bound object.

Augmenting algorithm
--------------------

By default the HTML is augmented by setting the _id_ attribute directly on the
destination element (see `Tocifier::setId` for the exact implementation). Any
preexisting _id_ will be overwritten.

The old behavior instead was to inject anchors (`<a>` elements with the _id_
attribute but without _href_) just before the destination element. It is still
possible to enable it by changing the augment callback to
`Tocifier::prependAnchor`. Just add the following to your YAML config:

    eNTiDi\Autotoc\Autotoc:
        augment_callback: eNTiDi\Autotoc\Tocifier::prependAnchor

You can leverage this option to run your own callbacks too, e.g.:

    eNTiDi\Autotoc\Autotoc:
        augment_callback: 'Page::defaultCallback'
    Page:
        augment_callback: 'Page::specificCallback'

The callback receives three arguments: the main
[DOMDocument](http://php.net/manual/en/class.domdocument.php) instance, the 
[DOMElement](http://php.net/manual/en/class.domelement.php) to augment and a
string with the ID the element must refer to.

Excluding items from the TOC
----------------------------

If for some reasons you **do not** want to include some sections in the TOC,
just specify the `data-hide-from-toc` attribute, e.g.:

    <h2>First section</h2>
    <h2>Second section</h2>
    <h2 data-hide-from-toc>This section will be skipped</h2>
    <h2>Last section</h2>
