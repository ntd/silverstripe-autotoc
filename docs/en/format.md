AutoTOC format
--------------

The *$Autotoc* tag is a tree that can be represented with the following
pseudocode:

    $Autotoc = ArrayData( $Children <- <CHILDREN> )
    <CHILDREN> = ArrayList( <ITEM> )
    <ITEM> = ArrayData( $Id, $Title [, $Children <- <CHILDREN> ] )

In a more SilverStripe way, this can be seen as:

    $Autotoc
        $Children[]
            $Id
            $Title
            $Children[]

`Autotoc.ss` and `AutotocItem.ss` show a way to render the whole table of
contents by leveraging mutual recursion between two templates.  The format used
there is intentionally compatible with the
[Bootstrap navlist](http://getbootstrap.com/components/#nav) component,
so it can be used and it will be properly handled by the
[Silverstrap](http://dev.entidi.com/p/silverstrap/) theme.
