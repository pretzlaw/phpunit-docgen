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

use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;
use Prophecy\Doubler\ClassPatch\ReflectionClassNewInstancePatch;

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
class TestCaseListener extends \PHPUnit_Util_Printer implements \PHPUnit_Framework_TestListener {
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
	 * @var string[]
	 */
	private $current;
	/**
	 * @var \DOMElement
	 */
	private $root;

	public function __construct( $out ) {
		$this->document = new DocumentNode( '\\', 'Documentation' );

		$this->docBlockParser = DocBlockFactory::createInstance();

		parent::__construct( $out );
	}

	/**
	 * Flush buffer and close output.
	 */
	public function flush() {
		$this->write( $this->printDocument( $this->document ) );

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
	 * @param PHPUnit_Framework_Test $test
	 * @param Exception              $e
	 * @param float                  $time
	 */
	public function addError( PHPUnit_Framework_Test $test, Exception $e, $time ) {
		// TODO: Implement addError() method.
	}

	/**
	 * A failure occurred.
	 *
	 * @param PHPUnit_Framework_Test                 $test
	 * @param PHPUnit_Framework_AssertionFailedError $e
	 * @param float                                  $time
	 */
	public function addFailure( PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time ) {
		// TODO: Implement addFailure() method.
	}

	/**
	 * Incomplete test.
	 *
	 * @param PHPUnit_Framework_Test $test
	 * @param Exception              $e
	 * @param float                  $time
	 */
	public function addIncompleteTest( PHPUnit_Framework_Test $test, Exception $e, $time ) {
		// TODO: Implement addIncompleteTest() method.
	}

	/**
	 * Risky test.
	 *
	 * @param PHPUnit_Framework_Test $test
	 * @param Exception              $e
	 * @param float                  $time
	 *
	 * @since Method available since Release 4.0.0
	 */
	public function addRiskyTest( PHPUnit_Framework_Test $test, Exception $e, $time ) {
		// TODO: Implement addRiskyTest() method.
	}

	/**
	 * Skipped test.
	 *
	 * @param PHPUnit_Framework_Test $test
	 * @param Exception              $e
	 * @param float                  $time
	 *
	 * @since Method available since Release 3.0.0
	 */
	public function addSkippedTest( PHPUnit_Framework_Test $test, Exception $e, $time ) {
		// TODO: Implement addSkippedTest() method.
	}

	protected $handledMethods = [];

	/**
	 * A test suite started.
	 *
	 * @param PHPUnit_Framework_TestSuite $suite
	 *
	 * @since Method available since Release 2.2.0
	 */
	public function startTestSuite( PHPUnit_Framework_TestSuite $suite ) {
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
	 * @param string   $namespace
	 * @param DocBlock $docBlock
	 */
	protected function appendDoc( $namespace, $docBlock ) {
		$node = $this->document->fetchNode( $namespace );

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
	 * @param PHPUnit_Framework_TestSuite $suite
	 *
	 * @since Method available since Release 2.2.0
	 */
	public function endTestSuite( PHPUnit_Framework_TestSuite $suite ) {

	}

	/**
	 * A test started.
	 *
	 * @param PHPUnit_Framework_Test $test
	 */
	public function startTest( PHPUnit_Framework_Test $test ) {
		if ( ! $test instanceof \PHPUnit_Framework_TestCase ) {
			return;
		}

		/* @var \PHPUnit_Framework_TestCase $test */

		if (isset($this->handledMethods[$this->getDocNamespace($test)])) {
			// Seems like a test with data provider so we won't parse it more than once.
			return;
		}

		$this->handledMethods[$this->getDocNamespace($test)] = true;

		try {
			$reflectMethod = new \ReflectionMethod( get_class( $test ), $test->getName(false) );
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
	 * @param \PHPUnit_Framework_TestCase $test
	 *
	 * @return string
	 */
	protected function getDocNamespace( \PHPUnit_Framework_TestCase $test ) {
		return preg_replace( '@Test$@', '', get_class( $test ) )
			. '\\' . preg_replace( '@^test@', '', $test->getName(false) );
	}

	/**
	 * A test ended.
	 *
	 * @param PHPUnit_Framework_Test $test
	 * @param float                  $time
	 */
	public function endTest( PHPUnit_Framework_Test $test, $time ) {

	}
}
