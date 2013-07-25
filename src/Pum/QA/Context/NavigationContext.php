<?php

namespace Pum\QA\Context;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step\When;
use WebDriver\Behat\AbstractWebDriverContext;
use WebDriver\By;
use WebDriver\Util\Xpath;

class NavigationContext extends AbstractWebDriverContext
{
    const BUTTON_FROM_TEXT_XPATH  = '//a[contains(@class, "btn") and contains(., {text})]';
    const BUTTON_FROM_TITLE_XPATH = '//a[contains(@class, "btn") and contains(@title, {title})]';
    /**
     * @When /^I click on button "((?:[^"]|"")+)"$/
     */
    public function iClickOnButton($text)
    {
        $text = $this->unescape($text);
        $xpath = strtr(self::BUTTON_FROM_TEXT_XPATH, array('{text}' => Xpath::quote($text)));
        $elements = $this->getBrowser()->elements(By::xpath($xpath));

        if (count($elements) == 0) {
            throw new \RuntimeException(sprintf('Found no button with text "%s".', $text));
        }

        $elements[0]->click();
    }

    /**
     * @When /^I should see a button "((?:[^"]|"")+)"$/
     */
    public function iShouldSeeAButton($text)
    {
        $text = $this->unescape($text);
        $xpath = strtr(self::BUTTON_FROM_TEXT_XPATH, array('{text}' => Xpath::quote($text)));
        $elements = $this->getBrowser()->elements(By::xpath($xpath));

        if (count($elements) == 0) {
            throw new \RuntimeException(sprintf('Found no button with text "%s".', $text));
        }
    }

    /**
     * @Given /^I click on button with title "((?:[^"]|"")+)"$/
     */
    public function iClickOnButtonWithTitle($title)
    {
        $title = $this->unescape($title);

        $xpath = strtr(self::BUTTON_FROM_TITLE_XPATH, array('{title}' => Xpath::quote($title)));
        $elements = $this->getBrowser()->elements(By::xpath($xpath));

        if (count($elements) == 0) {
            throw new \RuntimeException(sprintf('Found no button with title "%s".', $title));
        }

        $elements[0]->click();
    }

    private function unescape($text)
    {
        return str_replace('""', '"', $text);
    }
}
