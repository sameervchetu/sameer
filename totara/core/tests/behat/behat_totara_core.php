<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2015 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Sam Hemelryk <sam.hemelryk@totaralms.com>
 * @package totara_core
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode;

/**
 * The Totara core definitions class.
 *
 * This class contains the definitions for core Totara functionality.
 * Any definitions that belong to separ
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright Copyright (C) 2010-2013 Totara Learning Solutions LTD
 */
class behat_totara_core extends behat_base {

    /**
     * A tab should be visible but disabled.
     *
     * @Given /^I should see the "([^"]*)" tab is disabled$/
     */
    public function i_should_see_the_tab_is_disabled($text) {
        $text = $this->getSession()->getSelectorsHandler()->xpathLiteral($text);
        $xpath = "//div[contains(concat(' ', normalize-space(@class), ' '), ' tabtree ')]//a[contains(concat(' ', normalize-space(@class), ' '), ' nolink ') and not(@href)]/*[contains(text(), {$text})]";
        $this->find(
            'xpath',
            $xpath,
            new \Behat\Mink\Exception\ExpectationException('Tab "'.$text.'" could not be found or was not disabled', $this->getSession())
        );
    }

    /**
     * Finds a totara menu item and returns the node.
     *
     * @param string $text
     * @param bool $ensurevisible
     * @return \Behat\Mink\Element\NodeElement
     * @throws \Behat\Mink\Exception\ExpectationException
     */
    protected function find_totara_menu_item($text, $ensurevisible = false) {
        $text = $this->getSession()->getSelectorsHandler()->xpathLiteral($text);
        $xpath = "//div[@id = 'totaramenu']//li/a[contains(text(),{$text})]";
        $node = $this->find(
            'xpath',
            $xpath,
            new \Behat\Mink\Exception\ExpectationException('Totara menu item "'.$text.'" could not be found', $this->getSession())
        );
        if ($ensurevisible && !$node->isVisible()) {
            throw new \Behat\Mink\Exception\ExpectationException('Totara menu item "'.$text.'" is not visible visible', $this->getSession());
        }
        return $node;
    }

    /**
     * Check you can see the expected menu item.
     *
     * @Given /^I should see "([^"]*)" in the totara menu$/
     */
    public function i_should_see_in_the_totara_menu($text) {
        $this->find_totara_menu_item($text, true);
    }

    /**
     * Check the menu item is not there as expected.
     *
     * @Given /^I should not see "([^"]*)" in the totara menu$/
     */
    public function i_should_not_see_in_the_totara_menu($text) {
        try {
            $this->find_totara_menu_item($text, true);
        } catch (\Behat\Mink\Exception\ExpectationException $ex) {
            // This is the desired outcome.
            return true;
        }
        throw new \Behat\Mink\Exception\ExpectationException('Totara menu item "'.$text.'" has been found and is visible', $this->getSession());
    }

    /**
     * Click on an item in the totara menu.
     *
     * @Given /^I click on "([^"]*)" in the totara menu$/
     */
    public function i_click_on_in_the_totara_menu($text) {
        $node = $node = $this->find_totara_menu_item($text, true);
        $node->click();
    }

    /**
     * Create one or more menu items for the Totara main menu
     *
     * @Given /^I create the following totara menu items:$/
     */
    public function i_create_the_following_totara_menu_items(TableNode $table) {
        $possiblemenufields = array('Parent item', 'Menu title', 'Visibility', 'Menu default url address', 'Open link in new window');
        $first = false;

        $steps = array();
        $menufields = array();
        $rulefields = array();

        // We are take table c
        foreach ($table->getRows() as $row) {
            $menutable = new TableNode();
            $ruletable = new TableNode();

            if ($first === false) {
                // The first row is the headings.
                foreach ($row as $key => $field) {
                    if (in_array($field, $possiblemenufields)) {
                        $menufields[$field] = $key;
                    } else {
                        $rulefields[$field] = $key;
                    }
                }
                $first = true;
                continue;
            }

            foreach ($row as $key => $value) {
                $menurow = array();
                $rulerow = array();
                if (in_array($key, $menufields)) {
                    $menurow[] = array_search($key, $menufields);
                    $menurow[] = $row[$key];
                    $menutable->addRow($menurow);
                } else {
                    $rulerow[] = array_search($key, $rulefields);
                    $rulerow[] = $row[$key];
                    $ruletable->addRow($rulerow);
                }
            }

            $steps[] = new Given('I navigate to "Main menu" node in "Site administration > Appearance"');
            $steps[] = new Given('I press "Add new menu item"');
            $steps[] = new Given('I set the following fields to these values:', $menutable);
            $steps[] = new Given('I press "Add new menu item"');
            $steps[] = new Given('I should see "Edit menu item"');
            $steps[] = new Given('I click on "Access" "link"');
            $steps[] = new Given('I expand all fieldsets');
            $steps[] = new Given('I set the following fields to these values:', $ruletable);
            $steps[] = new Given('I press "Save changes"');
        }

        return $steps;
    }

    /**
     * Edit a Totara main menu item via the Admin interface.
     *
     * @Given /^I edit "([^"]*)" totara menu item$/
     */
    public function i_edit_totara_menu_item($text) {
        $text = $this->getSession()->getSelectorsHandler()->xpathLiteral($text);
        $xpath = "//table[@id='totaramenu']//td[contains(concat(' ', normalize-space(@class), ' '), ' name ')]/*[contains(text(),{$text})]//ancestor::tr//a[@title='Edit']";
        $node = $this->find(
            'xpath',
            $xpath,
            new \Behat\Mink\Exception\ExpectationException('Could not find Edit action for "'.$text.'" menu item', $this->getSession())
        );
        $node->click();
    }

    /**
     * Generic focus action.
     *
     * @When /^I focus on "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)"$/
     * @param string $element Element we look for
     * @param string $selectortype The type of what we look for
     */
    public function i_focus_on($element, $selectortype) {
        $node = $this->get_selected_node($selectortype, $element);
        $node->focus();
    }
}
