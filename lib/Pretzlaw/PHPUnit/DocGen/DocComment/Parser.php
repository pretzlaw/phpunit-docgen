<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * DocQuery.php
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

namespace Pretzlaw\PHPUnit\DocGen\DocComment;

use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;

/**
 * DocQuery
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-06-09
 */
trait Parser
{
    private $docBlockFactory;

    public function classComment($className = null): Comment
    {
        if (null === $className) {
            $className = get_class($this);
        }

        $reflection = new \ReflectionClass($className);

        return new Comment($this->docBlockFactory()->create((string)$reflection->getDocComment()));
    }

    public function comment(): Comment
    {
        return $this->methodComment(get_class($this), $this->getName());
    }

    public function methodComment($classOrMethodName, $method = null): Comment
    {
        if (null === $method) {
            $parts = explode('::', $classOrMethodName);

            $classOrMethodName = get_class($this);
            $method = array_pop($parts);

            if ($parts) {
                $classOrMethodName = array_pop($parts);
            }
        }

        $reflection = new ReflectionClass($classOrMethodName);
        $method = $reflection->getMethod($method);

        return new Comment($this->docBlockFactory()->create((string)$method->getDocComment()));
    }


    private function docBlockFactory(): DocBlockFactory
    {
        if (null === $this->docBlockFactory) {
            $this->docBlockFactory = DocBlockFactory::createInstance();
        }

        return $this->docBlockFactory;
    }
}