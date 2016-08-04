Usage
-----

`silverstripe-autotoc` is basically a [SilverStripe 3](http://www.silverstripe.org/)
module that extends a controller (if the CMS is present the
[ContentController](http://api.silverstripe.org/3.0/class-ContentController.html)
will be extended out of the box) to provide:

* the *$Autotoc* tag, containing the table of contents dynamically
  created from the content of the current page. The tree is provided as
  a mixture of
  [ArrayData](http://api.silverstripe.org/3.0/class-ArrayData.html) and
  [ArrayList](http://api.silverstripe.org/3.0/class-ArrayList.html),
  ready to be consumed by templates.
* the override of the subject field (i.e. *$Content* by default) that
  will be augmented to add proper destinations for the links.

You will need to modify your templates for embedding the *$Autotoc* tag
(see [AutoTOC format](format.md) for the gory details) or directly
include the sample template, e.g.:

    <% include Autotoc %>

If you want to tocify a field different from *Content*, set the desired
name in the `content_field` property of a YAML config file, e.g.:

    ContentController:
        content_field: 'Content' # Bogus: this is the default
    ProductPage_Controller:
        content_field: 'Details'
    AuthorHandler:
        content_field: 'Biography'

The *$Autotoc* tag will automatically become available in the above
controllers.

By default the HTML is augmented with anchors (`<a>` elements with the
_id_ attribute but without _href_) prepended to the destination
elements. See `Tocifier::prependAnchor` for the exact implementation.

This is kept mainly for backward compatibility. A better approach would
be to directly set the id of the destination element. If you want to
enable this behavior, just change the augment callback to
`Tocifier::setId` by adding the following to your YAML config:

    Tocifier:
        augment_callback: [ Tocifier, setId ]

You can leverage this option to enable your own callbacks too.
