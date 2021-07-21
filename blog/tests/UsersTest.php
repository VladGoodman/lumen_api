<?php

use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UsersTest extends TestCase
{
    use DatabaseTransactions;
    use DatabaseMigrations;
    use CreateEntityByRequest;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUnknownUser()
    {
        $this->post('/user/login');

        $this->assertArrayHasKey('email', $this->response->json());
        $this->assertArrayHasKey('password', $this->response->json());
        $this->assertStringContainsString('required', $this->response->getContent());

        $this->post('/user/login', ['email' => 'u1@u1.u1', 'password' => 'upass']);

        $this->assertResponseStatus(401);
    }

    public function testRegisterUser()
    {
        $this->post('/user/register', ['name' => '', 'email' => 'u1@u1.u1', 'password' => 'upass']);
        $this->assertResponseStatus(422, 'wrong parameters - short name!');
        $this->assertStringContainsString('name', $this->response->getContent(), 'short name allowed');

        $this->post('/user/register', ['name' => 'u112', 'email' => 'u1@u1.u1', 'password' => 'upass123']);
        $this->isTrue(in_array($this->response->getStatusCode(), [200, 201]));
        $this->seeInDatabase('users', ['name' => 'u112', 'email' => 'u1@u1.u1']);
        $this->notSeeInDatabase('users', ['password' => 'upass123']);

        $this->post('/user/register', ['name' => 'u112', 'email' => 'u1@u1.u1', 'password' => 'upass123']);
        $this->assertResponseStatus(422, 'same user registered');
        $this->assertStringContainsString('already', $this->response->getContent(), 'same user registered');
    }

    public function testLoginUser()
    {
        $this->post('/user/register', ['name' => 'u112', 'email' => 'u1@u1.u1', 'password' => 'upass123']);

        $this->post('/user/login', ['email' => 'u1@u1.u1', 'password' => 'upass123']);
        $this->assertResponseOk();
        $this->assertArrayHasKey('access_token', $this->response->json('data'));
        $this->seeInDatabase('users', ['name' => 'u112', 'email' => 'u1@u1.u1']);
    }

    public function testUsersFiltersGet()
    {
        $this->post('/user/register', ['name' => 'u112', 'email' => 'u2@u1.u1', 'password' => 'upass123']);
        $this->post('/user/register', ['name' => 'u113', 'email' => 'u3@u1.u1', 'password' => 'upass123']);
        $this->post('/user/register', ['name' => 'u114', 'email' => 'u4@u1.u1', 'password' => 'upass123']);

        $this->aget('/user');
        $this->assertCount(4, $this->response->json('data')['items']);

        $this->aget('/user', ['filter' => [['name', '=', 'u112']]]);
        $this->assertCount(1, $this->response->json('data')['items']);

        $this->aget('/user', ['filter' => [['name', '>=', 'u112']]]);
        $this->assertCount(3, $this->response->json('data')['items']);

        $this->aget('/user', ['filter' => [['name', '>=', 'u112'], ['email', '<=', 'u3@u1.u1']]]);
        $this->assertCount(2, $this->response->json('data')['items']);

        $this->aget('/user', ['order' => [['name', 'desc']]]);
        $this->assertCount(4, $this->response->json('data')['items']);
        $this->response->json()['data']['items'][0]['name'] = 'u114';

        $this->aget('/user', ['per_page' => 2, 'page' => 2]);
        $this->assertCount(2, $this->response->json('data')['items']);
    }

    public function testFailedUsersFiltersGet()
    {
        $this->post('/user/register', ['name' => 'u112', 'email' => 'u2@u1.u1', 'password' => 'upass123']);
        $this->post('/user/register', ['name' => 'u113', 'email' => 'u3@u1.u1', 'password' => 'upass123']);
        $this->post('/user/register', ['name' => 'u114', 'email' => 'u4@u1.u1', 'password' => 'upass123']);

        $this->aget('/user', ['filter' => [['name', 'jhljh', 'u112']]]);
        $this->assertCount(0, $this->response->json('data')['items']);
    }

}
