<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * 비밀번호 재설정 링크 요청을 처리하는 Livewire 컴포넌트
 * 사용자가 비밀번호를 잊어버렸을 때 이메일을 통해 재설정 링크를 발송
 */
#[Title('Forgot Password')]
class ForgotPasswordPage extends Component
{
    /** @var string 사용자가 입력한 이메일 주소 */
    public $email;

    /**
     * 비밀번호 재설정 링크 발송을 처리하는 메소드
     * 
     * @return void
     */
    public function save() {
        // 이메일 유효성 검사
        // - required: 필수 입력값
        // - email: 올바른 이메일 형식
        // - exists: users 테이블에 존재하는 이메일인지 확인
        // - max:255: 최대 255자까지 허용
        $this->validate([
            'email' => 'required|email|exists:users,email|max:255'
        ]);

        // Password 파사드를 사용하여 비밀번호 재설정 링크 발송
        $status = Password::sendResetLink(['email' => $this->email]);

        // 링크 발송 성공 시 처리
        if($status === Password::RESET_LINK_SENT) {
            // 성공 메시지를 세션에 플래시 데이터로 저장
            session()->flash('success', 'Password reset link has been sent to your email address!');
            // 이메일 입력 필드 초기화
            $this->email = '';
        }
    }

    /**
     * 비밀번호 재설정 요청 폼 뷰를 렌더링
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.auth.forgot-password-page');
    }
}
