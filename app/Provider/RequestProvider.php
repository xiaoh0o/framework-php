<?php
namespace App\Provider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use App\Core\Http\Request;

class RequestProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        $array = [
            "request",
            Request::class
        ];
        return in_array($id, $array, true);
    }

    public function register(): void
    {
        $request = Request::createFromGlobals();
        $this->getContainer()->addShared(Request::class, $request);
        $this->getContainer()->addShared("request",      $request);
    }
}
