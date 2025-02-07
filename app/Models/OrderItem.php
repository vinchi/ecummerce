<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 주문 상품 항목을 관리하는 모델 클래스
 * 주문과 상품 간의 중간 테이블로서 주문된 개별 상품의 정보를 저장
 */
class OrderItem extends Model
{
    use HasFactory;
    
    /**
     * 대량 할당이 가능한 속성들
     * 
     * @var array<string>
     */
    protected $fillable = [
        'order_id',      // 연관된 주문 ID
        'product_id',    // 주문된 상품 ID
        'quantity',      // 주문 수량
        'unit_amount',   // 개당 가격
        'total_amount'   // 총 금액 (수량 * 개당 가격)
    ];
    
    /**
     * 주문과의 관계 정의
     * 각 주문 항목은 하나의 주문에 속함
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order() {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * 상품과의 관계 정의
     * 각 주문 항목은 하나의 상품을 참조
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product() {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * 주문 항목의 소계를 계산하는 접근자
     * 단위 가격과 수량을 곱하여 총액 반환
     * 
     * @return float
     */
    public function getSubtotalAttribute() {
        return $this->unit_amount * $this->quantity;
    }
}
