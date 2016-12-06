<?php
/**
 * Contains document generator.
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

/**
 * Document tree.
 *
 * Helps creating the document.
 *
 * @package   phpunit-docgen
 * @author    Ralf Mike Pretzlaw <hi@mike-pretzlaw.de>
 * @copyright 2016 Ralf Mike Pretzlaw
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/pretzlaw/phpunit-docgen
 * @see       \PHPUnit_Framework_TestListener
 * @since     1.0.0
 */
class DocumentNode {
	/**
	 * @var string
	 */
	protected $content;
	/**
	 * @var string
	 */
	protected $heading;
	/**
	 * @var DocumentNode[]
	 */
	protected $children = [ ];
	/**
	 * @var DocumentNode
	 */
	protected $parent;
	protected $namespace;

	public function __construct( $namespace, $heading, DocumentNode $parent = NULL ) {
		$this->namespace = $namespace;
		$this->heading   = $heading;
		$this->parent    = $parent;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	public function setContent( $content ) {
		$this->content = $content;
	}

	public function findHeading( $heading ) {
		foreach ( $this->getChildren() as $child ) {
			if ( $child->getHeading() == $heading ) {
				return $child;
			}
		}

		return NULL;
	}

	/**
	 * @return DocumentNode[]
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * @return string
	 */
	public function getHeading() {
		return $this->heading;
	}

	public function findNode( $namespace ) {
		$node = $this->findNearestNode( $namespace );

		if ( $node->getNamespace() != $namespace ) {
			return NULL;
		}

		return $node;
	}

	/**
	 * @param $namespace
	 *
	 * @return null|DocumentNode
	 */
	public function findNearestNode( $namespace ) {
		$currentNamespace = '';
		$currentNode      = $this;
		foreach ( explode( '\\', $namespace ) as $item ) {

			$currentNamespace .= '\\' . $item;
			$currentNamespace = ltrim( $currentNamespace, '\\' );

			if ( ! $currentNode->getChild( $currentNamespace ) ) {
				// Not found but continue because "bar/baz" inside is allowed.
				continue;
			}

			$currentNode = $currentNode->getChild( $currentNamespace );
		}

		return $currentNode;
	}

	public function getChild( $namespace ) {
		if ( ! isset( $this->children[ $namespace ] ) ) {
			return NULL;
		}

		return $this->children[ $namespace ];
	}

	public function getNamespace() {
		return $this->namespace;
	}

	public function createChild( $namespace, $heading ) {
		$node = new DocumentNode( $namespace, $heading, $this );

		$this->addChild( $node );

		return $node;
	}

	public function addChild( DocumentNode $node ) {
		$this->children[ $node->getNamespace() ] = $node;
	}

	public function addContent( $content, $prefix = PHP_EOL . PHP_EOL ) {
		$this->content .= $prefix . $content;
	}

	public function getLevel() {
		if ( NULL == $this->parent ) {
			return 1;
		}

		return $this->parent->getLevel() + 1;
	}

	public function getRoot() {
		if ( NULL == $this->parent ) {
			return $this;
		}

		return $this->parent->getRoot();
	}

	/**
	 * @param $namespace
	 *
	 * @return mixed
	 */
	protected function makeHash( $namespace ) {
		return str_replace( '\\', '/', $namespace );
	}
}
