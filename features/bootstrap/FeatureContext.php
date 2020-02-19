<?php

use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;

require_once __DIR__.'/../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawMinkContext implements Context, SnippetAcceptingContext // This class uses Mink and behat to allow us to run behat tests and use Mink functionality such as getting web pages and clicking on links along with our behat tests.
{
    use \Behat\Symfony2Extension\Context\KernelDictionary;

    private $currentUser;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @BeforeScenario
     */
    public function clearData() // This method clears the database before each scenario because we always want a predictable database for each individual test. This is so we don't get any conflicts. F
                                // For example, if we want to create a user in the database to use for a test and this user already exists because of a different test, the test will fail because the database cannot create another user which is identical to an already existing user
                                // The test will fail not because the feature we want to test is broken, but because the database is not in a predicable state for the test. This is why we should clear the database data before each scenario
    {
        $purger = new ORMPurger($this->getContainer()->get('doctrine')->getManager()); // Creating the purger object
        $purger->purge(); // Calling the purger object to clear the database
    }

    // By adding the "@fixtures" this method is only run by scenarios that have the @fixtures tag
    /**
     * @BeforeScenario @fixtures
     */
    public function loadFixtures()
    {
        $loader = new ContainerAwareLoader($this->getContainer());
        $loader->loadFromDirectory(__DIR__.'/../../src/AppBundle/DataFixtures'); // Pointing to the data fixtures that I want to load. In this case it is the data fixture that creates the Kindle and Samsung products
        $executor = new ORMExecutor($this->getEntityManager());
        $executor->execute($loader->getFixtures(), true);
    }

    /**
     * @Given there is an admin user :username with password :password
     */
    public function thereIsAnAdminUserWithPassword($username, $password)
    {
        $user = new \AppBundle\Entity\User(); // When this function is called by a behet test it will create a new user object
        $user->setUsername($username); // Set the username to the one that was defined in the behat test
        $user->setPlainPassword($password); // Set the password to the one that was defined in the behat test
        $user->setRoles(array('ROLE_ADMIN')); // Set the role of the user to admin, since we are creating an admin user with this method

        $em = $this->getContainer()->get('doctrine')->getManager(); // Getting the Doctrine Entity Manager so that we can save this newly created user to the database
        $em->persist($user); // Saves the user to the database
        $em->flush(); // Checks all the fields and translates it into something that the database can understand. You cannot use flush() with persist() and vise versa because they rely on each other to save an object to the database

        return $user; // returning the user because another method that uses this method requires the user object
    }

    /**
     * @When I fill in the search box with :term
     */
    public function iFillInTheSearchBoxWith($term)
    {
        $searchBox = $this->assertSession() // Allows us to have access to mink in this file that holds all the behat methods
            ->elementExists('css', 'input[name="searchTerm"]');

        $searchBox->setValue($term);
    }

    /**
     * @When I press the search button
     */
    public function iPressTheSearchButton()
    {
        $button = $this->assertSession()
            ->elementExists('css', '#search_submit');

        $button->press();
    }

    /**
     * @Given there is/are :count product(s)
     */
    public function thereAreProducts($count)
    {
        $this->createProducts($count);
    }

    /**
     * @Given I author :count products
     */
    public function iAuthorProducts($count)
    {
        $this->createProducts($count, $this->currentUser);
    }

    /**
     * @Given the following product(s) exist(s):
     */
    public function theFollowingProductsExist(TableNode $table) // This method has the TableNode $table argument because we are passing it the contents of the table that we created in the behat test
    {
        foreach ($table as $row) { // Looping over the $table object as $row. Each individual instance can now be refered to as $row
            $product = new Product();
            $product->setName($row['name']);
            $product->setPrice(rand(10, 1000));
            $product->setDescription('lorem');

            if (isset($row['is published']) && $row['is published'] == 'yes') { // if the table row is set to 'is published' and the table row 'is published' is equal to 'yes'
                $product->setIsPublished(true); // Then make the product published
            }

            $this->getEntityManager()->persist($product); // If it is not published, then save it to the database.
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @Then the :rowText row should have a check mark
     */
    public function theProductRowShouldShowAsPublished($rowText)
    {
        $row = $this->findRowByText($rowText); // Gets the row of a html table by calling the method that does this.

        assertContains('fa-check', $row->getHtml(), 'Could not find the fa-check element in the row!'); // Asserts that the table row contains 'fa-check' if it doesn't contain it, then display a helpful message
    }

    /**
     * @When I press :linkText in the :rowText row
     */
    public function iClickInTheRow($linkText, $rowText)
    {
        $this->findRowByText($rowText)->pressButton($linkText);
    }

    /**
     * @When I click :linkName
     */
    public function iClick($linkName)
    {
        $this->getPage()->clickLink($linkName); // Uses the link name that was used in the behat test and sets it to the $linkName. It then uses the getPage method to get the required page and then calls the clickLink() method passed with the link name which clicks the passed link
    }

    /**
     * @Then I should see :count products
     */
    public function iShouldSeeProducts($count)
    {
        $table = $this->getPage()->find('css', 'table.table'); // Gets the page, and finds the table on the page. 'css' has to be there
        assertNotNull($table, 'Cannot find a table!'); // Asserts that the table should exist, if it doesn't then display the message

        assertCount(intval($count), $table->findAll('css', 'tbody tr')); // Asserts that $count is has a value which is of type integer, if it doesn't it finds all the contents of the table and returns the elements in an array. The findAll() method is what turns the contents into an array.
    }

    /**
     * @Given I am logged in as an admin
     */
    public function iAmLoggedInAsAnAdmin()
    {
        $this->currentUser = $this->thereIsAnAdminUserWithPassword('admin', 'admin'); // The current user is the one that is logged in as the admin. It gets the user object that is created above to do this

        $this->visitPath('/login'); // Prefixes '/login' to our base URL which is defined in behat.yml
        $this->getPage()->fillField('Username', 'admin'); // Gets the page and fills in the field 'Username' with admin on the page
        $this->getPage()->fillField('Password', 'admin');
        $this->getPage()->pressButton('Login'); // Pressed the login button the page
    }

    /**
     * @When I wait for the modal to load
     */
    public function iWaitForTheModalToLoad() // This method exists because when behat runs the test with mink and selenium so it opens up a browser and preforms the steps, it does not wait for the JavaScript modal to load, it expects it to display instantly. They do however, wait for normal page refreshes.
                                            // When it doesn't display instantly, the test will fail, even though there could be nothing wrong with the code. This method makes behat and mink wait a certain amount of time so that the JS modal is loaded fully.
    {
        $this->getSession()->wait( // Tells the browser session to wait for 5 seconds
            5000,
            "$('.modal:visible').length > 0" // This is a javascript expression that runs on the page every 100 mili seconds, as soon as it equals true, mink will stop waiting and move on to the next step
        );
    }

    /**
     * Pauses the scenario until the user presses a key. Useful when debugging a scenario.
     *
     * @Then (I )break
     */
    public function iPutABreakpoint() // When this method is called in a test it pauses once the behat test gets to it, this means that if we have an error, but can't see it because mink goes too fast, we can but a break there and it it pause and wait until we tell it to continue
    {
        fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {}
        fwrite(STDOUT, "\033[u");
        return;
    }

    /**
     * Saving a screenshot
     *
     * @When I save a screenshot to :filename
     */
    public function iSaveAScreenshotIn($filename)// When this method is used along with the breakpoint method, it takes a screen shot of the page and saves it to our root directory so that we can open it and look at the page.
                                                // This is useful for when you know there is an error on a page, but mink goes too fast to spot the error, with this you can use it in a behat test just before where you think the error is and you can look at an image of the page where the error is to try and find the issue that is causing the test to fail.
    {
        sleep(1);
        $this->saveScreenshot($filename, __DIR__.'/../..');
    }

    /**
     * @return \Behat\Mink\Element\DocumentElement
     */
    private function getPage()
    {
        return $this->getSession()->getPage();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    private function createProducts($count, User $author = null)
    {
        for ($i = 0; $i < $count; $i++) { // $i is equal to one; if $i is less than or equal to $count; add 1 to $i. This keeps looping until $count is greater than 0, if it is not greater than 0 it adds 1 to $i. Then, if it is not greater than 1 it carries on. For loops are used when you know in advance how many times it should run
                                            // This ensures that every bit of data that is in the $count variable is looped over. It will stop once everything in $count has been looped over.
            $product = new Product(); // For every loop, a new product is created
            $product->setName('Product '.$i); // The name is set to " 'Product'.$i " The $i is the number of the product counting from 0
            $product->setPrice(rand(10, 1000)); // The price is set to a random integer between 10, 1000
            $product->setDescription('lorem'); // The description is set to "lorem"

            if ($author) { // If an author exists in the loop
                $product->setAuthor($author); // Set the author to their product/products
            }

            $this->getEntityManager()->persist($product); // Saves the product data to the database
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @param $rowText
     * @return \Behat\Mink\Element\NodeElement
     */
    private function findRowByText($rowText)
    {
        $row = $this->getPage()->find('css', sprintf('table tr:contains("%s")', $rowText)); // gets a page and looks for a css table which inside a tr tag contains the row text that was passed in the behat test
        assertNotNull($row, 'Cannot find a table row with this text!'); // Asserts that the $row object is not empty, if it is, then display a message saying that it can't find a table row that has the text the behat test was looking for

        return $row;
    }
}
