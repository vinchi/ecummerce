<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 주문 배송지 정보를 관리하는 모델 클래스
 * 주문과 1:1 관계를 가지며 배송 관련 정보를 저장
 */
class Address extends Model
{
    use HasFactory;
    
    /**
     * 대량 할당이 가능한 속성들
     * 
     * @var array<string>
     */
    protected $fillable = [
        'order_id',        // 연관된 주문 ID
        'first_name',      // 수령인 이름
        'last_name',       // 수령인 성
        'phone',           // 연락처
        'street_address',  // 상세 주소
        'city',           // 도시
        'state',          // 주/도
        'zip_code'        // 우편번호
    ];
    
    /**
     * 주문과의 관계 정의
     * 하나의 주소는 하나의 주문에 속함
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order() {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * 수령인의 전체 이름을 반환하는 접근자
     * first_name과 last_name을 결합하여 반환
     * 
     * @return string
     */
    public function getFullNameAttribute() {
        return "{$this->first_name} {$this->last_name}";
    }
}
