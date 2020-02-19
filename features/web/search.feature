Feature: Search
  In order to find products dinosaurs love
  As a website user
  I need to be able to search for products

  Background:
    Given I am on "/"

    # This Scenario Outline below is the best way to stop duplicated test code past the "Given" line.
    # What this does is it searches for a product and fills out the search box with both of the terms that are defined below in the "Examples:" table
    # The last line where we should see a result also uses the examples table and puts in the correct result depending on the term
    # This works by using the syntax "<term>" and "<result>" when these are called it goes into the examples table and selects the term and the corresponding result that goes with the term and uses that data instead for the test
    # The "@fixtures" is called a tag
  @fixtures
  Scenario Outline: Search for a product
    When I fill in the search box with "<term>"
    And I press the search button
    Then I should see "<result>"

    Examples:
      | term    | result              |
      | Samsung | Samsung Galaxy S II |
      | XBox    | No products found   |
