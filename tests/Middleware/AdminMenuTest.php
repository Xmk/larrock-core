<?php

namespace Larrock\Core\Tests\Middleware;

use Illuminate\Http\Request;
use Larrock\Core\Middleware\AdminMenu;
use Orchestra\Testbench\TestCase;

class AdminMenuTest extends TestCase
{
    public function testHandle()
    {
        $adminMenu = new AdminMenu();
        $request = new Request();
        $test = $adminMenu->handle($request, function (){
            return 'test';
        });
        $this->assertEquals('test', $test);
    }
}