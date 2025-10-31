<?php
namespace LadyByron\ArrowChat\Controllers;

use Flarum\Http\RequestUtil;
use Flarum\User\User;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class WhoAmIController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $actor = RequestUtil::getActor($request);

            if (!$actor instanceof User || !$actor->exists) {
                return new JsonResponse(['error' => 'guest'], 401);
            }

            $forumUrl = $this->forumBaseUrl();
            $groups   = $actor->groups()->pluck('name_singular')->all();

            return new JsonResponse([
                'id'          => (int) $actor->id,
                'username'    => (string) $actor->username,
                'displayName' => (string) ($actor->display_name ?? $actor->username),
                'avatarUrl'   => $actor->avatar_url ?: null,
                'profileUrl'  => rtrim($forumUrl, '/') . '/u/' . $actor->username,
                'isAdmin'     => (bool) $actor->isAdmin(),
                'groups'      => $groups,
            ], 200);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'internal'], 500);
        }
    }

    private function forumBaseUrl(): string
    {
        // 优先 flarum.forum.url，兜底 config['url']
        $url = '';
        try { $url = resolve('flarum.forum.url'); } catch (\Throwable $e) {}
        if (!$url) {
            try { $cfg = resolve('flarum.config'); $url = $cfg['url'] ?? ''; } catch (\Throwable $e) {}
        }
        return (string) $url;
    }
}
