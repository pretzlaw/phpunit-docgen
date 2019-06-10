<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ToHtmlTest.php
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

use Pretzlaw\PHPUnit\DocGen\Test\TestCase;

/**
 * Create markdown
 *
 * This part should be turned into markdown.
 *
 * It contains an enumeration:
 *
 * 1. One
 * 2. Two
 * 3. Four
 *
 * And an itemization:
 *
 * * Itemi
 * * zation
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-06-09
 */
class ToMarkdownTest extends TestCase
{
    private static function assertClassComment(string $markdown)
    {
        static::assertStringStartsWith('# Create markdown', $markdown);
        static::assertStringEndsWith('* zation', $markdown);
    }

    public function testCreateMarkdownFromClass()
    {
        static::assertClassComment($this->classComment(__CLASS__)->markdown());
    }

    public function testCreatesMarkdownWithoutExplicitClassName()
    {
        static::assertClassComment($this->classComment()->markdown());
    }

    /**
     * This comment has no header
     * and directly starts with content.
     */
    public function testCreatesMarkdownFromMethodName()
    {
        $markdown = $this->methodComment(__METHOD__)->markdown();

        static::assertStringStartsWith('This comment has no header', $markdown);
        static::assertStringEndsWith('with content.', $markdown);
    }

    /**
     * Explicit
     *
     * And some body here.
     */
    public function testCreatesMarkdownFromExplizitMethodName()
    {
        $markdown = $this->methodComment(__CLASS__, __FUNCTION__)->markdown();

        static::assertStringStartsWith('# Explicit', $markdown);
        static::assertStringEndsWith('body here.', $markdown);
    }

    /**
     * Just heading
     */
    public function testCreatesMarkdownFromComment()
    {
        static::assertEquals('# Just heading', $this->comment()->markdown());
    }
}