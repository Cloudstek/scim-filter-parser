# Operator

[Enumeration](https://github.com/Cloudstek/php-enum) of the different supported [Comparison](./comparison.md) operators.

The list and meaning of the operators supported by default can be found in the [RFC here](https://tools.ietf.org/html/rfc7644#section-3.4.2.2).

### Custom filter operations

The [SCIM RFC](https://tools.ietf.org/html/rfc7644#section-3.4.2.2) notes that providers may support additional filter operations. Currently there is no easy way of implementing these without adding some code yourself. This might change in the future though.

To support a custom operator you will need to extend the Operator enum to add your own operators, then extend the [Comparison](./comparison.md) class to support your own operator enum and last extend the [Parser](../parsers.md) to override the `parseComparison` method.

##### MyOperator.php

<!-- {.file-heading} -->

Extend the Operator enum class to add your own operators. Make sure to keep the class `@method` docblocks to have your IDE autocomplete the methods. For more information about the enumeration implementation, [see its documentation](https://github.com/Cloudstek/php-enum).

```php
<?php

namespace App\AST;

use Cloudstek\SCIM\FilterParser\AST\Operator;

/**
 * My comparison operator.
 *
 * @method static static EQ()
 * @method static static NE()
 * @method static static CO()
 * @method static static SW()
 * @method static static EW()
 * @method static static GT()
 * @method static static LT()
 * @method static static GE()
 * @method static static LE()
 * @method static static PR()
 * 
 * @method static static REGEX()
 */
class MyOperator extends Operator
{
    // Custom 'regex' operator.
    private const REGEX = 'regex';
}
```

##### MyComparison.php
<!-- {.file-heading} -->

Extend the [Comparison](./comparison.md) class to add support for your own operator enum (it must be an enum).

```php
<?php

namespace App\AST;

use Cloudstek\SCIM\FilterParser\AST\AttributePath;
use Cloudstek\SCIM\FilterParser\AST\Comparison;
use Cloudstek\SCIM\FilterParser\AST\Node;

class MyComparison extends Comparison
{
    /**
     * My comparison.
     *
     * @param AttributePath              $attributePath
     * @param string|MyOperator          $operator
     * @param bool|float|int|string|null $value
     * @param Node|null                  $parent
     *
     * @throws \UnexpectedValueException On invalid operator.
     */
    public function __construct(AttributePath $attributePath, $operator, $value, ?Node $parent = null)
    {
        $myOperator = MyOperator::get($operator);

        parent::__construct($attributePath, $myOperator, $value, $parent);
    }
}
```

##### MyFilterParser.php

<!-- {.file-heading} -->

Extend either the [FilterParser](../parsers.md) or [PathParser](../parsers.md) and override the `parseComparison` method. Before we do anything we save the current position of the stream so when we decide not to handle this comparison, we can reset the stream back to its original position and pass it back to our parent parser class.

```php
<?php

namespace App;

use App\AST\MyComparison;
use Cloudstek\SCIM\FilterParser\AST;
use Nette\Tokenizer;

class MyFilterParser extends FilterParser
{
    protected function parseComparison(Tokenizer\Stream $stream, AST\AttributePath $attributePath)
    {
        // Save position for if we don't handle this comparison.
        $position = $stream->position;

        // Read the operator from the stream
        $operator = trim($stream->consumeValue(self::T_STRING, self::T_COMP_OP));
        $value = null;

        // It's a regex, do stuff!
        if (strcasecmp($operator, 'regex') === 0) {
            $value = $stream->consumeToken(self::T_STRING);

            return new MyComparison($attributePath, $operator, $value);
        }

        // It's not a regex, let a parent handle the faults. But first, reset the stream position like we weren't here.
        $stream->position = $position;

        return parent::parseComparison($stream, $attributePath);
    }
}
```

