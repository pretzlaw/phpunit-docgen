<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XPathTest.php
 *
 * LICENSE: This source file is created by the company around Mike Pretzlaw
 * located in Germany also known as rmp-up. All its contents are proprietary
 * and under german copyright law. Consider this file as closed source and/or
 * without the permission to reuse or modify its contents.
 * This license is available through the world-wide-web at the following URI:
 * https://mike-pretzlaw.de/license-generic.txt . If you did not receive a copy
 * of the license and are unable to obtain it through the web, please send a
 * note to mail@mike-pretzlaw.de so we can mail you a copy.
 *
 * @package    phpunit-docgen
 * @copyright  2019 Mike Pretzlaw
 * @license    https://mike-pretzlaw.de/license-generic.txt
 * @link       https://project.mike-pretzlaw.de/phpunit-docgen
 * @since      2019-06-09
 */

declare(strict_types=1);

namespace Pretzlaw\PHPUnit\DocGen\Test\DocComment;

use Pretzlaw\PHPUnit\DocGen\DocComment\Comment;
use Pretzlaw\PHPUnit\DocGen\Test\TestCase;

/**
 * XPathTest
 *
 * First enum:
 *
 * * Some
 * * bullets
 *
 * Second enum:
 *
 * * Another one
 * * Bites
 * * The Dust
 *
 * Numbers:
 *
 * 1. Uno
 * 2. Dos
 * 3. Drei
 * 4. Four
 *
 * Goodbye
 *
 * > Okay.
 *
 * Exchange 61 with 16 because 15 is Magna Charta.
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-06-09
 */
class XPathTest extends TestCase
{
    /**
     * @var Comment
     */
    private $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->comment = $this->classComment();
    }

    public function testFetchMultiple()
    {
        $elements = $this->comment->xpath('//ul');

        static::assertCount(2, $elements);
    }

    public function testFetchSingle()
    {
        $element = $this->comment->xpath('//ul', 1);

        static::assertCount(3, $element->children());
    }

    public function testFetchInvalid()
    {
        static::assertFalse($this->comment->xpath('//ul', 5));
    }
}