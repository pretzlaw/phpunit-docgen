<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ExecuteCodeTest.php
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
 * @since      2019-06-10
 */

declare(strict_types=1);

namespace Pretzlaw\PHPUnit\DocGen\Test\DocComment;

use Pretzlaw\PHPUnit\DocGen\Test\TestCase;

/**
 * ExecuteCodeTest
 *
 * This is some simple code returning an `integer`:
 *
 * ```php
 * <?php
 *
 * return 1337;
 * ```
 *
 * But this is missing an opener:
 *
 * ```php
 * return ['no', 'opener'];
 * ```
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-06-10
 */
class ExecuteCodeTest extends TestCase
{
    public function testExecuteCodeWithOpener()
    {
        $value = $this->classComment()->execute('//pre/code', 0);

        static::assertSame(1337, $value);
    }

    public function testCodeWithoutOpener()
    {
        static::assertSame('return [\'no\', \'opener\'];', trim($this->classComment()->execute(1)));
    }
}