silverstripe-autotoc
====================

Generate the table of contents dynamically from *$Content*.

This is basically a [SilverStripe 3](http://www.silverstripe.org/)
module that extends the
[ContentController](http://api.silverstripe.org/3.0/class-ContentController.html)
class to provide:

* the new *$Autotoc* tag, containing the table of contents dynamically
  created from the content of the current page. The tree is provided as
  a mixture of
  [ArrayData](http://api.silverstripe.org/3.0/class-ArrayData.html) and
  [ArrayList](http://api.silverstripe.org/3.0/class-ArrayList.html),
  ready to be consumed by templates.
* overriding of the standard *$Content* tag, augmenting it with anchors
  (`<a>` elements with the _id_ attribute but without _href_) that adds
  proper destination targets to the links in *$Autotoc*.

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
intentionally compatible with the [Bootstrap](http://twitter.github.io/bootstrap/)
[navlist](http://twitter.github.io/bootstrap/components.html#navs)
components, so it can be used and it will be properly handled by the
[Silverstrap](http://dev.entidi.com/p/silverstrap/) theme.

Support
-------

For bug report or feature requests, go to the dedicated [development
tracker](http://dev.entidi.com/p/silverstripe-autotoc/).
