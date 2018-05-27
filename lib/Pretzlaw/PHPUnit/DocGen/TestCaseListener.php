<?php
/**
 * Contains test case listener
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to the MIT License
 * that is available through the world-wide-web at the following URI:
 * https://opensource.org/licenses/MIT.  If you did not receive a copy of
 * the MIT License and are unable to obtain it through the web, please
 * send a note to hi@mike-pretzlaw.de so we can mail you a copy immediately.
 *
 * @author    Ralf Mike Pretzlaw <hi@mike-pretzlaw.de>
 * @copyright 2016 Ralf Mike Pretzlaw
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/pretzlaw/phpunit-docgen
 * @since     1.0.0
 */

namespace Pretzlaw\PHPUnit\DocGen;

use Dompdf\Dompdf;
use Exception;
use Michelf\MarkdownExtra;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Printer;

/**
 * Listen to test cases and generate document.
 *
 * This reacts to single test scenarios.
 *
 * @package   phpunit-docgen
 * @author    Ralf Mike Pretzlaw <hi@mike-pretzlaw.de>
 * @copyright 2016 Ralf Mike Pretzlaw
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/pretzlaw/phpunit-docgen
 * @see       \PHPUnit_Framework_TestListener
 * @since     1.0.0
 */
class TestCaseListener extends Printer implements TestListener {
	/**
	 * Gather document.
	 *
	 * This stays alive during the whole testing process.
	 *
	 * @var array
	 */
	protected $document;

	/**
	 * @var DocBlockFactory
	 */
	protected $docBlockParser;

	/**
	 * TestCaseListener constructor.
	 *
	 * @param $out
	 *
	 * @throws \PHPUnit\Framework\Exception
	 */
	public function __construct( $out ) {
		$this->document = new DocumentNode( '\\', 'Documentation' );

		$this->docBlockParser = DocBlockFactory::createInstance();

		parent::__construct( $out );
	}

	/**
	 * Flush buffer and close output.
	 * @throws \InvalidArgumentException
	 */
	public function flush() {
        // Determine file type by extension.
        $fileType = strtolower( substr( $this->outTarget, strrpos( $this->outTarget, '.' ) + 1 ) );

        switch ( $fileType ) {
            case 'md':
                $content = $this->printDocument( $this->document );
                break;
            case 'pdf':
                $markdownParser = new MarkdownExtra();
                $html = $markdownParser->transform( $this->printDocument( $this->document ) );

                $domPdf = new Dompdf();
                $domPdf->loadHtml($html);
                $domPdf->render();
                $content = $domPdf->output();
                break;
            default:
                throw new \InvalidArgumentException( 'Unknown file type. Not implemented: ' . $fileType );
        }

        $this->write( $content );

		parent::flush();
	}

	/**
	 * @param DocumentNode $document
	 *
	 * @return string
	 */
	private function printDocument( DocumentNode $document ) {
		$text = '';

		if ( $document->getHeading() ) {
			$text = PHP_EOL . PHP_EOL . str_repeat( '#', $document->getLevel() ) . ' ' . $document->getHeading();
		}

		$content = trim( $document->getContent() );
		if ( $content ) {
			$text .= PHP_EOL . PHP_EOL . $content;
		}

		foreach ( $document->getChildren() as $child ) {
			$text .= $this->printDocument( $child );
		}

		return $text;
	}

	/**
	 * An error occurred.
	 *
	 * @param Test $test
	 * @param Exception              $e
	 * @param float                  $time
	 */
	public function addError( Test $test, Exception $e, $time ) {
		// TODO: Implement addError() method.
	}

	/**
	 * A failure occurred.
	 *
	 * @param Test $test
	 * @param AssertionFailedError $e
	 * @param float $time
	 */
	public function addFailure( Test $test, AssertionFailedError $e, $time ) {
		// TODO: Implement addFailure() method.
	}

	/**
	 * Incomplete test.
	 *
	 * @param Test $test
	 * @param Exception              $e
	 * @param float                  $time
	 */
	public function addIncompleteTest( Test $test, Exception $e, $time ) {
		// TODO: Implement addIncompleteTest() method.
	}

	/**
	 * Risky test.
	 *
	 * @param Test $test
	 * @param Exception              $e
	 * @param float                  $time
	 *
	 * @since Method available since Release 4.0.0
	 */
	public function addRiskyTest( Test $test, Exception $e, $time ) {
		// TODO: Implement addRiskyTest() method.
	}

	/**
	 * Skipped test.
	 *
	 * @param Test $test
	 * @param Exception              $e
	 * @param float                  $time
	 *
	 * @since Method available since Release 3.0.0
	 */
	public function addSkippedTest( Test $test, Exception $e, $time ) {
		// TODO: Implement addSkippedTest() method.
	}

	protected $handledMethods = [];

	/**
	 * A test suite started.
	 *
	 * @param TestSuite $suite
	 *
	 * @since Method available since Release 2.2.0
	 * @throws \RuntimeException
	 * @throws \ReflectionException
	 */
	public function startTestSuite( TestSuite $suite ) {
		if ( 0 === strpos( $suite->getName(), 'PHPUnit_' ) ) {
			return;
		}

		if ( ! class_exists( $suite->getName() ) ) {
			return;
		}

		$reflection = new \ReflectionClass( $suite->getName() );

		if ( ! $reflection->getDocComment() ) {
			// When this one has no comment, then it shall not be parsed.
			return;
		}

		/** @var DocBlock $docBlock */
		$docBlock = $this->docBlockParser->create( $reflection->getDocComment() );

		if ( $docBlock->hasTag( 'internal' ) ) {
			// This one is internal, which will be ignored.
			return;
		}

		$this->appendDoc(preg_replace('@Test$@', '', $suite->getName()), $docBlock);
	}

	/**
	 * @todo This breaks when some tests depends on another.
	 *
	 * @param string $namespace
	 * @param DocBlock $docBlock
	 *
	 * @throws \RuntimeException
	 */
	protected function appendDoc( $namespace, $docBlock ) {
		$node = $this->document->fetchNode( $namespace );

		if ( null === $node ) {
			throw new \RuntimeException( 'Could not determine new node' );
		}

		// Check for sibling with same heading, which should be extended instead.
		$matchingSibling = null;
		if ($node->getParent() && trim($docBlock->getSummary())) {
			// Use sibling with same non-empty heading.
			$matchingSibling = $node->getParent()->findHeading($docBlock->getSummary());
		}

		if ($matchingSibling) {
			// Found sibling with same heading, which will be used instead of creating a duplicate.
			$node = $matchingSibling;
		}

		if (!$node->getHeading()) {
			// Is a new node so we fill the heading.
			$node->setHeading($docBlock->getSummary());
		}

		if ( ! $docBlock->getDescription() ) {
			return;
		}

		$node->addContent( trim( $docBlock->getDescription() ) );
	}

	/**
	 * A test suite ended.
	 *
	 * @param TestSuite $suite
	 *
	 * @since Method available since Release 2.2.0
	 */
	public function endTestSuite( TestSuite $suite ) {

	}

	/**
	 * A test started.
	 *
	 * @param Test $test
	 *
	 * @throws \RuntimeException
	 */
	public function startTest( Test $test ) {
		if ( ! $test instanceof TestCase ) {
			return;
		}

		/* @var TestCase $test */

		if (isset($this->handledMethods[$this->getDocNamespace($test)])) {
			// Seems like a test with data provider so we won't parse it more than once.
			return;
		}

		$this->handledMethods[$this->getDocNamespace($test)] = true;

		try {
			$reflectMethod = new \ReflectionMethod( \get_class( $test ), $test->getName(false) );
		} catch (\ReflectionException $e) {
			// Not a method or not accessible, so we skip it.
			return;
		}

		if ( ! $reflectMethod->getDocComment() ) {
			// When this one has no comment, then it shall not be parsed.
			return;
		}

		$docBlock = $this->docBlockParser->create( $reflectMethod->getDocComment() );

		if ( $docBlock->hasTag( 'internal' ) ) {
			// This one is internal, which will be ignored.
			return;
		}

		$this->appendDoc( $this->getDocNamespace( $test ), $docBlock );
	}

	/**
	 * Generate namespace.
	 *
	 * Chops off beginning "test" from methods
	 * and trailing "Test" from class names.
	 *
	 * @param TestCase $test
	 *
	 * @return string
	 */
	protected function getDocNamespace( TestCase $test ) {
		return preg_replace( '@Test$@', '', \get_class( $test ) )
			. '\\' . preg_replace( '@^test@', '', $test->getName(false) );
	}

	/**
	 * A test ended.
	 *
	 * @param Test $test
	 * @param float                  $time
	 */
	public function endTest( Test $test, $time ) {

	}

	/**
	 * A warning occurred.
	 *
	 * @param Test $test
	 * @param Warning $e
	 * @param float $time
	 */
	public function addWarning( Test $test, Warning $e, $time ) {

	}
}
