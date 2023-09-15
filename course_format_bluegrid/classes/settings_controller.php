<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace format_bluegrid;

defined('MOODLE_INTERNAL') || die;

/**
 * Format BlueGrid settings controller class.
 *
 * @package   format_bluegrid
 * @copyright 2020 onwards, Univates
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Christian Bayer (christian.bayer@universo.univates.br)
 */
class SettingsController
{
    protected static $instance;

    /**
     * Constructor
     *
     * Init the required properties.
     */
    private function __construct() {
        $this->plugin_config = "format_bluegrid";
    }

    /**
     * Singleton Implementation.
     *
     * @return SettingsController
     */
    public static function getinstance() {
        if( ! is_object(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Finds the given setting in the plugin from the plugin's configuration object.
     * @param string $setting Setting name.
     * @return mixed defaultvalue|value of setting.
     * @throws \dml_exception
     */
    public function getsetting($setting) {
        $config = get_config($this->plugin_config);
        if(property_exists($config, $setting)) {
            return $config->$setting;
        } else {
            return 0;
        }
    }

}
