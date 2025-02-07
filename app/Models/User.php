<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * 사용자 정보를 관리하는 모델 클래스
 * 인증, 권한, 주문 내역 등 사용자 관련 모든 정보를 통합 관리
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * 대량 할당이 가능한 속성들
     * 
     * @var array<string>
     */
    protected $fillable = [
        'name',          // 사용자 이름
        'email',         // 이메일 주소 (로그인 ID로 사용)
        'password',      // 암호화된 비밀번호
        'phone',         // 연락처
        'is_admin',      // 관리자 여부 (1: 관리자, 0: 일반 사용자)
        'is_active'      // 계정 활성화 상태 (1: 활성화, 0: 비활성화)
    ];

    /**
     * JSON 직렬화 시 숨길 속성들
     * 보안을 위해 민감한 정보는 제외
     * 
     * @var array<string>
     */
    protected $hidden = [
        'password',           // 비밀번호
        'remember_token',     // 자동 로그인 토큰
    ];

    /**
     * 타입 캐스팅이 필요한 속성들
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',  // 이메일 인증 시간
            'password' => 'hashed',             // 비밀번호 해시 처리
            'is_admin' => 'boolean',            // 관리자 여부 불리언 변환
            'is_active' => 'boolean'            // 활성화 상태 불리언 변환
        ];
    }
    
    /**
     * 사용자의 주문 내역과의 관계 정의
     * 한 사용자는 여러 주문을 가질 수 있음
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders() {
        return $this->hasMany(Order::class);
    }
    
    /**
     * 사용자가 관리자인지 확인하는 메소드
     * 
     * @return bool
     */
    public function isAdmin() {
        return $this->is_admin === true;
    }
    
    /**
     * 사용자 계정이 활성화 상태인지 확인하는 메소드
     * 
     * @return bool
     */
    public function isActive() {
        return $this->is_active === true;
    }
}
