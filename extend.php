<?php
use Flarum\Extend;

return [
    (new Extend\Routes('api'))
        ->get('/arrowchat/whoami', 'lady-byron.arrowchat.whoami', \LadyByron\ArrowChat\Controllers\WhoAmIController::class)
        ->get('/arrowchat/users/{id:[0-9]+}', 'lady-byron.arrowchat.user', \LadyByron\ArrowChat\Controllers\UserInfoController::class),
];
