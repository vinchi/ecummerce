<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 브랜드 정보를 관리하는 모델 클래스
 * 상품과 1:N 관계를 가지며 브랜드 관련 정보를 저장
 */
class Brand extends Model
{
    use HasFactory;
    
    /**
     * 대량 할당이 가능한 속성들
     * 
     * @var array<string>
     */
    protected $fillable = [
        'name',       // 브랜드 이름
        'slug',       // URL에 사용될 고유 식별자
        'image',      // 브랜드 이미지 경로
        'is_active'   // 활성화 상태 (1: 활성화, 0: 비활성화)
    ];
    
    /**
     * 브랜드에 속한 상품들과의 관계 정의
     * 하나의 브랜드는 여러 상품을 가질 수 있음
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products() {
        return $this->hasMany(Product::class);
    }
}
