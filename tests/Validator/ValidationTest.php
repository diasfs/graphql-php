<?php
namespace GraphQL\Tests\Validator;

class ValidationTest extends TestCase
{
    // Validate: Supports full validation

    /**
     * @it validates queries
     */
    public function testValidatesQueries()
    {
        $this->expectPassesCompleteValidation('
          query {
            catOrDog {
              ... on Cat {
                furColor
              }
              ... on Dog {
                isHousetrained
              }
            }
          }
        ');
    }

    /**
     * @it detects bad scalar parse
     */
    public function testDetectsBadScalarParse()
    {
        $doc = '
      query {
        invalidArg(arg: "bad value")
      }
        ';

        $expectedError = [
            'message' => "Expected type Invalid, found \"bad value\"; Invalid scalar is always invalid: bad value",
            'locations' => [ ['line' => 3, 'column' => 25] ]
        ];

        $this->expectInvalid(
            $this->getTestSchema(),
            null,
            $doc,
            [$expectedError]
        );
    }

    public function testPassesValidationWithEmptyRules()
    {
        $query = '{invalid}';

        $expectedError = [
            'message' => 'Cannot query field "invalid" on type "QueryRoot". Did you mean "invalidArg"?',
            'locations' => [ ['line' => 1, 'column' => 2] ]
        ];
        $this->expectFailsCompleteValidation($query, [$expectedError]);
        $this->expectValid($this->getTestSchema(), [], $query);
    }
}
