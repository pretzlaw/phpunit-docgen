<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Comment.php
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

use Michelf\MarkdownExtra;
use phpDocumentor\Reflection\DocBlock;
use SimpleXMLElement;

/**
 * Comment
 *
 * @copyright  2019 Mike Pretzlaw (https://mike-pretzlaw.de)
 * @since      2019-06-09
 */
class Comment
{
    /**
     * @var DocBlock
     */
    private $docBlock;
    private $html;
    private $xml;

    public function __construct(DocBlock $docBlock)
    {
        $this->docBlock = $docBlock;
    }

    public function docBlock()
    {
        return $this->docBlock;
    }

    /**
     * @param string $xpath
     * @param int|null $index
     * @return SimpleXMLElement[]|SimpleXMLElement|bool
     */
    public function xpath(string $xpath, int $index = null)
    {
        $elements = $this->xml()->xpath($xpath);

        if (null === $index) {
            return $elements;
        }

        if (count($elements) < $index + 1) {
            return false;
        }

        return $elements[$index];
    }

    private function xml(): SimpleXMLElement
    {
        if (null === $this->xml) {
            $this->xml = simplexml_load_string('<html>' . $this->html() . '</html>');
        }

        return $this->xml;
    }

    public function html(): string
    {
        if (null === $this->html) {
            $this->html = MarkdownExtra::defaultTransform($this->markdown());
        }

        return $this->html;
    }

    public function markdown(): string
    {
        $markdown = $this->docBlock()->getSummary();

        if (false === strpos($markdown, "\n")) {
            $markdown = '# ' . trim($markdown);
        }

        $markdown .= PHP_EOL . PHP_EOL;
        $markdown .= $this->docBlock()->getDescription();

        return trim($markdown);
    }

    public function execute($xpathOrIndex)
    {
        $index = 0;
        if (is_int($xpathOrIndex)) {
            $index = $xpathOrIndex;
            $xpathOrIndex = '//pre/code';
        }

        $content = $this->xpath($xpathOrIndex, $index);

        if (!$content || !$content instanceof SimpleXMLElement) {
            throw new \RuntimeException('Code not found or empty: ' . $xpathOrIndex);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'rmpup_dc_');

        $isSaved = file_put_contents($tempFile, $content);

        if (false === $isSaved) {
            throw new \RuntimeException('Could not create tempfile for code: ' . $xpathOrIndex);
        }

        ob_start();
        $return = require $tempFile;
        $content = ob_get_clean();
        unlink($tempFile);

        if (1 !== $return) {
            return $return;
        }

        return $content;
    }
}