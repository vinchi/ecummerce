<?php

namespace App\Livewire;

use App\Models\Product;
use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * 상품 상세 페이지를 관리하는 Livewire 컴포넌트
 * 상품 정보 표시, 수량 조절, 장바구니 담기 기능을 처리
 */
#[Title('Product Detail - NexParan')]
class ProductDetailPage extends Component
{
    // LivewireAlert 트레이트 사용 (알림 메시지 표시 기능)
    use LivewireAlert;
    
    /** @var string 상품의 고유 식별자(slug) */
    public $slug;
    
    /** @var int 선택된 상품 수량 (기본값: 1) */
    public $quantity = 1;
    
    /**
     * 컴포넌트 초기화 메소드
     * URL에서 전달받은 slug를 저장
     * 
     * @param string $slug 상품 식별자
     * @return void
     */
    public function mount($slug) {
        $this->slug = $slug;
    }
    
    /**
     * 상품 수량을 증가시키는 메소드
     * 
     * @return void
     */
    public function increaseQty() {
        $this->quantity++;
    }
    
    /**
     * 상품 수량을 감소시키는 메소드
     * 최소 수량은 1개로 제한
     * 
     * @return void
     */
    public function decreaseQty() {
        if($this->quantity > 1) {
            $this->quantity--;
        }
    }
    
    /**
     * 상품을 장바구니에 추가하는 메소드
     * 
     * @param int $product_id 장바구니에 담을 상품 ID
     * @return void
     */
    public function addToCart($product_id) {
        // CartManagement 헬퍼를 사용하여 장바구니에 상품 추가
        $total_count = CartManagement::addItemToCartWithQty($product_id, $this->quantity);
        
        // Navbar 컴포넌트에 장바구니 아이템 개수 업데이트 이벤트 발송
        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);
        
        // 성공 알림 메시지 표시
        $this->alert('success', '제품이 카트에 담겼습니다.', [
            'position' => 'top-end',  // 알림 위치
            'timer' => 3000,          // 표시 시간 (3초)
            'toast' => true           // 토스트 스타일 사용
        ]);
    }
    
    /**
     * 상품 상세 페이지 뷰를 렌더링하는 메소드
     * slug를 사용하여 해당 상품 정보를 조회
     * 
     * @return \Illuminate\View\View
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException 상품이 존재하지 않을 경우
     */
    public function render()
    {
        return view('livewire.product-detail-page', [
            'product' => Product::where('slug', $this->slug)->firstOrFail()
        ]);
    }
}
