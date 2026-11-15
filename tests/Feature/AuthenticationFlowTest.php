<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function member(): User
    {
        return User::factory()->create(['email' => 'member@example.test', 'password' => 'Original123!']);
    }

    public function test_authentication_forms_render_and_profile_requires_login(): void
    {
        foreach (['/login', '/register', '/forgot-password', '/reset-password/example-token'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/profile')->assertRedirect('/login');
        $this->actingAs($this->member())->get('/profile')->assertOk();
    }

    public function test_registration_hashes_password_and_ignores_privileged_fields(): void
    {
        $this->post('/register', [
            'name' => 'New Member', 'email' => 'NEW@example.test',
            'password' => 'Original123!', 'password_confirmation' => 'Original123!',
            'role' => 'admin', 'is_active' => false,
        ])->assertRedirect('/');
        $user = User::where('email', 'new@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('Original123!', $user->password));
        $this->assertSame('user', $user->role);
        $this->assertTrue($user->is_active);
        $this->get('/admin')->assertForbidden();
    }

    public function test_registration_rejects_duplicate_email_and_weak_password(): void
    {
        $this->member();
        $this->post('/register', ['name'=>'Another', 'email'=>'member@example.test', 'password'=>'short', 'password_confirmation'=>'short'])
            ->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
        $this->assertSame(1, User::count());
    }

    public function test_login_logout_and_suspension_with_remember_me(): void
    {
        $user = $this->member();
        $this->post('/login', ['email'=>'MEMBER@example.test', 'password'=>'Original123!', 'remember'=>true])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertNotEmpty($user->fresh()->remember_token);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $user->forceFill(['is_active'=>false])->save();
        $this->post('/login', ['email'=>$user->email, 'password'=>'Original123!'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_profile_email_changes_require_password_and_preserve_access_fields(): void
    {
        $user = $this->member();
        $user->forceFill(['email_verified_at'=>now()])->save();
        $this->actingAs($user)->put('/profile', ['name'=>'Changed', 'email'=>'new@example.test'])
            ->assertSessionHasErrors('current_password');
        $this->assertSame('member@example.test', $user->fresh()->email);
        $this->put('/profile', ['name'=>'Changed', 'email'=>'new@example.test', 'current_password'=>'Original123!', 'role'=>'admin', 'is_active'=>false])
            ->assertSessionHasNoErrors();
        $this->assertSame('new@example.test', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertSame('user', $user->fresh()->role);
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_password_change_checks_current_password(): void
    {
        $user = $this->member();
        $data = ['current_password'=>'wrong', 'password'=>'Replacement123!', 'password_confirmation'=>'Replacement123!'];
        $this->actingAs($user)->put('/profile/password', $data)->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('Original123!', $user->fresh()->password));
        $data['current_password'] = 'Original123!';
        $this->put('/profile/password', $data)->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Replacement123!', $user->fresh()->password));
    }

    public function test_reset_request_is_generic_and_reset_token_is_single_use(): void
    {
        Notification::fake();
        $user = $this->member();
        $this->post('/forgot-password', ['email'=>$user->email])->assertSessionHas('status');
        $knownStatus = session('status');
        Notification::assertSentTo($user, ResetPassword::class);
        $this->post('/forgot-password', ['email'=>'unknown@example.test'])->assertSessionHas('status', $knownStatus);
        $data = ['email'=>$user->email, 'token'=>Password::createToken($user), 'password'=>'Replacement123!', 'password_confirmation'=>'Replacement123!'];
        $this->post('/reset-password', $data)->assertRedirect('/login')->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Replacement123!', $user->fresh()->password));
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
    }

    private function fakeGithub(bool $verified = true): void
    {
        config(['services.github_oauth.client_id'=>'test-client', 'services.github_oauth.client_secret'=>'test-secret']);
        Http::fake([
            'github.com/login/oauth/access_token'=>Http::response(['access_token'=>'fake-token']),
            'api.github.com/user'=>Http::response(['id'=>12345, 'login'=>'test-member', 'name'=>'Test Member']),
            'api.github.com/user/emails'=>Http::response([['email'=>'github@example.test', 'primary'=>true, 'verified'=>$verified]]),
        ]);
    }

    public function test_github_requires_matching_state_before_contacting_provider(): void
    {
        $this->fakeGithub();
        $this->withSession(['oauth.github.state'=>'expected'])->get('/auth/github/callback?state=wrong&code=example')
            ->assertRedirect('/login')->assertSessionHasErrors('email');
        Http::assertNothingSent();
        $this->assertGuest();
    }

    public function test_github_sign_in_creates_a_verified_member_without_storing_token(): void
    {
        $this->fakeGithub();
        $this->withSession(['oauth.github.state'=>'expected'])->get('/auth/github/callback?state=expected&code=example')->assertRedirect('/');
        $user = User::where('email','github@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('user', $user->role);
        $this->assertDatabaseHas('social_accounts', ['user_id'=>$user->id, 'provider'=>'github', 'provider_user_id'=>'12345']);
        $this->assertStringNotContainsString('fake-token', $user->socialAccounts()->first()->toJson());
    }

    public function test_github_rejects_unverified_email_and_suspended_linked_user(): void
    {
        $this->fakeGithub(false);
        $this->withSession(['oauth.github.state'=>'expected'])->get('/auth/github/callback?state=expected&code=example')->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame(0, User::count());
        $user=$this->member();
        $user->forceFill(['is_active'=>false])->save();
        $user->socialAccounts()->create(['provider'=>'github', 'provider_user_id'=>'12345']);
        $this->fakeGithub();
        $this->withSession(['oauth.github.state'=>'another'])->get('/auth/github/callback?state=another&code=example')->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
