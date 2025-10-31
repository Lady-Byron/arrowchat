<?php
namespace LadyByron\ArrowChat\Controllers;

use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class WhoAmIController
{
    public function handle(ServerRequestInterface $request)
    {
        $actor = RequestUtil::getActor($request);
        if (!$actor instanceof User || !$actor->exists) {
            return new JsonResponse(['error' => 'guest'], 401);
        }

        return new JsonResponse([
            'id'          => (int) $actor->id,
            'username'    => $actor->username,
            'displayName' => $actor->display_name,
            'avatarUrl'   => $actor->avatar_url,
            'profileUrl'  => app('flarum.forum.url') . '/u/' . $actor->username,
            'isAdmin'     => $actor->isAdmin(),
            'groups'      => $actor->groups->pluck('name_singular')->all(),
        ], 200);
    }
}
