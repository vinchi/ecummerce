<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 주문 정보를 관리하는 모델 클래스
 * 사용자의 주문 정보, 결제 정보, 배송 정보를 통합 관리
 */
class Order extends Model
{
    use HasFactory;
    
    /**
     * 대량 할당이 가능한 속성들
     * 
     * @var array<string>
     */
    protected $fillable = [
        'user_id',          // 주문한 사용자 ID
        'grand_total',      // 총 주문 금액
        'payment_method',   // 결제 수단 (카드, 계좌이체 등)
        'payment_status',   // 결제 상태 (대기, 완료, 실패 등)
        'status',          // 주문 상태 (접수, 처리중, 배송중, 완료 등)
        'currency',        // 통화 단위 (KRW, USD 등)
        'shipping_amount', // 배송비
        'shipping_method', // 배송 방법 (일반, 특급 등)
        'notes'           // 주문 메모 사항
    ];
    
    /**
     * 주문한 사용자와의 관계 정의
     * 하나의 주문은 한 명의 사용자에게 속함
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 주문에 포함된 상품 항목들과의 관계 정의
     * 하나의 주문은 여러 주문 항목을 가질 수 있음
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items() {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * 주문의 배송 주소와의 관계 정의
     * 하나의 주문은 하나의 배송 주소를 가짐
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function address() {
        return $this->hasOne(Address::class);
    }
}
