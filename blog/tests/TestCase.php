<?php

use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var string
     */
    protected $token = '';

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    protected function auth(string $email = 'u1@u1.u1', string $password = 'upass123')
    {
        $this->post('/user/register', ['name' => 'u111', 'email' => $email, 'password' => $password]);
        $this->post('/user/login', ['email' => $email, 'password' => $password]);
        $this->token = $this->response->json('data')['access_token'];
    }

    public function aget(string $uri, array $data = [], array $headers = []) {
        if (!$this->token) {
            $this->auth();
        }

        if ($data) {
            $uri .= (strpos($uri, '?') ? '&' : '?') . http_build_query($data);
        }

        $this->get($uri, array_merge($headers, ['Authorization' => $this->token]));
    }

    public function apost(string $uri, array $data = [], array $headers = []) {
        if (!$this->token) {
            $this->auth();
        }

        $this->post($uri, $data, array_merge($headers, ['Authorization' => $this->token]));
    }

    public function aput(string $uri, array $data = [], array $headers = []) {
        if (!$this->token) {
            $this->auth();
        }

        $this->put($uri, $data, array_merge($headers, ['Authorization' => $this->token]));
    }

    public function adelete(string $uri, array $data = [], array $headers = []) {
        if (!$this->token) {
            $this->auth();
        }

        $this->delete($uri, $data, array_merge($headers, ['Authorization' => $this->token]));
    }
}
