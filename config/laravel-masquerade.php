<?php

return [

    /**
     * The session key used to store the original user id.
     */
    'session_key' => 'masquerade_by',

    /**
     * The session key used to store the original user guard.
     */
    'session_guard' => 'masquerade_guard',

    /**
     * The session key used to stored what guard is masquerade using.
     */
    'session_guard_using' => 'masquerade_guard_using',

    /**
     * The default masquerade guard used.
     */
    'default_masquerade_guard' => 'web',

    /**
     * The URI to redirect after taking a masquerade.
     *
     * Only used in the built-in controller.
     * * Use 'back' to redirect to the previous page
     */
    'take_redirect_to' => '/',

    /**
     * The URI to redirect after leaving a masquerade.
     *
     * Only used in the built-in controller.
     * Use 'back' to redirect to the previous page
     */
    'leave_redirect_to' => '/',

];