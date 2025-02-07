<?php

namespace App\Livewire;

use App\Livewire\Partials\Navbar;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Helpers\CartManagement;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * 상품 목록 페이지를 관리하는 Livewire 컴포넌트
 * 상품 필터링, 정렬, 페이징, 장바구니 담기 기능을 제공
 */
#[Title('Products - NexParan')]
class ProductsPage extends Component
{
    // 페이지네이션과 알림 기능 사용
    use WithPagination, LivewireAlert;
    
    /** @var array URL 파라미터로 관리되는 선택된 카테고리 목록 */
    #[Url]
    public $selected_categories = [];

    /** @var array URL 파라미터로 관리되는 선택된 브랜드 목록 */
    #[Url]
    public $selected_brands = [];

    /** @var bool URL 파라미터로 관리되는 추천 상품 필터 */
    #[Url]
    public $featured;

    /** @var bool URL 파라미터로 관리되는 할인 상품 필터 */
    #[Url]
    public $on_sale;

    /** @var int URL 파라미터로 관리되는 가격 범위 필터 (기본값: 5,000,000) */
    #[Url]
    public $price_range = 5000000;

    /** @var string URL 파라미터로 관리되는 정렬 기준 (기본값: 'latest') */
    #[Url]
    public $sort = 'latest';
    
    /**
     * 상품을 장바구니에 추가하는 메소드
     * 
     * @param int $product_id 장바구니에 추가할 상품 ID
     * @return void
     */
    public function addToCart($product_id) {
        // 장바구니에 상품 추가 후 총 개수 반환
        $total_count = CartManagement::addItemToCart($product_id);
        
        // Navbar 컴포넌트에 장바구니 개수 업데이트 이벤트 발송
        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);
        
        // 성공 알림 메시지 표시
        $this->alert('success', '제품이 카트에 담겼습니다.', [
            'position' => 'top-end',
            'timer' => 3000,
            'toast' => true
        ]);
    }
    
    /**
     * 상품 목록 페이지 뷰를 렌더링하는 메소드
     * 필터링, 정렬 조건을 적용하여 상품 목록 조회
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 기본 상품 쿼리 생성 (활성화된 상품만)
        $productQuery = Product::query()->where('is_active', 1);
        
        // 선택된 카테고리 필터 적용
        if(!empty($this->selected_categories)) {
            $productQuery->whereIn('category_id', $this->selected_categories);
        }
        
        // 선택된 브랜드 필터 적용
        if(!empty($this->selected_brands)) {
            $productQuery->whereIn('brand_id', $this->selected_brands);
        }
        
        // 추천 상품 필터 적용
        if($this->featured) {
            $productQuery->where('is_featured', 1);
        }
        
        // 할인 상품 필터 적용
        if($this->on_sale) {
            $productQuery->where('is_sail', 1);
        }
        
        // 가격 범위 필터 적용
        if($this->price_range) {
            $productQuery->whereBetween('price', [0, $this->price_range]);
        }
        
        // 정렬 조건 적용
        if($this->sort == 'latest') {
            $productQuery->latest();
        }
        if($this->sort == 'price') {
            $productQuery->orderBy('price');
        }
        
        // 뷰에 데이터 전달하여 렌더링
        return view('livewire.products-page', [
            'products' => $productQuery->paginate(6),  // 페이지당 6개 상품 표시
            'brands' => Brand::where('is_active', 1)->get(['id', 'name', 'slug']),  // 활성화된 브랜드 목록
            'categories' => Category::where('is_active', 1)->get(['id', 'name', 'slug'])  // 활성화된 카테고리 목록
        ]);
    }
}
