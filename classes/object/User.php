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

namespace enrol_cart\object;

defined('MOODLE_INTERNAL') || die();

use moodle_url;

/**
 * Class User
 *
 * Represents a user in the system and extends functionality from BaseModel.
 *
 * @property int $id The ID of the user.
 * @property string $username The username of the user.
 * @property string $email The email of the user.
 * @property string $first_name The first name of the user.
 * @property string $last_name The last name of the user.
 *
 * @property string $fullName The full name of the user.
 * @property string $profileUrl The url of the user profile.
 */
class User extends BaseModel
{
    /**
     * Retrieves the attributes of the course.
     *
     * @return array An array of course attributes including id, name, and title.
     */
    public function attributes(): array
    {
        return ['id', 'username', 'email', 'first_name', 'last_name'];
    }

    /**
     * Finds a user by its user ID.
     *
     * @param int $userId The user ID of the user.
     * @return null|self The User object corresponding to the user ID.
     */
    public static function findOneId(int $userId): ?self
    {
        global $DB;

        // SQL query to retrieve user details based on user ID.
        $row = $DB->get_record_sql(
            'SELECT id, username, email, firstname as first_name, lastname as last_name
                 FROM {user} u 
                 WHERE id = :id',
            ['id' => $userId],
        );

        return $row ? static::populateOne($row) : null;
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getProfileUrl(): moodle_url
    {
        return new moodle_url('/user/profile.php', ['id' => $this->id]);
    }
}
