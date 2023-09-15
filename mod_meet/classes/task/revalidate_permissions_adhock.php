<?php

namespace mod_meet\task;

class revalidate_permissions_adhock extends \core\task\adhoc_task {

    /**
     * Método que será executado pela cron
     */
    public function execute() {
        global $CFG;

        require_once($CFG->dirroot . '/mod/meet/lib.php');

        $data = $this->get_custom_data();

        mtrace( print_r($data, true) );

        switch ($data->action) {
            case 'add':
                mtrace('User: ' . $data->user . ' being remanaged ');
                foreach ($data->meets as $meet) {
                    mtrace('User: ' . $data->user . ' invited to meet ' . $meet);
                    meet_add_user_to_event($meet, $data->user);
                }
                break;
            case 'remove':
                mtrace('User: ' . $data->user . ' being remanaged ');
                foreach ($data->meets as $meet) {
                    mtrace('User: ' . $data->user . ' removed for meet ' . $meet);
                    meet_remove_user_from_event($meet, $data->user);
                }
                break;
            default:
                foreach ($data->meets as $meet) {
                    mtrace('Meet: '. $meet . ' being refreshed');
                    meet_refresh_users($meet);
                }
                break;
        }
        mtrace('Done');
 
    }
}
