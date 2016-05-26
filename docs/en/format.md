AutoTOC format
--------------

The *$Autotoc* tag is a tree that can be represented with the following
pseudocode:

    $Autotoc = ArrayData( $Children <- <CHILDREN> )
    <CHILDREN> = ArrayList( <ITEM> )
    <ITEM> = ArrayData( $Id, $Title [, $Children <- <CHILDREN> ] )

In a more SilverStripe template way, this can be seen as:

    $Autotoc
        $Children[]
            $Id
            $Title
            $Children[]

The `Autotoc.ss` and `AutotocItem.ss` show a way to represent the whole
table of content tree in a recursive way. The format used there is
intentionally compatible with the
[Bootstrap navlist](http://getbootstrap.com/components/#nav) component,
so it can be used and it will be properly handled by the
[Silverstrap](http://dev.entidi.com/p/silverstrap/) theme.
