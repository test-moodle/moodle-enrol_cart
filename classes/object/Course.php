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

use core_course_list_element;
use moodle_url;

/**
 * Class Course
 *
 * Represents a course in the system and extends functionality from BaseModel.
 *
 * @property int $id The ID of the course.
 * @property string $name The shortname of the course.
 * @property string $title The fullname of the course.
 * @property string $imageUrl The URL of the course image.
 */
class Course extends BaseModel
{
    private ?string $_imageUrl = null; // Private property to store the course image URL.

    /**
     * Retrieves the attributes of the course.
     *
     * @return array An array of course attributes including id, name, and title.
     */
    public function attributes(): array
    {
        return ['id', 'name', 'title'];
    }

    /**
     * Finds a course by its instance ID.
     *
     * @param int $instanceId The instance ID of the course.
     * @return null|self The Course object corresponding to the instance ID.
     */
    public static function findOneByInstanceId(int $instanceId): ?self
    {
        global $DB; // Global database object.

        // SQL query to retrieve course details based on instance ID.
        $row = $DB->get_record_sql(
            'SELECT c.id, c.shortname as name, c.fullname as title 
                 FROM {course} c 
                 INNER JOIN {enrol} e ON e.courseid = c.id 
                 WHERE e.id = :instance_id',
            ['instance_id' => $instanceId],
        );

        return $row ? static::populateOne($row) : null; // Returns a populated Course object.
    }

    /**
     * Retrieves the URL of the course image.
     *
     * @return string The URL of the course image.
     */
    public function getImageUrl(): string
    {
        if ($this->_imageUrl === null) {
            $this->_imageUrl = ''; // Initialize image URL to an empty string.

            // Create a new core_course_list_element object for the course.
            $courseListElement = new core_course_list_element(
                (object) [
                    'id' => $this->id,
                    'shortname' => $this->name,
                    'fullname' => $this->title,
                ],
            );

            // Iterate through course overview files to find valid images.
            foreach ($courseListElement->get_course_overviewfiles() as $file) {
                if ($file->is_valid_image()) {
                    // Check if the file is a valid image.
                    // Construct the path for the image URL.
                    $path = implode('/', [
                        '/pluginfile.php',
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                    ]);

                    // Generate and set the image URL using moodle_url class.
                    $this->_imageUrl = (new moodle_url($path))->out();
                }
            }
        }

        return $this->_imageUrl; // Return the URL of the course image.
    }
}
