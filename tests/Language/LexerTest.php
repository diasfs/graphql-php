<?php
namespace GraphQL\Tests\Language;

use GraphQL\Language\Lexer;
use GraphQL\Language\Source;
use GraphQL\Language\SourceLocation;
use GraphQL\Language\Token;
use GraphQL\Error\SyntaxError;
use GraphQL\Utils\Utils;

class LexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @it disallows uncommon control characters
     */
    public function testDissallowsUncommonControlCharacters()
    {
        $this->expectSyntaxError(
            Utils::chr(0x0007),
            'Cannot contain the invalid character "\u0007"',
            $this->loc(1, 1)
        );
    }

    /**
     * @it accepts BOM header
     */
    public function testAcceptsBomHeader()
    {
        $bom = Utils::chr(0xFEFF);
        $expected = [
            'kind' => Token::NAME,
            'start' => 2,
            'end' => 5,
            'value' => 'foo'
        ];

        $this->assertArraySubset($expected, (array) $this->lexOne($bom . ' foo'));
    }

    /**
     * @it records line and column
     */
    public function testRecordsLineAndColumn()
    {
        $expected = [
            'kind' => Token::NAME,
            'start' => 8,
            'end' => 11,
            'line' => 4,
            'column' => 3,
            'value' => 'foo'
        ];
        $this->assertArraySubset($expected, (array) $this->lexOne("\n \r\n \r  foo\n"));
    }

    /**
     * @it skips whitespace and comments
     */
    public function testSkipsWhitespacesAndComments()
    {
        $example1 = '

    foo


';
        $expected = [
            'kind' => Token::NAME,
            'start' => 6,
            'end' => 9,
            'value' => 'foo'
        ];
        $this->assertArraySubset($expected, (array) $this->lexOne($example1));

        $example2 = '
    #comment
    foo#comment
';

        $expected = [
            'kind' => Token::NAME,
            'start' => 18,
            'end' => 21,
            'value' => 'foo'
        ];
        $this->assertArraySubset($expected, (array) $this->lexOne($example2));

        $expected = [
            'kind' => Token::NAME,
            'start' => 3,
            'end' => 6,
            'value' => 'foo'
        ];

        $example3 = ',,,foo,,,';
        $this->assertArraySubset($expected, (array) $this->lexOne($example3));
    }

    /**
     * @it errors respect whitespace
     */
    public function testErrorsRespectWhitespace()
    {
        $str = '' .
            "\n" .
            "\n" .
            "    ?\n" .
            "\n";

        try {
            $this->lexOne($str);
            $this->fail('Expected exception not thrown');
        } catch (SyntaxError $error) {
            $this->assertEquals(
                'Syntax Error: Cannot parse the unexpected character "?".' . "\n" .
                "\n" .
                "GraphQL request (3:5)\n" .
                "2: \n" .
                "3:     ?\n" .
                "       ^\n" .
                "4: \n",
                (string) $error
            );
        }
    }

    /**
     * @it updates line numbers in error for file context
     */
    public function testUpdatesLineNumbersInErrorForFileContext()
    {
        $str = '' .
            "\n" .
            "\n" .
            "     ?\n" .
            "\n";
        $source = new Source($str, 'foo.js', new SourceLocation(11, 12));

        try {
            $lexer = new Lexer($source);
            $lexer->advance();
            $this->fail('Expected exception not thrown');
        } catch (SyntaxError $error) {
            $this->assertEquals(
                'Syntax Error: Cannot parse the unexpected character "?".' . "\n" .
                "\n" .
                "foo.js (13:6)\n" .
                "12: \n" .
                "13:      ?\n" .
                "         ^\n" .
                "14: \n",
                (string) $error
            );
        }
    }

    public function testUpdatesColumnNumbersInErrorForFileContext()
    {
        $source = new Source('?', 'foo.js', new SourceLocation(1, 5));

        try {
            $lexer = new Lexer($source);
            $lexer->advance();
            $this->fail('Expected exception not thrown');
        } catch (SyntaxError $error) {
            $this->assertEquals(
                'Syntax Error: Cannot parse the unexpected character "?".' . "\n" .
                "\n" .
                "foo.js (1:5)\n" .
                '1:     ?' . "\n" .
                '       ^' . "\n",
                (string) $error
            );
        }
    }

    /**
     * @it lexes strings
     */
    public function testLexesStrings()
    {
        $this->assertArraySubset([
            'kind' => Token::STRING,
            'start' => 0,
            'end' => 8,
            'value' => 'simple'
        ], (array) $this->lexOne('"simple"'));


        $this->assertArraySubset([
            'kind' => Token::STRING,
            'start' => 0,
            'end' => 15,
            'value' => ' white space '
        ], (array) $this->lexOne('" white space "'));

        $this->assertArraySubset([
            'kind' => Token::STRING,
            'start' => 0,
            'end' => 10,
            'value' => 'quote "'
        ], (array) $this->lexOne('"quote \\""'));

        $this->assertArraySubset([
            'kind' => Token::STRING,
            'start' => 0,
            'end' => 25,
            'value' => 'escaped \n\r\b\t\f'
        ], (array) $this->lexOne('"escaped \\\\n\\\\r\\\\b\\\\t\\\\f"'));

        $this->assertArraySubset([
            'kind' => Token::STRING,
            'start' => 0,
            'end' => 16,
            'value' => 'slashes \\ \/'
        ], (array) $this->lexOne('"slashes \\\\ \\\\/"'));

        $this->assertArraySubset([
            'kind' => Token::STRING,
            'start' => 0,
            'end' => 13,
            'value' => 'unicode яуц'
        ], (array) $this->lexOne('"unicode яуц"'));

        $unicode = json_decode('"\u1234\u5678\u90AB\uCDEF"');
        $this->assertArraySubset([
            'kind' => Token::STRING,
            'start' => 0,
            'end' => 34,
            'value' => 'unicode ' . $unicode
        ], (array) $this->lexOne('"unicode \u1234\u5678\u90AB\uCDEF"'));

        $this->assertArraySubset([
            'kind' => Token::STRING,
            'start' => 0,
            'end' => 26,
            'value' => $unicode
        ], (array) $this->lexOne('"\u1234\u5678\u90AB\uCDEF"'));
    }

    /**
     * @it lexes block strings
     */
    public function testLexesBlockString()
    {
        $this->assertArraySubset([
            'kind' => Token::BLOCK_STRING,
            'start' => 0,
            'end' => 12,
            'value' => 'simple'
        ], (array) $this->lexOne('"""simple"""'));

        $this->assertArraySubset([
            'kind' => Token::BLOCK_STRING,
            'start' => 0,
            'end' => 19,
            'value' => ' white space '
        ], (array) $this->lexOne('""" white space """'));

        $this->assertArraySubset([
            'kind' => Token::BLOCK_STRING,
            'start' => 0,
            'end' => 22,
            'value' => 'contains " quote'
        ], (array) $this->lexOne('"""contains " quote"""'));

        $this->assertArraySubset([
            'kind' => Token::BLOCK_STRING,
            'start' => 0,
            'end' => 31,
            'value' => 'contains """ triplequote'
        ], (array) $this->lexOne('"""contains \\""" triplequote"""'));

        $this->assertArraySubset([
            'kind' => Token::BLOCK_STRING,
            'start' => 0,
            'end' => 16,
            'value' => "multi\nline"
        ], (array) $this->lexOne("\"\"\"multi\nline\"\"\""));

        $this->assertArraySubset([
            'kind' => Token::BLOCK_STRING,
            'start' => 0,
            'end' => 28,
            'value' => "multi\nline\nnormalized"
        ], (array) $this->lexOne("\"\"\"multi\rline\r\nnormalized\"\"\""));

        $this->assertArraySubset([
            'kind' => Token::BLOCK_STRING,
            'start' => 0,
            'end' => 32,
            'value' => 'unescaped \\n\\r\\b\\t\\f\\u1234'
        ], (array) $this->lexOne('"""unescaped \\n\\r\\b\\t\\f\\u1234"""'));

        $this->assertArraySubset([
            'kind' => Token::BLOCK_STRING,
            'start' => 0,
            'end' => 19,
            'value' => 'slashes \\\\ \\/'
        ], (array) $this->lexOne('"""slashes \\\\ \\/"""'));

        $this->assertArraySubset([
            'kind' => Token::BLOCK_STRING,
            'start' => 0,
            'end' => 68,
            'value' => "spans\n  multiple\n    lines"
        ], (array) $this->lexOne("\"\"\"

        spans
          multiple
            lines

        \"\"\""));
    }

    public function reportsUsefulStringErrors() {
        return [
            ['"', "Unterminated string.", $this->loc(1, 2)],
            ['"no end quote', "Unterminated string.", $this->loc(1, 14)],
            ["'single quotes'", "Unexpected single quote character ('), did you mean to use a double quote (\")?", $this->loc(1, 1)],
            ['"contains unescaped \u0007 control char"', "Invalid character within String: \"\\u0007\"", $this->loc(1, 21)],
            ['"null-byte is not \u0000 end of file"', 'Invalid character within String: "\\u0000"', $this->loc(1, 19)],
            ['"multi' . "\n" . 'line"', "Unterminated string.", $this->loc(1, 7)],
            ['"multi' . "\r" . 'line"', "Unterminated string.", $this->loc(1, 7)],
            ['"bad \\z esc"', "Invalid character escape sequence: \\z", $this->loc(1, 7)],
            ['"bad \\x esc"', "Invalid character escape sequence: \\x", $this->loc(1, 7)],
            ['"bad \\u1 esc"', "Invalid character escape sequence: \\u1 es", $this->loc(1, 7)],
            ['"bad \\u0XX1 esc"', "Invalid character escape sequence: \\u0XX1", $this->loc(1, 7)],
            ['"bad \\uXXXX esc"', "Invalid character escape sequence: \\uXXXX", $this->loc(1, 7)],
            ['"bad \\uFXXX esc"', "Invalid character escape sequence: \\uFXXX", $this->loc(1, 7)],
            ['"bad \\uXXXF esc"', "Invalid character escape sequence: \\uXXXF", $this->loc(1, 7)],
        ];
    }

    /**
     * @dataProvider reportsUsefulStringErrors
     * @it lex reports useful string errors
     */
    public function testLexReportsUsefulStringErrors($str, $expectedMessage, $location)
    {
        $this->expectSyntaxError($str, $expectedMessage, $location);
    }

    public function reportsUsefulBlockStringErrors() {
        return [
            ['"""', "Unterminated string.", $this->loc(1, 4)],
            ['"""no end quote', "Unterminated string.", $this->loc(1, 16)],
            ['"""contains unescaped ' . json_decode('"\u0007"') . ' control char"""', "Invalid character within String: \"\\u0007\"", $this->loc(1, 23)],
            ['"""null-byte is not ' . json_decode('"\u0000"') . ' end of file"""', "Invalid character within String: \"\\u0000\"", $this->loc(1, 21)],
        ];
    }

    /**
     * @dataProvider reportsUsefulBlockStringErrors
     * @it lex reports useful block string errors
     */
    public function testReportsUsefulBlockStringErrors($str, $expectedMessage, $location)
    {
        $this->expectSyntaxError($str, $expectedMessage, $location);
    }

    /**
     * @it lexes numbers
     */
    public function testLexesNumbers()
    {
        $this->assertArraySubset(
            ['kind' => Token::INT, 'start' => 0, 'end' => 1, 'value' => '4'],
            (array) $this->lexOne('4')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 5, 'value' => '4.123'],
            (array) $this->lexOne('4.123')
        );
        $this->assertArraySubset(
            ['kind' => Token::INT, 'start' => 0, 'end' => 2, 'value' => '-4'],
            (array) $this->lexOne('-4')
        );
        $this->assertArraySubset(
            ['kind' => Token::INT, 'start' => 0, 'end' => 1, 'value' => '9'],
            (array) $this->lexOne('9')
        );
        $this->assertArraySubset(
            ['kind' => Token::INT, 'start' => 0, 'end' => 1, 'value' => '0'],
            (array) $this->lexOne('0')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 6, 'value' => '-4.123'],
            (array) $this->lexOne('-4.123')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 5, 'value' => '0.123'],
            (array) $this->lexOne('0.123')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 5, 'value' => '123e4'],
            (array) $this->lexOne('123e4')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 5, 'value' => '123E4'],
            (array) $this->lexOne('123E4')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 6, 'value' => '123e-4'],
            (array) $this->lexOne('123e-4')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 6, 'value' => '123e+4'],
            (array) $this->lexOne('123e+4')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 8, 'value' => '-1.123e4'],
            (array) $this->lexOne('-1.123e4')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 8, 'value' => '-1.123E4'],
            (array) $this->lexOne('-1.123E4')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 9, 'value' => '-1.123e-4'],
            (array) $this->lexOne('-1.123e-4')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 9, 'value' => '-1.123e+4'],
            (array) $this->lexOne('-1.123e+4')
        );
        $this->assertArraySubset(
            ['kind' => Token::FLOAT, 'start' => 0, 'end' => 11, 'value' => '-1.123e4567'],
            (array) $this->lexOne('-1.123e4567')
        );
    }

    public function reportsUsefulNumberErrors()
    {
        return [
            [ '00', "Invalid number, unexpected digit after 0: \"0\"", $this->loc(1, 2)],
            [ '+1', "Cannot parse the unexpected character \"+\".", $this->loc(1, 1)],
            [ '1.', "Invalid number, expected digit but got: <EOF>", $this->loc(1, 3)],
            [ '1.e1', "Invalid number, expected digit but got: \"e\"", $this->loc(1, 3)],
            [ '.123', "Cannot parse the unexpected character \".\".", $this->loc(1, 1)],
            [ '1.A', "Invalid number, expected digit but got: \"A\"", $this->loc(1, 3)],
            [ '-A', "Invalid number, expected digit but got: \"A\"", $this->loc(1, 2)],
            [ '1.0e', "Invalid number, expected digit but got: <EOF>", $this->loc(1, 5)],
            [ '1.0eA', "Invalid number, expected digit but got: \"A\"", $this->loc(1, 5)],
        ];
    }

    /**
     * @dataProvider reportsUsefulNumberErrors
     * @it lex reports useful number errors
     */
    public function testReportsUsefulNumberErrors($str, $expectedMessage, $location)
    {
        $this->expectSyntaxError($str, $expectedMessage, $location);
    }

    /**
     * @it lexes punctuation
     */
    public function testLexesPunctuation()
    {
        $this->assertArraySubset(
            ['kind' => Token::BANG, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne('!')
        );
        $this->assertArraySubset(
            ['kind' => Token::DOLLAR, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne('$')
        );
        $this->assertArraySubset(
            ['kind' => Token::PAREN_L, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne('(')
        );
        $this->assertArraySubset(
            ['kind' => Token::PAREN_R, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne(')')
        );
        $this->assertArraySubset(
            ['kind' => Token::SPREAD, 'start' => 0, 'end' => 3, 'value' => null],
            (array) $this->lexOne('...')
        );
        $this->assertArraySubset(
            ['kind' => Token::COLON, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne(':')
        );
        $this->assertArraySubset(
            ['kind' => Token::EQUALS, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne('=')
        );
        $this->assertArraySubset(
            ['kind' => Token::AT, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne('@')
        );
        $this->assertArraySubset(
            ['kind' => Token::BRACKET_L, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne('[')
        );
        $this->assertArraySubset(
            ['kind' => Token::BRACKET_R, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne(']')
        );
        $this->assertArraySubset(
            ['kind' => Token::BRACE_L, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne('{')
        );
        $this->assertArraySubset(
            ['kind' => Token::PIPE, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne('|')
        );
        $this->assertArraySubset(
            ['kind' => Token::BRACE_R, 'start' => 0, 'end' => 1, 'value' => null],
            (array) $this->lexOne('}')
        );
    }

    public function reportsUsefulUnknownCharErrors()
    {
        $unicode1 = json_decode('"\u203B"');
        $unicode2 = json_decode('"\u200b"');

        return [
            ['..', "Cannot parse the unexpected character \".\".", $this->loc(1, 1)],
            ['?', "Cannot parse the unexpected character \"?\".", $this->loc(1, 1)],
            [$unicode1, "Cannot parse the unexpected character \"\\u203b\".", $this->loc(1, 1)],
            [$unicode2, "Cannot parse the unexpected character \"\\u200b\".", $this->loc(1, 1)],
        ];
    }

    /**
     * @dataProvider reportsUsefulUnknownCharErrors
     * @it lex reports useful unknown character error
     */
    public function testReportsUsefulUnknownCharErrors($str, $expectedMessage, $location)
    {
        $this->expectSyntaxError($str, $expectedMessage, $location);
    }

    /**
     * @it lex reports useful information for dashes in names
     */
    public function testReportsUsefulDashesInfo()
    {
        $q = 'a-b';
        $lexer = new Lexer(new Source($q));
        $this->assertArraySubset(['kind' => Token::NAME, 'start' => 0, 'end' => 1, 'value' => 'a'], (array) $lexer->advance());

        $this->setExpectedException(SyntaxError::class, 'Syntax Error: Invalid number, expected digit but got: "b"');
        try {
            $lexer->advance();
            $this->fail('Expected exception not thrown');
        } catch(SyntaxError $error) {
            $this->assertEquals([$this->loc(1,3)], $error->getLocations());
            throw $error;
        }
    }

    /**
     * @it produces double linked list of tokens, including comments
     */
    public function testDoubleLinkedList()
    {
        $lexer = new Lexer(new Source('{
      #comment
      field
    }'));

        $startToken = $lexer->token;
        do {
            $endToken = $lexer->advance();
            // Lexer advances over ignored comment tokens to make writing parsers
            // easier, but will include them in the linked list result.
            $this->assertNotEquals('Comment', $endToken->kind);
        } while ($endToken->kind !== '<EOF>');

        $this->assertEquals(null, $startToken->prev);
        $this->assertEquals(null, $endToken->next);

        $tokens = [];
        for ($tok = $startToken; $tok; $tok = $tok->next) {
            if (!empty($tokens)) {
                // Tokens are double-linked, prev should point to last seen token.
                $this->assertSame($tokens[count($tokens) - 1], $tok->prev);
            }
            $tokens[] = $tok;
        }

        $this->assertEquals([
            '<SOF>',
            '{',
            'Comment',
            'Name',
            '}',
            '<EOF>'
        ], Utils::map($tokens, function ($tok) {
            return $tok->kind;
        }));
    }

    /**
     * @param string $body
     * @return Token
     */
    private function lexOne($body)
    {
        $lexer = new Lexer(new Source($body));
        return $lexer->advance();
    }

    private function loc($line, $column)
    {
        return new SourceLocation($line, $column);
    }

    private function expectSyntaxError($text, $message, $location)
    {
        $this->setExpectedException(SyntaxError::class, $message);
        try {
            $this->lexOne($text);
        } catch (SyntaxError $error) {
            $this->assertEquals([$location], $error->getLocations());
            throw $error;
        }
    }
}
