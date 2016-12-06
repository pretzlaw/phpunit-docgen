# Documentation generator via PHPUnit

## Usage

Require package:

	composer require pretzlaw/phpunit-docgen

Write this in your `phpunit.xml`:

	<listeners>
        <listener class="Pretzlaw\PHPUnit\DocGen\TestCaseListener">
            <arguments>
                <string>test-evidence.md</string>
            </arguments>
        </listener>
    </listeners>

It will output all your doc comments as a nice markdown.
This brings up a nice test evidence for unit tests
or some kind of documentation when running integration tests.
