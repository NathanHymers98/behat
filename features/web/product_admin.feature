# A bad business value would be when the first and third line are exactly the same. Below, if I put the first line to be "In order to add/edit/delete products" it would be a sign of a bad business value
  # This is because the admin would not come to the Admin area of the website simply to use CRUD on the products, they would come to maintain the products that are on the front-end of the website
Feature: Product admin panel
  In order to maintain the products shown on the site
  As an admin
  I need to be able to add/edit/delete products

  Background:
    Given I am logged in as an admin

# When writing a scenario, think as though you are the type of user the test is for, in this case admin user and talk in the first person view at the users intended technical level
  # The Given line is whatever setup that you want before the scenario or user story actually starts. In this case since we are trying to list products, before the scenario starts, we want some products in the database to see
  # Using the "And" on the second line after the given allow me to extend the given line and add more setup details. With these two lines, we will start the test with 5 products in the database and start on the /admin page on the website
  # The second part of every scenario, When, is the user action. In this case, using the first person view and acting as the test user I write what I want to test. Which is click on the "Products" link on the admin page
  # The last part of every scenario, Then, is where we witness things as the user.
  # It is important when acting like the user to only write things that they can do and see.
   # Note: the "And" word can be used to extend any other word such as "When"
  Scenario: List available products
    Given there are 5 products
    And I am on "/admin"
    When I click "Products"
    Then I should see 5 products

  Scenario: Products show owner
    Given I author 5 products
    When I go to "/admin/products"
    # no products will be anonymous
    Then I should not see "Anonymous"

  Scenario: Show published/unpublished
    Given the following products exist:
      | name | is published |
      | Foo1 | yes          |
      | Foo2 | no           |
    When I go to "/admin/products"
    Then the "Foo1" row should have a check mark

  Scenario: Deleting a product
    Given the following product exists:
      | name |
      | Bar  |
      | Foo1 |
    When I go to "/admin/products"
    And I press "Delete" in the "Foo1" row
    Then I should see "The product was deleted"
    And I should not see "Foo1"
    But I should see "Bar"

  @javascript
  Scenario: Add a new product
    Given I am on "/admin/products"
    When I click "New Product"
    And I wait for the modal to load
    And I fill in "Name" with "Veloci-chew toy"
    And I fill in "Price" with "20"
    And I fill in "Description" with "Have your velociraptor chew on this instead!"
    And I press "Save"
    Then I should see "Product created FTW!"
    And I should see "Veloci-chew toy"
    # verify that we are the owner of the toy
    And I should not see "Anonymous"
