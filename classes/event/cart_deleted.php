<?php

/**
 * @package    enrol_cart
 * @brief      Shopping Cart Enrolment Plugin for Moodle
 * @category   Moodle, Enrolment, Shopping Cart
 *
 * @author     MohammadReza PourMohammad <onbirdev@gmail.com>
 * @copyright  2024 MohammadReza PourMohammad
 * @link       https://onbir.dev
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace enrol_cart\event;

use core\event\base;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

class cart_deleted extends base
{
    protected function init()
    {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'enrol_cart';
    }

    public static function get_name()
    {
        return get_string('event_cart_deleted', 'enrol_cart');
    }

    public function get_description()
    {
        return "The user with id '{$this->userid}' deleted the cart with id '{$this->objectid}'.";
    }

    public function get_url()
    {
        return new moodle_url('/enrol/cart/view.php', ['id' => $this->objectid]);
    }
}
