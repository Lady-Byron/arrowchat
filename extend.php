<?php
use Flarum\Extend;

return [
    (new Extend\Routes('api'))
        ->get('/arrowchat/whoami', 'lady-byron.arrowchat.whoami', \LadyByron\ArrowChat\Controllers\WhoAmIController::class)
        ->get('/arrowchat/users/{id:[0-9]+}', 'lady-byron.arrowchat.user', \LadyByron\ArrowChat\Controllers\UserInfoController::class),

    // ★ 新增：在论坛请求管线里加入我们的心跳中间件
    (new Extend\Middleware('forum'))
        ->add(\LadyByron\ArrowChat\Middleware\Heartbeat::class),
];
