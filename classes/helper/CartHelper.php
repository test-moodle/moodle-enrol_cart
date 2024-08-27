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

namespace enrol_cart\helper;

defined('MOODLE_INTERNAL') || die();

use context_course;
use enrol_cart\object\Cart;
use enrol_cart\object\CartEnrollmentInstance;
use enrol_cart\object\CookieCart;
use moodle_url;

/**
 * Class CartHelper
 * Provides utility functions for managing shopping cart-related operations.
 */
class CartHelper
{
    /**
     * Retrieve the configuration setting for enrol_cart.
     *
     * @param string|null $name The name of the configuration setting to retrieve.
     * @return mixed The configuration value or null if not found.
     */
    public static function getConfig($name = null)
    {
        static $config;

        if (!isset($config)) {
            $config = get_config('enrol_cart');
        }

        if ($name) {
            return $config->$name ?? null;
        }

        return $config;
    }

    /**
     * Return the first cart enrolment instance ID of a course.
     *
     * @param int $courseId The ID of the course.
     * @return int The cart enrolment instance ID of the course.
     */
    public static function getCourseInstanceId(int $courseId): int
    {
        global $DB;
        $instances = $DB->get_records(
            'enrol',
            [
                'courseid' => $courseId,
                'enrol' => 'cart',
                'status' => ENROL_INSTANCE_ENABLED,
            ],
            'sortorder ASC',
            'id',
        );
        foreach ($instances as $instance) {
            return $instance->id;
        }
        return 0;
    }

    /**
     * Return an active cart enrol record.
     *
     * @param int $instanceId The ID of the enrolment instance.
     * @return false|CartEnrollmentInstance The cart enrolment record or false if not found.
     */
    public static function getInstance(int $instanceId)
    {
        static $cache = [];
        if (!isset($cache[$instanceId])) {
            $instance = CartEnrollmentInstance::findOneById($instanceId);
            if (
                (!$instance->enrol_start_date || $instance->enrol_start_date < time()) &&
                (!$instance->enrol_end_date || $instance->enrol_end_date > time())
            ) {
                $cache[$instanceId] = $instance;
            }
        }
        return $cache[$instanceId];
    }

    /**
     * Return true if the enrol method exists.
     *
     * @param int $instanceId The ID of the enrolment instance.
     * @return bool Returns true if the enrol method exists, false otherwise.
     */
    public static function hasInstance(int $instanceId): bool
    {
        return !!self::getInstance($instanceId);
    }

    /**
     * Check if a user is enrolled in a specified instance.
     *
     * @param int $instanceId The ID of the enrolment instance.
     * @param int $userId The ID of the user.
     * @param bool $anyInstance (Optional) Whether to check enrollment in any instance of the course.
     * @param bool $onlyActive (Optional) Whether to check only active enrollments.
     * @return bool Returns true if the user is enrolled, otherwise false.
     */
    public static function isUserEnrolled(
        int $instanceId,
        int $userId,
        bool $anyInstance = false,
        bool $onlyActive = false
    ): bool {
        global $DB;

        // If anyInstance flag is set and the instance exists, check enrollment in the course
        if ($anyInstance && self::hasInstance($instanceId)) {
            $instance = self::getInstance($instanceId);
            $course = $DB->get_record('course', ['id' => $instance->course_id], '*', MUST_EXIST);
            $context = context_course::instance($course->id);
            return is_enrolled($context, $userId, '', $onlyActive); // Check enrollment in the course context
        }

        // Otherwise, check enrollment in the specified instance
        return $DB->record_exists('user_enrolments', [
            'enrolid' => $instanceId,
            'userid' => $userId,
        ]);
    }

    /**
     * Move the not-authenticated user cookie cart to the database when the user logs in.
     *
     * @return void
     * @see UserObserver::userLoggedIn()
     */
    public static function moveCookieCartToDB()
    {
        global $USER;

        $cookieCart = new CookieCart();

        if (empty($cookieCart->items) || !$USER->id || isguestuser()) {
            return;
        }

        $cart = Cart::findCurrent(true);
        foreach ($cookieCart->items as $item) {
            $cart->addItem($item->instance_id);
        }

        $cookieCart->flush();
    }

    /**
     * Return the cart object.
     *
     * @param bool $forceNew Create an active cart on the database for the current user.
     * @return Cart|CookieCart|null The shopping cart object.
     */
    public static function getCurrent(bool $forceNew = false)
    {
        global $USER;

        static $current = null;

        if (!$current) {
            if (!$USER->id || isguestuser()) {
                $current = new CookieCart();
            } else {
                $current = Cart::findCurrent($forceNew);
            }
        }

        return $current;
    }

    /**
     * Add a course to the current cart.
     *
     * @param int $courseId The ID of the course to add.
     * @return bool Returns true if the course was added successfully, otherwise false.
     */
    public static function addCourseToCart(int $courseId): bool
    {
        $cart = self::getCurrent(true);
        return $cart->addCourse($courseId);
    }

    /**
     * Add an enrolment instance to the current cart.
     *
     * @param int $instanceId The ID of the enrolment instance to add.
     * @return bool Returns true if the instance was added successfully, otherwise false.
     */
    public static function addInstanceToCart(int $instanceId): bool
    {
        $cart = self::getCurrent(true);
        return $cart->addItem($instanceId);
    }

    /**
     * Remove a course from the current cart.
     *
     * @param int $courseId The ID of the course to remove.
     * @return bool Returns true if the course was removed successfully, otherwise false.
     */
    public static function removeCourseFromCart(int $courseId): bool
    {
        $cart = self::getCurrent(true);
        return $cart->removeCourse($courseId);
    }

    /**
     * Remove an enrolment instance from the current cart.
     *
     * @param int $instanceId The ID of the enrolment instance to remove.
     * @return bool Returns true if the instance was removed successfully, otherwise false.
     */
    public static function removeInstanceFromCart(int $instanceId): bool
    {
        $cart = self::getCurrent(true);
        return $cart->removeItem($instanceId);
    }

    /**
     * Get the URL for the cart view page.
     *
     * @param int|null $id The ID of the cart to view, or null for the current cart.
     * @return moodle_url The URL of the cart view page.
     */
    public static function getCartViewUrl(?int $id = null): moodle_url
    {
        $params = $id ? ['id' => $id] : null;
        return new moodle_url('/enrol/cart/view.php', $params);
    }

    /**
     * Get the URL for the cart checkout page.
     *
     * @param int|null $id The ID of the cart for checkout, or null for the current cart.
     * @return moodle_url The URL of the cart checkout page.
     */
    public static function getCartCheckoutUrl(?int $id = null): moodle_url
    {
        $params = $id ? ['id' => $id] : null;
        return new moodle_url('/enrol/cart/checkout.php', $params);
    }
}
