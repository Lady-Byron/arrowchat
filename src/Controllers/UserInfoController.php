<?php
namespace LadyByron\ArrowChat\Controllers;

use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

class UserInfoController
{
    public function handle(ServerRequestInterface $request)
    {
        $id = (int) ($request->getAttribute('id') ?? 0);
        $user = User::query()->find($id);
        if (!$user) return new JsonResponse(['error' => 'not_found'], 404);

        return new JsonResponse([
            'id'          => (int) $user->id,
            'username'    => $user->username,
            'displayName' => $user->display_name,
            'avatarUrl'   => $user->avatar_url,
            'profileUrl'  => app('flarum.forum.url') . '/u/' . $user->username,
            'isAdmin'     => $user->isAdmin(),
            'groups'      => $user->groups->pluck('name_singular')->all(),
        ], 200);
    }
}
