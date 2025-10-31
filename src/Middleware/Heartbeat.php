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

                // ★ 关键：使用原生 SQL，避免 Laravel 自动追加表前缀
                try {
                    // 先尝试更新；没更新到再插入
                    $updated = $this->db->update(
                        'UPDATE `arrowchat_status`
                         SET `session_time` = ?, `is_admin` = ?, `status` = ?
                         WHERE `userid` = ?',
                        [$now, $isAdmin, 'available', (int) $actor->id]
                    );

                    if ($updated === 0) {
                        $this->db->insert(
                            'INSERT INTO `arrowchat_status` (`userid`, `session_time`, `is_admin`, `status`)
                             VALUES (?, ?, ?, ?)',
                            [(int) $actor->id, $now, $isAdmin, 'available']
                        );
                    }
                } catch (\Throwable $e) {
                    // 静默失败即可（不影响页面），如需调试可写日志：
                    // resolve('log')->error('AC heartbeat error: '.$e->getMessage());
                }
            }
        }

        return $handler->handle($request);
    }
}
