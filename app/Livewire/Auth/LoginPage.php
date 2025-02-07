<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

/**
 * 사용자 로그인을 처리하는 Livewire 컴포넌트
 */
#[Title('Login')]
class LoginPage extends Component
{
    /** @var string 사용자 이메일 */
    public $email;
    
    /** @var string 사용자 비밀번호 */
    public $password;

    /**
     * 로그인 처리를 수행하는 메소드
     * 
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function save() {
        // 입력값 유효성 검사
        $this->validate([
            'email' => 'required|email|max:255|exists:users,email',
            'password' => 'required|min:6|max:255'
        ]);

        // 로그인 시도
        if(!Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            // 로그인 실패 시 에러 메시지 표시
            session()->flash('error', 'Invalid credentials');
            return;
        }

        // 이전에 접근하려던 페이지나 기본 페이지로 리다이렉트
        return redirect()->intended();
    }

    /**
     * 로그인 폼 뷰를 렌더링
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
