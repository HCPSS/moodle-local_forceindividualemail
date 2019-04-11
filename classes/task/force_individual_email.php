<?php

namespace local_forceindividualemail\task;

use core\task\scheduled_task;

class force_individual_email extends scheduled_task {

    /**
     * @var \moodle_database
     */
    private $db;

    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    /**
     * {@inheritDoc}
     * @see \core\task\scheduled_task::get_name()
     */
    public function get_name() {
        return get_string('force_subscriptions', 'local_forceindividualemail');
    }

    /**
     * Get a list of user ids for users who do not have a subscription override
     * for the give forum id.
     *
     * @param int $forumid
     * @return array
     */
    private function get_users_without_subscription($forumid) {
        $results = $this->db->get_records_sql('
            SELECT u.id
            FROM {user} u
            LEFT JOIN {forum_digests} f
                ON u.id = f.userid AND f.forum = :forumid
            WHERE f.maildigest IS NULL
        ', ['forumid' => $forumid]);

        return array_keys($results);
    }

    /**
     * Subscribe the given users to the given forum for individual emails.
     *
     * @param array $userids
     * @param int $formumid
     */
    private function subscribe_users_to_individual_emails($userids, $formumid) {
        $records = array_map(function ($userid) use ($formumid) {
            return (object)array(
                'userid'     => $userid,
                'forum'      => $formumid,
                'maildigest' => 0,
            );
        }, $userids);

        $this->db->insert_records('forum_digests', $records);
    }

    /**
     * {@inheritDoc}
     * @see \core\task\task_base::execute()
     */
    public function execute() {
        if ($config = get_config('local_forceindividualemail', 'forums')) {
            $ids = explode(',', $config);

            foreach ($ids as $id) {
                // Update all existing subscriptions.
                $this->db->set_field('forum_digests', 'maildigest', 0, array(
                    'forum' => $id,
                ));

                $userids = $this->get_users_without_subscription($id);
                $this->subscribe_users_to_individual_emails($userids, $id);
            }
        }
    }
}
