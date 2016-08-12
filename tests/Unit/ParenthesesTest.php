<?php

namespace RickTap\Qriteria\Tests\Unit;

use RickTap\Qriteria\Support\Parentheses;

class ParenthesesTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function itCanValidateTheBalanceOfParentheses()
    {
        $this->assertTrue(Parentheses::isBalanced('()'));
        $this->assertTrue(Parentheses::isBalanced('()()()'));
    }

    /** @test */
    public function itCanValidateTheBalanceOfNestedParentheses()
    {
        $this->assertTrue(Parentheses::isBalanced('( () )'));
        $this->assertTrue(Parentheses::isBalanced('( () () )'));
        $this->assertTrue(Parentheses::isBalanced('(()()())'));
    }

    /** @test */
    public function itIgnoresAdditionalCharacters()
    {
        $this->assertTrue(Parentheses::isBalanced('a(sd(asd),a/d(a\(ads)sd))'));
    }

    /** @test */
    public function itValidatesTooManyClosingBraces()
    {
        $this->assertFalse(Parentheses::isBalanced('())'));
        $this->assertFalse(Parentheses::isBalanced('(()) )'));
    }

    /** @test */
    public function itValidatesTooManyOpeningBraces()
    {
        $this->assertFalse(Parentheses::isBalanced('('));
        $this->assertFalse(Parentheses::isBalanced('(()'));
        $this->assertFalse(Parentheses::isBalanced('(()(())'));
    }

    /** @test */
    public function itValidatesTheWrongOrderOfBraces()
    {
        $this->assertFalse(Parentheses::isBalanced(')('));
        $this->assertFalse(Parentheses::isBalanced('())('));
    }
}
