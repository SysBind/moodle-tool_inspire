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
 * Cognitive depth indicator - chat.
 *
 * @package   tool_inspire
 * @copyright 2017 David Monllao {@link http://www.davidmonllao.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_inspire\local\indicator\chat;

defined('MOODLE_INTERNAL') || die();

/**
 * Cognitive depth indicator - chat.
 *
 * @package   tool_inspire
 * @copyright 2017 David Monllao {@link http://www.davidmonllao.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cognitive_depth extends \tool_inspire\local\indicator\activity_cognitive_depth {

    public static function get_name() {
        return get_string('indicator:cognitivedepthchat', 'tool_inspire');
    }

    protected function get_activity_type() {
        return 'chat';
    }

    public function get_cognitive_depth_level(\cm_info $cm) {
        return 4;
    }

    protected function feedback_viewed_events() {
        return array('\mod_chat\event\course_module_viewed', '\mod_chat\event\message_sent',
            '\mod_chat\event\sessions_viewed');
    }

    protected function feedback_replied_events() {
        return array('\mod_chat\event\message_sent');
    }

    protected function feedback_post_action(\cm_info $cm, $contextid, $userid, $eventnames, $after = false) {

        if (empty($this->activitylogs[$contextid][$userid])) {
            return false;
        }

        $logs = $this->activitylogs[$contextid][$userid];

        if (empty($logs['\mod_chat\event\message_sent'])) {
            // No feedback viewed if there is no submission.
            return false;
        }

        // First user message time.
        $firstmessage = $logs['\mod_chat\event\message_sent']->timecreated[0];

        // We consider feedback another user messages.
        foreach ($this->activitylogs[$contextid] as $anotheruserid => $logs) {
            if ($anotheruserid == $userid) {
                continue;
            }
            if (empty($logs['\mod_chat\event\message_sent'])) {
                continue;
            }
            $firstmessagesenttime = $logs['\mod_chat\event\message_sent']->timecreated[0];

            if (parent::feedback_post_action($cm, $contextid, $userid, $eventnames, $firstmessagesenttime)) {
                return true;
            }
            // Continue with the next user.
        }

        return false;
    }

    protected function feedback_check_grades() {
        // Chat's feedback is not contained in grades.
        return false;
    }
}
