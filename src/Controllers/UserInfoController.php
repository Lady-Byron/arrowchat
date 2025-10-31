<?php
namespace LadyByron\ArrowChat\Controllers;

use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UserInfoController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $id   = (int) ($request->getAttribute('id') ?? 0);
            $user = $id ? User::query()->find($id) : null;

            if (!$user) {
                return new JsonResponse(['error' => 'not_found'], 404);
            }

            $forumUrl = $this->forumBaseUrl();
            $groups   = $user->groups()->pluck('name_singular')->all();

            return new JsonResponse([
                'id'          => (int) $user->id,
                'username'    => (string) $user->username,
                'displayName' => (string) ($user->display_name ?? $user->username),
                'avatarUrl'   => $user->avatar_url ?: null,
                'profileUrl'  => rtrim($forumUrl, '/') . '/u/' . $user->username,
                'isAdmin'     => (bool) $user->isAdmin(),
                'groups'      => $groups,
            ], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'internal'], 500);
        }
    }

    private function forumBaseUrl(): string
    {
        $url = '';
        try { $url = resolve('flarum.forum.url'); } catch (\Throwable $e) {}
        if (!$url) {
            try { $cfg = resolve('flarum.config'); $url = $cfg['url'] ?? ''; } catch (\Throwable $e) {}
        }
        return (string) $url;
    }
}
