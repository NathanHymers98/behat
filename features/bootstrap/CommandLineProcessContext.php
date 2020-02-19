<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

require_once __DIR__.'/../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

class CommandLineProcessContext implements Context, SnippetAcceptingContext
{
    private $output;

    // The 3 methods below and there annotations are linked to the ls.feature file. As long as I follow the format of the annotation, behat will assign the variables with the data I am using in the test and execute the functions for me.

    /**
     * @Given I have a file named :filename
     */
    public function iHaveAFileNamed($filename)
    {
        touch($filename);
    }

    /**
     * @When I run :command
     */
    public function iRun($command)
    {
        $this->output = shell_exec($command);
    }

    /**
     * @Then I should see :string in the output
     */
    public function iShouldSeeInTheOutput($string)
    {
        assertContains(
            $string,
            $this->output,
            sprintf('Did not see "%s" in output "%s"', $string, $this->output)
        );
    }

    // The @AfterScenario and @BeforeScenario tells behat that these function are to be run before and after the test respectively.
    /**
     * @BeforeScenario
     */
    public function moveIntoTestDir()
    {
        if (!is_dir('test')) {
            mkdir('test');
        }
        chdir('test');
    }

    /**
     * @AfterScenario
     */
    public function moveOutOfTestDir()
    {
        chdir('..');
        if (is_dir('test')) {
            system('rm -r '.realpath('test'));
        }
    }

    /**
     * @Given I have a dir named :dir
     */
    public function iHaveADirNamed($dir)
    {
        mkdir($dir);
    }
}
