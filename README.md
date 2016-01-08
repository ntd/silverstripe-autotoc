silverstripe-autotoc
====================

Generate the table of contents dynamically from *$Content* or from any
HTML field specified in the `content_field` YAML property of a
controller.

This is basically a [SilverStripe 3](http://www.silverstripe.org/)
module that extends the controller (if the cms is present, by default
[ContentController](http://api.silverstripe.org/3.0/class-ContentController.html)
is extended) to provide:

* the new *$Autotoc* tag, containing the table of contents dynamically
  created from the content of the current page. The tree is provided as
  a mixture of
  [ArrayData](http://api.silverstripe.org/3.0/class-ArrayData.html) and
  [ArrayList](http://api.silverstripe.org/3.0/class-ArrayList.html),
  ready to be consumed by templates.
* overriding of the specified field (default to *$Content*), augmenting
  it with anchors (`<a>` elements with the _id_ attribute but without
  _href_) that adds proper destinations to the links in *$Autotoc*.

Installation
------------

To install silverstripe-autotoc you should proceed as usual: unpack or
copy the directory tree inside your SilverStripe root directory and do a
`?flush`.

If you use [composer](https://getcomposer.org/), you could just use the
following command instead:

    composer require entidi/silverstripe-autotoc

Usage
-----

Modify your templates to include the newly introduced *$Autotoc* tag
(see the next section for the gory details) or directly include the
default template, e.g.:

    <% include Autotoc %>

If you want to use a field different from *Content*, set the desired
name in the `content_field` property of a YAML config file, e.g.:

    ContentController:
        content_field: 'Content' # Bogus: this is the default
    ProductPage_Controller:
        content_field: 'Details'
    AuthorHandler:
        content_field: 'Biography'

Autotoc format
--------------

The *$Autotoc* is a tree that can be represented with the following
pseudo representation:

    $Autotoc = ArrayData( $Children <- <CHILDREN> )
    <CHILDREN> = ArrayList( <ITEM> )
    <ITEM> = ArrayData( $Id, $Title [, $Children <- <CHILDREN> ] )

In a more SilverStripe template way, this can be seen as:

    $Autotoc
        $Children[]
            $Id
            $Title
            $Children[]

The `Autotoc.ss` and `AutotocItem.ss` shows a way to represent the whole
table of content tree in a recursive way. The format used is
intentionally compatible with the [Bootstrap](http://getbootstrap.com/)
[navlist](http://getbootstrap.com/components/#nav) components, so it can
be used and it will be properly handled by the
[Silverstrap](http://dev.entidi.com/p/silverstrap/) theme.

Support
-------

This project has been developed by [ntd](mailto:ntd@entidi.it). Its
[home page](http://silverstripe.entidi.com/) is shared by other
[SilverStripe](http://www.silverstripe.org/) modules and themes.

To check out the code, report issues or propose enhancements, go to the
[dedicated tracker](http://dev.entidi.com/p/silverstripe-autotoc).
Alternatively, you can do the same things by leveraging the official
[github repository](https://github.com/ntd/silverstripe-autotoc).
