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

/**
 * @package   media_kinescope
 * @copyright 2023 LMS-Service {@link https://lms-service.ru/}
 * @author    Nikita Badin
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_media_kinescope_install() {
    global $CFG;

    // Enabling the plugin during installation
    $enabled = true;
    $pluginname = 'kinescope';

    $haschanged = false;
    $plugins = [];
    if (!empty($CFG->media_plugins_sortorder)) {
        $plugins = explode(',', $CFG->media_plugins_sortorder);
    }
    // Only set visibility if it's different from the current value.
    if ($enabled && !in_array($pluginname, $plugins)) {
        // Enable media plugin.

        /** @var \core\plugininfo\media[] $pluginsbytype */
        $pluginsbytype = \core_plugin_manager::instance()->get_plugins_of_type('media');
        if (!array_key_exists($pluginname, $pluginsbytype)) {
            // Can not be enabled.
            return false;
        }

        $rank = $pluginsbytype[$pluginname]->get_rank();
        $position = 0;
        // Insert before the first enabled plugin which default rank is smaller than the default rank of this one.
        foreach ($plugins as $playername) {
            if (($player = $pluginsbytype[$playername]) && ($rank > $player->get_rank())) {
                break;
            }
            $position++;
        }
        array_splice($plugins, $position, 0, [$pluginname]);
        $haschanged = true;
    } else if (!$enabled && in_array($pluginname, $plugins)) {
        // Disable media plugin.
        $key = array_search($pluginname, $plugins);
        unset($plugins[$key]);
        $haschanged = true;
    }

    if ($haschanged) {
        add_to_config_log('media_plugins_sortorder', !$enabled, $enabled, $pluginname);
        if (empty($plugins)) {
            $list = [];
        } else if (!is_array($plugins)) {
            $list = explode(',', $plugins);
        } else {
            $list = $plugins;
        }

        set_config('media_plugins_sortorder', join(',', $list));
        \core_plugin_manager::reset_caches();
        \core_media_manager::reset_caches();
    }

}
