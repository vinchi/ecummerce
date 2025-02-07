<?php

namespace App\Livewire;

use App\Models\Brand;
use App\Models\Category;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * 홈페이지를 관리하는 Livewire 컴포넌트
 * 활성화된 브랜드와 카테고리 목록을 표시하는 메인 페이지
 */
#[Title('Home Page - NexParan')]
class HomePage extends Component
{
    /**
     * 홈페이지 뷰를 렌더링하는 메소드
     * 활성화된 브랜드와 카테고리 데이터를 조회하여 뷰에 전달
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 활성화된 브랜드 목록 조회 (is_active = 1인 브랜드만)
        $brands = Brand::where('is_active', 1)->get();
        
        // 활성화된 카테고리 목록 조회 (is_active = 1인 카테고리만)
        $categories = Category::where('is_active', 1)->get();
        
        // 조회된 데이터를 뷰에 전달하여 렌더링
        return view('livewire.home-page', [
            'brands' => $brands,        // 브랜드 목록
            'categories' => $categories // 카테고리 목록
        ]);
    }
}
