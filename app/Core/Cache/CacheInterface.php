<?php
namespace App\Core\Cache;

interface CacheInterface
{
    public function has(string|int $key): bool;

    public function get(string|int $key): mixed;

    public function mget(array $keys): array;

    public function set(string|int $key, mixed $value, ?int $expire = 0): bool;

    public function delete(string|int|array $key): int;

    public function incr(string|int $key, int $number): int;

    public function decr(string|int $key, int $number): int;
}
