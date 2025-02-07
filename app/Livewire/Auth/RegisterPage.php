<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * 사용자 회원가입을 처리하는 Livewire 컴포넌트
 */
#[Title('Register')]
class RegisterPage extends Component
{
    /** @var string 사용자 이름 */
    public $name;
    
    /** @var string 사용자 이메일 */
    public $email;
    
    /** @var string 사용자 비밀번호 */
    public $password;

    /**
     * 사용자 등록을 처리하는 메소드
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save() {
        // 입력값 유효성 검사
        $this->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|min:6|max:255'
        ]);

        // 새로운 사용자 생성
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password)
        ]);

        // 사용자 자동 로그인 처리
        Auth::login($user);

        // 이전에 접근하려던 페이지나 기본 페이지로 리다이렉트
        return redirect()->intended();
    }

    /**
     * 회원가입 폼 뷰를 렌더링
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
