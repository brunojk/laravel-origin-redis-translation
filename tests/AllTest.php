<?php

class AllTest extends TestCase
{
    public function tearDown() {
        $this->redis->flushDB();
        parent::tearDown();
    }

    public function testConnection() {
        $this->assertNotNull($this->redis);
    }

    public function testReplacements() {
        $this->redis->set('app.en.default.hello_world', 'Hello World, :name');
        $this->redis->set('app.pt.default.hello_world', 'Olá Mundo, :name');
        $this->redis->set('app.es.default.hello_world', 'Hola Mondo, :name');

        $this->assertEquals('Hello World, Bruno', trans('hello_world', ['name' => 'Bruno']));
        $this->assertEquals('Olá Mundo, Bruno', trans('hello_world', ['name' => 'Bruno'], null, 'pt'));
        $this->assertEquals('Hola Mondo, Bruno', trans('hello_world', ['name' => 'Bruno'], null, 'es'));
        $this->assertEquals('Hello World, Bruno', trans('hello_world', ['name' => 'Bruno'], null, 'ru'));

        $this->redis->del('app.en.default.hello_world');
        $this->redis->del('app.pt.default.hello_world');
        $this->redis->del('app.es.default.hello_world');
        $this->redis->set('plt.en.default.hello_world', 'Hello World, (plt) :name');
        $this->assertEquals('Hello World, (plt) Bruno', trans('hello_world', ['name' => 'Bruno']));
    }

    public function testChoice() {
        $this->redis->set('app.en.default.hello_world', '{0} Hello World|[1,Inf] Hellos Worlds');

        $this->assertEquals('Hello World', trans_choice('hello_world', 0));
        $this->assertEquals('Hellos Worlds', trans_choice('hello_world', 1));
        $this->assertEquals('Hellos Worlds', trans_choice('hello_world', 2));
        $this->assertEquals('Hellos Worlds', trans_choice('hello_world', 2, [], null, 'es'));
        $this->assertEquals('Hellos Worlds', trans_choice('hello_world', 2, [], null, 'pt'));


        $this->redis->del('app.en.default.hello_world');
        $this->assertEquals('default.hello_world', trans_choice('hello_world', 2, [], null, 'es'));


        $this->redis->set('app.en.default.hello_world', 'Hello World');
        $this->assertEquals('Hello World', trans_choice('hello_world', 0));
        $this->assertEquals('Hello World', trans_choice('hello_world', 1));
        $this->assertEquals('Hello World', trans_choice('hello_world', 5));


        $this->redis->del('app.en.default.hello_world');
        $this->redis->set('app.pt.default.hello_world', 'Olá mundo');

        $this->assertEquals('default.hello_world', trans_choice('hello_world', 0));
        $this->assertEquals('default.hello_world', trans_choice('hello_world', 1));
        $this->assertEquals('default.hello_world', trans_choice('hello_world', 5));

        $this->assertEquals('default.hello_world', trans_choice('hello_world', 0, [], null, 'es'));
        $this->assertEquals('default.hello_world', trans_choice('hello_world', 1, [], null, 'es'));
        $this->assertEquals('default.hello_world', trans_choice('hello_world', 5, [], null, 'es'));

        $this->assertEquals('Olá mundo', trans_choice('hello_world', 0, [], null, 'pt'));
        $this->assertEquals('Olá mundo', trans_choice('hello_world', 1, [], null, 'pt'));
        $this->assertEquals('Olá mundo', trans_choice('hello_world', 5, [], null, 'pt'));
    }

    public function testCascadeOrigins() {
        $this->redis->set('plt.en.default.hello_world', 'Hello World plt');
        $this->redis->set('plt.pt.default.hello_world', 'Olá mundo plt');
        $this->redis->set('plt.es.default.hello_world', 'Hola mondo plt');

        $this->assertEquals('Hello World plt', trans('hello_world'));
        $this->assertEquals('Olá mundo plt', trans('hello_world', [], null, 'pt'));
        $this->assertEquals('Hola mondo plt', trans('hello_world', [], null, 'es'));

        $this->redis->set('app.en.default.hello_world', 'Hello World');
        $this->assertEquals('Hello World', trans('hello_world'));
        $this->assertNotEquals('Hello World', trans('hello_world', [], null, 'pt'));
    }

    public function testCascadeLang() {
        $this->redis->set('app.en.default.hello_world', 'Hello World');
        $this->redis->set('app.pt.default.hello_world', 'Olá mundo');
        $this->redis->set('app.es.default.hello_world', 'Hola mondo');

        $this->assertEquals('Hello World', trans('hello_world'));
        $this->assertEquals('Olá mundo', trans('hello_world', [], null, 'pt'));
        $this->assertEquals('Hola mondo', trans('hello_world', [], null, 'es'));

        $this->redis->del('app.pt.default.hello_world');
        $this->assertEquals('Hello World', trans('hello_world', [], null, 'pt'));

        $this->redis->del('app.en.default.hello_world');
        $this->assertEquals('default.hello_world', trans('hello_world'));
        $this->assertEquals('default.hello_world', trans('hello_world', [], null, 'pt'));
        $this->assertNotEquals('default.hello_world', trans('hello_world', [], null, 'es'));

    }

    public function testDefaultOrigins() {
        $this->redis->set('plt.en.default.hello_world', 'Hello World plt');
        $this->assertEquals('Hello World plt', trans('hello_world'));
        $this->assertEquals('Hello World plt', trans('default.hello_world'));
        $this->assertEquals('Hello World plt', trans('hello_world', [], 'default'));

        $this->redis->set('app.en.default.hello_world', 'Hello World');
        $this->assertEquals('Hello World', trans('hello_world'));
        $this->assertEquals('Hello World', trans('default.hello_world'));
        $this->assertEquals('Hello World', trans('hello_world', [], 'default'));
    }

    public function testDefault() {
        $this->redis->set('app.en.default.hello_world', 'Hello World');
        $this->assertEquals('Hello World', trans('hello_world'));
        $this->assertEquals('Hello World', trans('default.hello_world'));
        $this->assertEquals('Hello World', trans('hello_world', [], 'default'));

        //update
        $this->redis->set('app.en.default.hello_world', 'Hello World Updated!');
        $this->assertEquals('Hello World Updated!', trans('hello_world'));
        $this->assertEquals('Hello World Updated!', trans('default.hello_world'));
        $this->assertEquals('Hello World Updated!', trans('hello_world', [], 'default'));

        //delete
        $this->redis->del('app.en.default.hello_world');
        $this->assertEquals('default.hello_world', trans('hello_world'));
        $this->assertEquals('default.hello_world', trans('default.hello_world'));
        $this->assertEquals('default.hello_world', trans('default.hello_world', [], 'outcome'));
        $this->assertNotEquals('default.hello_world', trans('hello_world', [], 'outcome'));

        $this->redis->set('app.en.default.hello_world', 'Hello World!');
        $this->assertNotEquals('Hello World!', trans('outcome.hello_world'));
        $this->assertEquals('Hello World!', trans('default.hello_world', [], 'outcome'));
        $this->assertEquals('Hello World!', trans('hello_world'));
    }
}
