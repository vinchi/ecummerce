<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset; // 비밀번호 재설정 이벤트를 위한 클래스
use Illuminate\Support\Facades\Hash;      // 비밀번호 해싱을 위한 클래스
use Illuminate\Support\Facades\Password;  // 비밀번호 재설정 기능을 위한 클래스
use Illuminate\Support\Str;              // 문자열 관련 헬퍼 클래스
use Livewire\Attributes\Title;           // 페이지 제목 설정을 위한 속성
use Livewire\Attributes\Url;             // URL 파라미터 바인딩을 위한 속성
use Livewire\Component;

#[Title('Reset Password')]
class ResetPasswordPage extends Component
{
    // 비밀번호 재설정에 필요한 속성들
    public $token;                    // 재설정 토큰
    #[Url]                           
    public $email;                    // 사용자 이메일
    public $password;                 // 새 비밀번호
    public $password_confirmation;     // 새 비밀번호 확인

    /**
     * 컴포넌트 초기화 및 토큰 설정
     * 
     * @param string $token 비밀번호 재설정 토큰
     * @return void
     */
    public function mount($token) {
        $this->token = $token;
    }

    /**
     * 비밀번호 재설정 처리
     * 
     * @return mixed 성공 시 로그인 페이지로 리다이렉트, 실패 시 에러 메시지
     */
    public function save() {
        $this->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed'
        ]);

        $status = Password::reset([
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'token' => $this->token
        ], function(User $user, string $password) {
            $password = $this->password;
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));
            
            $user->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET 
            ? redirect('/login')
            : session()->flash('error', 'Something went wrong');
    }

    /**
     * 비밀번호 재설정 페이지 렌더링
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.auth.reset-password-page');
    }
}