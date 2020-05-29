# Parsers

Technically this library is called SCIM Filter Parser but as the code for parsing paths is so similar to parsing filters, it made sense to include both parsers in one library. Besides, you'll likely use them both anyway.

These parsers will take a string and output an [AST](./ast/README.md). It is then up to you to work your way through the AST and for example build your database query.

## Filter Parser

> Cloudstek\SCIM\FilterParser\FilterParser

Parses [filter strings](https://tools.ietf.org/html/rfc7644#section-3.4.2.2) used to filter results when querying resources. In attempt to parse a path (e.g. `foo.bar`) instead of a filter (`foo.bar eq "baz"`) this parser will throw an exception. To parse paths, use the path parser instead.

#### Example

_Please be aware that this is just an example and not to be used anywhere. Building an SQL query like this completely unsafe, but again here just for demonstration purposes._

```php
<?php

use Cloudstek\SCIM\FilterParser\FilterParser;
use Cloudstek\SCIM\FilterParser\AST;

// Map attribute paths to column names.
$columnMap = [
    'name.familyName' => 'family_name'
];

// Create the filter parser.
$filterParser = new FilterParser();

$comparison = $filterParser->parse('name.familyName sw "J"'); 
// -> AST\Comparison

$column = $columnMap[(string)$comparison->getAttributePath()];
// 'name.familyName' -> 'family_name'

$value = $comparison->getValue();
// -> 'J'

$sql = 'SELECT * FROM person WHERE '.$column.' ';

switch ($comparison->getOperator()) {
    case AST\Operator::EQ:
        $sql .= sprintf(' = "%s"', $value);
        // -> = "J"
        break;
    case AST\Operator::SW:
        $sql .= sprintf(' LIKE "%%%s"', $value);
        // -> LIKE "%J"
        break;
    // ...
}

// name.familyName sw "J" 
//  -> SELECT * FROM person WHERE family_name LIKE "%J"

```

## Path Parser

> Cloudstek\SCIM\FilterParser\PathParser

Different from the filter parser, the path parser only supports parsing attribute and value paths as used in [`PATCH` requests](https://tools.ietf.org/html/rfc7644#section-3.5.2). Attempts to parse filters (e.g. `foo eq "bar"`) will result in an exception being thrown.

In addition, value paths (e.g. `members[value eq "foo"]`) can now reference a sub attribute: `members[value eq "foo"].displayName`, which isn't allowed in filters.

#### Example

```php
<?php

use Cloudstek\SCIM\FilterParser\PathParser;

// Create the path parser.
$pathParser = new PathParser();

$attributePath = $pathParser->parse('name.familyName'); 
// -> AST\AttributePath

// Get attribute names
$names = $attributePath->getNames();
// -> ['name', 'familyName']

// Or use $attributePath as array directly.
$firstAttribute = $attributePath[0]; 
// -> name
$lastAttribute = $attributePath[1];
// -> familyName

$valuePath = $pathParser->parse('name[given eq "John"].familyName'); 
// -> AST\ValuePath
```
