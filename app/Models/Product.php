<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 상품 정보를 관리하는 모델 클래스
 * 상품의 기본 정보, 가격, 상태 등을 관리하며
 * 카테고리, 브랜드, 주문과의 관계를 처리
 */
class Product extends Model
{
    use HasFactory;
    
    /**
     * 대량 할당이 가능한 속성들
     * 
     * @var array<string>
     */
    protected $fillable = [
        'category_id',   // 연관된 카테고리 ID
        'brand_id',      // 연관된 브랜드 ID
        'name',          // 상품명
        'slug',          // URL에 사용될 고유 식별자
        'images',        // 상품 이미지 배열 (JSON)
        'description',   // 상품 상세 설명
        'price',         // 판매 가격
        'is_active',     // 판매 활성화 상태 (1: 활성화, 0: 비활성화)
        'is_featured',   // 추천 상품 여부 (1: 추천, 0: 일반)
        'in_stock',      // 재고 여부 (1: 있음, 0: 없음)
        'is_sale'        // 할인 상품 여부 (1: 할인중, 0: 정상가)
    ];
    
    /**
     * 타입 캐스팅이 필요한 속성들
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'images' => 'array'  // images 필드를 JSON에서 배열로 자동 변환
    ];
    
    /**
     * 카테고리와의 관계 정의
     * 각 상품은 하나의 카테고리에 속함
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category() {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * 브랜드와의 관계 정의
     * 각 상품은 하나의 브랜드에 속함
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand() {
        return $this->belongsTo(Brand::class);
    }
    
    /**
     * 주문 항목과의 관계 정의
     * 하나의 상품은 여러 주문 항목에 포함될 수 있음
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems() {
        return $this->hasMany(OrderItem::class);
    }
}
