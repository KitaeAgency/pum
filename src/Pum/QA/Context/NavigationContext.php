<?php

namespace Pum\QA\Context;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step\When;
use WebDriver\Behat\AbstractWebDriverContext;
use WebDriver\Behat\WebDriverContext;
use WebDriver\By;
use WebDriver\Exception\ExceptionInterface;
use WebDriver\Exception\NoSuchElementException;
use WebDriver\Util\Xpath;

class NavigationContext extends AbstractWebDriverContext
{
    const BUTTON_FROM_TEXT_XPATH       = './/a[contains(@class, "btn") and contains(., {text})]';
    const BUTTON_FROM_TITLE_XPATH      = '//a[contains(@class, "btn") and (contains(@title, {title}) or contains(@data-original-title, {title}))]';
    const CHECKBOX_FROM_TEXT_XPATH     = '//label[contains(@class, "checkbox")]//span[contains(., {text})]';
    const TABLE_ROW_FROM_TEXT_XPATH    = '//tr[contains(normalize-space(.), {text})]';
    const LABEL_TO_MENU_XPATH          = '//nav[contains(@class, "pum-core-sidebar")]/ul/li/a[contains(normalize-space(.), {text})]';

    /**
     * @When /^I am connected as "((?:[^"]|"")+)"$/
     */
    public function iAmConnectedAs($username)
    {
        return array(
            new When('I am on "/login"'),
            new When('I fill "Username" with "'.$username.'"'),
            new When('I fill "Password" with "'.$username.'"'),
            new When('I click on "Signin"'),
            new When('I should see "Logout"')
        );
    }

    /**
     * @When /^I click on "((?:[^"]|"")+)" in table row containing "((?:[^"]|"")+)"$/
     */
    public function iClickOnInTableRowContaining($text, $rowFilter)
    {
        $text      = $this->unescape($text);
        $rowFilter = $this->unescape($rowFilter);
        $xpath = strtr(self::TABLE_ROW_FROM_TEXT_XPATH, array('{text}' => Xpath::quote($rowFilter)));
        $elements = $this->getElements(By::xpath($xpath));

        if (count($elements) > 1) {
            $texts = array_map(function ($element) {
                return $element->getText();
            }, $elements);

            throw new \RuntimeException(sprintf('Found multiple rows containing "%s":%s', $rowFilter, "\n".implode("\n", $texts)));
        }

        $button = $this->getElement($this->parseSelector($text), $elements[0]);

        $button->click();
    }

    /**
     * @When /^I click on button "((?:[^"]|"")+)"$/
     */
    public function iClickOnButton($text)
    {
        $text = $this->unescape($text);
        $xpath = strtr(self::BUTTON_FROM_TEXT_XPATH, array('{text}' => Xpath::quote($text)));

        $this->getElement(By::xpath($xpath))->click();
    }

    /**
     * @When /^I check on checkbox "((?:[^"]|"")+)"$/
     */
    public function iCheckOnCheckbox($text)
    {
        $text = $this->unescape($text);
        $xpath = strtr(self::CHECKBOX_FROM_TEXT_XPATH, array('{text}' => Xpath::quote($text)));

        $this->getElement(By::xpath($xpath))->click();
    }

    /**
     * @When /^I should( not)? see a button "((?:[^"]|"")+)"$/
     */
    public function iShouldSeeAButton($verb, $text)
    {
        $text = $this->unescape($text);
        $xpath = strtr(self::BUTTON_FROM_TEXT_XPATH, array('{text}' => Xpath::quote($text)));
        $elements = $this->getElements(By::xpath($xpath));

        if (count($elements) == 0 && $verb === '') {
            throw new \RuntimeException(sprintf('Found no button with text "%s" (visible text: %s).', $text, $this->getElement(By::css('html'))->getText()));
        }

        if (count($elements) > 0 && $verb === ' not') {
            throw new \RuntimeException(sprintf('Found button(s) with text "%s", and expected not to find it.', $text));
        }
    }

    /**
     * @When /^I confirm modal$/
     */
    public function iConfirmModal()
    {
        $max = 10;
        while ($max > 0) {
            try {
                $this->getElement(By::css('#pumModal a.myModalconfirm'))->click();
                break;
            } catch (ExceptionInterface $e) {
                sleep(1);
                $max--;
            }
        }

        if ($max === 0) {
            throw new \RuntimeException('Unable to confirm modal');
        }
    }

    /**
     * @When /^I should( not)? see a button with (?:title|tooltip) "((?:[^"]|"")+)"$/
     */
    public function iShouldSeeAButtonWithTitle($verb, $title)
    {
        $title = $this->unescape($title);
        $xpath = strtr(self::BUTTON_FROM_TITLE_XPATH, array('{title}' => Xpath::quote($title)));
        $elements = $this->getElements(By::xpath($xpath));

        if (count($elements) == 0 && $verb === '') {
            throw new \RuntimeException(sprintf('Found no button with title "%s".', $title));
        }

        if (count($elements) > 0 && $verb === ' not') {
            throw new \RuntimeException(sprintf('Found button(s) with title "%s", and expected not to find it.', $title));
        }
    }

    /**
     * @Given /^I click on button with (?:title|tooltip) "((?:[^"]|"")+)"$/
     */
    public function iClickOnButtonWithTitle($title)
    {
        $title = $this->unescape($title);

        $xpath = strtr(self::BUTTON_FROM_TITLE_XPATH, array('{title}' => Xpath::quote($title)));
        $elements = $this->getElements(By::xpath($xpath));

        if (count($elements) == 0) {
            throw new \RuntimeException(sprintf('Found no button with title "%s".', $title));
        }

        $elements[0]->click();
    }

    /**
     * @When /^I logout$/
     */
    public function iLogout()
    {
        return array(
            new When('I am on "/woodwork/logout"'),
        );
    }

    /**
     * @Then /^I should see menu "((?:[^"]|"")*)"$/
     */
    public function iShouldSeeMenu($text)
    {
        $xpath = strtr(self::LABEL_TO_MENU_XPATH, array('{text}' => Xpath::quote($text)));
        $this->getElement(By::xpath($xpath));
    }

    /**
     * @Then /^I should not see menu "([^"]*)"$/
     */
    public function iShouldNotSeeMenu($text)
    {
        $xpath = strtr(self::LABEL_TO_MENU_XPATH, array('{text}' => Xpath::quote($text)));

        try {
            $this->getBrowser()->element(By::xpath($xpath));

            throw new \RuntimeException(sprintf('Found menu with text "%s".', $text));
        } catch (NoSuchElementException $e) {
        }
    }

    private function escape($text)
    {
        return str_replace('"', '""', $text);
    }

    private function unescape($text)
    {
        return str_replace('""', '"', $text);
    }
}
