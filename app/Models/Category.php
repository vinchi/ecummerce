<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 상품 카테고리를 관리하는 모델 클래스
 * 상품과 1:N 관계를 가지며 카테고리 분류 정보를 저장
 */
class Category extends Model
{
    use HasFactory;
    
    /**
     * 대량 할당이 가능한 속성들
     * 
     * @var array<string>
     */
    protected $fillable = [
        'name',       // 카테고리 이름
        'slug',       // URL에 사용될 고유 식별자
        'image',      // 카테고리 대표 이미지 경로
        'is_active'   // 활성화 상태 (1: 활성화, 0: 비활성화)
    ];
    
    /**
     * 카테고리에 속한 상품들과의 관계 정의
     * 하나의 카테고리는 여러 상품을 포함할 수 있음
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products() {
        return $this->hasMany(Product::class);
    }
    
    /**
     * 활성화된 카테고리만 조회하는 스코프
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query) {
        return $query->where('is_active', 1);
    }
}
