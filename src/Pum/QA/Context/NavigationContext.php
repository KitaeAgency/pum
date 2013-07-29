<?php

namespace Pum\QA\Context;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step\When;
use WebDriver\Behat\AbstractWebDriverContext;
use WebDriver\By;
use WebDriver\Exception\ExceptionInterface;
use WebDriver\Util\Xpath;

class NavigationContext extends AbstractWebDriverContext
{
    const BUTTON_FROM_TEXT_XPATH  = '//a[contains(@class, "btn") and contains(., {text})]';
    const BUTTON_FROM_TITLE_XPATH = '//a[contains(@class, "btn") and contains(@title, {title})]';

    /**
     * @Then /^I sleep$/
     */
    public function iSleep($time=5)
    {
        sleep($time);
    }

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
     * @When /^I should( not)? see a button "((?:[^"]|"")+)"$/
     */
    public function iShouldSeeAButton($verb, $text)
    {
        $text = $this->unescape($text);
        $xpath = strtr(self::BUTTON_FROM_TEXT_XPATH, array('{text}' => Xpath::quote($text)));
        $elements = $this->getBrowser()->elements(By::xpath($xpath));

        if (count($elements) == 0 && $verb === '') {
            throw new \RuntimeException(sprintf('Found no button with text "%s".', $text));
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
                $this->getBrowser()->element(By::css('#pumModal a.btn-success'))->click();
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
     * @When /^I should( not)? see a button with title "((?:[^"]|"")+)"$/
     */
    public function iShouldSeeAButtonWithTitle($verb, $title)
    {
        $title = $this->unescape($title);
        $xpath = strtr(self::BUTTON_FROM_TITLE_XPATH, array('{title}' => Xpath::quote($title)));
        $elements = $this->getBrowser()->elements(By::xpath($xpath));

        if (count($elements) == 0 && $verb === '') {
            throw new \RuntimeException(sprintf('Found no button with title "%s".', $title));
        }

        if (count($elements) > 0 && $verb === ' not') {
            throw new \RuntimeException(sprintf('Found button(s) with title "%s", and expected not to find it.', $title));
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
