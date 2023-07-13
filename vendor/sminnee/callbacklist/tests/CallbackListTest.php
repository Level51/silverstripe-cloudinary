<?php declare(strict_types = 1);

namespace Sminnee\CallbackList\Tests;

use PHPUnit\Framework\TestCase;
use Sminnee\CallbackList\CallbackList;

/** @covers Sminnee\CallbackList\CallbackList */
class CallbackListTest extends TestCase
{
    public function testCallWithoutReturnVales(): void
    {
        $list = new CallbackList();

        $log = [];

        // Confirming that voids are allowed even though returns are collected
        $list->add(static function () use (&$log): void {
            $log[] = 'a';
        });
        $list->add(static function () use (&$log): void {
            $log[] = 'b';
        });
        $list->add(static function () use (&$log): void {
            $log[] = 'c';
        });

        // When there are no returns form the callbacks, an array of nulls is returned
        $this->assertEquals([null, null, null], $list->call());
        $this->assertEquals(['a', 'b', 'c'], $log);
    }


    public function testCallReturnValues(): void
    {
        $list = new CallbackList();

        $list->add(static function () {
            return 'a';
        });
        $list->add(static function () {
            return 2;
        });
        $list->add(static function () {
            return ['c'];
        });

        // An array of return values, including mixed return types, is returned
        $this->assertEquals(
            ['a', 2, ['c']],
            $list->call()
        );

        // Check invoke syntax
        $this->assertEquals(
            ['a', 2, ['c']],
            $list()
        );
    }


    public function testCallWithArgs(): void
    {
        $list = new CallbackList();

        $log = [];
        $list->add(static function ($greeting, $punctuation = '') use (&$log): void {
            $log[] = "$greeting, a$punctuation";
        });
        $list->add(static function ($greeting, $punctuation = '') use (&$log): void {
            $log[] = "$greeting, b$punctuation";
        });
        $list->add(static function ($greeting, $punctuation = '') use (&$log): void {
            $log[] = "$greeting, c$punctuation";
        });

        $log = [];
        $list->call('Hello');
        $this->assertEquals(['Hello, a', 'Hello, b', 'Hello, c'], $log);

        $log = [];
        $list->call('Hello', '!');
        $this->assertEquals(['Hello, a!', 'Hello, b!', 'Hello, c!'], $log);

        // Check invoke syntax
        $log = [];
        $list('Hello');
        $this->assertEquals(['Hello, a', 'Hello, b', 'Hello, c'], $log);

        $log = [];
        $list('Hello', '!');
        $this->assertEquals(['Hello, a!', 'Hello, b!', 'Hello, c!'], $log);
    }


    public function testGetAll(): void
    {
        $a = static function (): void {
            echo 'a';
        };
        $b = static function (): void {
            echo 'b';
        };

        $list = new CallbackList();
        $list->add($a);
        $list->add($b);

        $this->assertEquals([$a, $b], $list->getAll());
    }


    public function testGetNamed(): void
    {
        $a = static function (): void {
            echo 'a';
        };
        $b = static function (): void {
            echo 'b';
        };

        $list = new CallbackList();
        $list->add($a, 'a');
        $list->add($b, 'b');

        $this->assertEquals($a, $list->get('a'));
        $this->assertEquals($a, $list->get('b'));
    }


    public function testRemoveNamed(): void
    {
        $a = static function (): void {
            echo 'a';
        };
        $b = static function (): void {
            echo 'b';
        };

        $list = new CallbackList();
        $list->add($a, 'a');
        $list->add($b, 'b');
        $list->remove('a');

        $this->assertEquals([$b], $list->getAll());
    }


    public function testClear(): void
    {
        $a = static function (): void {
            echo 'a';
        };
        $b = static function (): void {
            echo 'b';
        };
        $c = static function (): void {
            echo 'c';
        };

        $list = new CallbackList();
        $list->add($a);
        $list->add($b);
        $list->clear();
        $list->add($c);

        $this->assertEquals([$c], $list->getAll());
    }


    public function testCallbackIsACallable(): void
    {
        $list = new CallbackList();

        $this->assertTrue(\is_callable($list));

        $test = static function (callable $x) {
            return $x();
        };

        $this->assertEquals($list(), $test($list));
    }


    public function testEmptyList(): void
    {
        $list = new CallbackList();
        $this->assertEquals([], $list());
    }
}
