<?php
namespace LadyByron\ArrowChat\Middleware;

use Flarum\Http\RequestUtil;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\ConnectionInterface as DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Heartbeat implements MiddlewareInterface
{
    protected Cache $cache;
    protected DB $db;

    public function __construct(Cache $cache, DB $db)
    {
        $this->cache = $cache;
        $this->db    = $db;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);

        if ($actor && $actor->exists) {
            $key = 'ac_ping_user_'.$actor->id;

            // 60 秒内只写一次，避免频繁写库
            if (!$this->cache->has($key)) {
                $this->cache->put($key, 1, 60);

                $now     = time();
                $isAdmin = $actor->isAdmin() ? 1 : 0;

                // 写入/刷新 arrowchat_status（若你的表名带前缀，请改成实际表名）
                $this->db->table('arrowchat_status')->updateOrInsert(
                    ['userid' => (int) $actor->id],
                    [
                        'session_time' => $now,
                        'is_admin'     => $isAdmin,
                        // 下面字段根据你表结构而定：大多数安装允许为 NULL 或有默认值
                        'status'       => 'available',
                    ]
                );
            }
        }

        return $handler->handle($request);
    }
}
