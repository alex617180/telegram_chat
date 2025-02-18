<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

class AuthTest extends ApiTestCase
{
    #[Test]
    public function it_should_login_and_return_token()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    #[Test]
    public function it_should_not_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    #[Test]
    public function it_should_return_user_info()
    {
        $response = $this->getJson('/api/v1/me', $this->authHeader());

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]);
    }

    #[Test]
    public function it_should_not_return_user_info_if_not_authenticated()
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized']);;
    }


    #[Test]
    public function it_should_logout_user()
    {
        $response = $this->postJson('/api/v1/logout', [], $this->authHeader());

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out']);
    }

    
    #[Test]
    public function it_should_logout_user_if_not_authenticated()
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized']);;
    }
}
