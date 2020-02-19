<?php

require __DIR__.'/vendor/autoload.php';

use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Session;

// Important object #1
//$driver = new GoutteDriver();
$driver = new Selenium2Driver();

// Important object #2
$session = new Session($driver);

$session->start(); // Starts the browser session
$session->visit('http://jurassicpark.wikia.com'); // Telling the driver what site we want to visit

//echo "Status code: ". $session->getStatusCode() . "\n";
echo "Current URL: ". $session->getCurrentUrl() . "\n"; // Echoing out the status code and current URL of the website that we are visiting

// Important object #3 DocumentElement
$page = $session->getPage(); // Getting a page from the website

echo "First 75 chars: ".substr($page->getText() , 0, 75) . "\n"; // Using that page that we just got and displaying the first 75 characters of the page. This goes off the raw HTML index document, but it ignores any scripts and only prints out h1 tags etc

// Important object #4 NodeElement
$header = $page->find('css', '.wds-community-header__sitename a'); // Finding by css, looking for the names of div classes in the HTML index page

echo "The wiki site name is: ".$header->getText()."\n";

$subNav = $page->find('css', '.wds-tabs');
$linkEl = $subNav->find('css', 'li a');

$linkEl = $page->findLink('Books');

echo "The link href is: ". $linkEl->getAttribute('href') . "\n";

$linkEl->click();
echo "Page URL after click: ". $session->getCurrentUrl() . "\n";

$session->stop();