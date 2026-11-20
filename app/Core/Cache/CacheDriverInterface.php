<?php
namespace App\Core\Cache;

interface CacheDriverInterface
{
    public function has(string $key) : bool;

    public function get(string $key): mixed;

    public function mget(array $keys): array;

    public function set(string $key, mixed $value, ?int $expires = null): bool;

    public function expire(string $key, int $seconds);

    public function delete(string|array $key): int;

    public function incr(string $key, int $number): int;

    public function decr(string $key, int $number): int;
}
